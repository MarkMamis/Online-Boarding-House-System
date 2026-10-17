@props([
    'name' => 'contract_signature_data',
    'existingUrl' => null,
    'title' => 'Contract E-signature',
    'helper' => 'Sign below to save your signature for future rental contracts.',
    'idPrefix' => 'signaturePad',
    'saveUrl' => null,
    'height' => 170,
])

<div class="signature-pad-widget" id="{{ $idPrefix }}Widget" data-has-existing="{{ !empty($existingUrl) ? '1' : '0' }}">
    @if(filled($title))
        <div class="d-flex align-items-center justify-content-between mb-1">
            <label class="form-label fw-semibold mb-0" id="{{ $idPrefix }}Title">
                <i class="bi bi-pen me-1 text-success"></i>{{ $title }}
            </label>
        </div>
    @endif

    @if(filled($helper))
        <div class="field-hint text-muted small mb-2" id="{{ $idPrefix }}Helper">
            {{ $helper }}
        </div>
    @endif

    {{-- SAVED SIGNATURE PREVIEW (Shown when an existing signature is already on file) --}}
    @if(!empty($existingUrl))
        <div class="signature-existing-card p-3 rounded-3 border bg-white mb-2" id="{{ $idPrefix }}ExistingCard">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                <div class="text-center text-sm-start flex-grow-1">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>Saved contract signature:
                    </div>
                    <div class="signature-preview-box p-2 rounded bg-light border d-inline-block">
                        <img src="{{ $existingUrl }}" alt="Saved signature preview" id="{{ $idPrefix }}ExistingImg" style="max-height: 72px; max-width: 100%; object-fit: contain;">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="{{ $idPrefix }}ReplaceBtn">
                        <i class="bi bi-pencil-square me-1"></i>Replace Signature
                    </button>
                    <a href="{{ $existingUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="{{ $idPrefix }}ViewFullBtn">
                        <i class="bi bi-box-arrow-up-right me-1"></i>View Full
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- INTERACTIVE SIGNING PAD --}}
    <div class="signature-draw-panel {{ !empty($existingUrl) ? 'd-none' : '' }}" id="{{ $idPrefix }}DrawPanel">
        <div class="signature-canvas-wrapper position-relative bg-white rounded-3 border" style="border-color: #cbd5e1 !important; overflow: hidden; background: #ffffff;">
            {{-- Visual subtle baseline guide in CSS behind transparent canvas --}}
            <div class="signature-baseline-indicator position-absolute start-0 end-0 pe-none" style="bottom: 38px; border-bottom: 1px dashed rgba(100, 116, 139, 0.4); margin: 0 24px; pointer-events: none;"></div>

            {{-- Centered subtle placeholder text --}}
            <div id="{{ $idPrefix }}Placeholder" class="signature-placeholder position-absolute top-50 start-50 translate-middle text-muted pe-none user-select-none" style="pointer-events: none; opacity: 0.55; font-size: 0.95rem; font-weight: 500;">
                <i class="bi bi-pen me-1"></i>Sign here
            </div>

            {{-- HTML5 Canvas --}}
            <canvas id="{{ $idPrefix }}Canvas" class="signature-canvas w-100" style="display: block; touch-action: none; height: {{ (int) $height }}px; cursor: crosshair; background: transparent;"></canvas>
        </div>

        {{-- Controls and Feedback --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="{{ $idPrefix }}ClearBtn" title="Clear canvas">
                    <i class="bi bi-eraser me-1"></i>Clear
                </button>
                <button type="button" class="btn btn-brand btn-sm rounded-pill px-3" id="{{ $idPrefix }}SaveBtn" title="Save signature">
                    <i class="bi bi-check2-circle me-1"></i><span id="{{ $idPrefix }}SaveBtnText">Save Signature</span>
                </button>
                @if(!empty($existingUrl))
                    <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted px-2" id="{{ $idPrefix }}CancelReplaceBtn">
                        Keep Existing
                    </button>
                @endif
            </div>

            <div id="{{ $idPrefix }}Status" class="small text-muted">
                {{ !empty($existingUrl) ? 'Draw a new signature to replace your current one.' : 'Sign above using mouse, touch, or stylus.' }}
            </div>
        </div>
    </div>

    {{-- Hidden Form Input --}}
    <input type="hidden" name="{{ $name }}" id="{{ $idPrefix }}Input" value="{{ old($name, '') }}">
    @error($name)
        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
    @enderror
</div>

<script>
(function () {
    const idPrefix = @json($idPrefix);
    const saveUrl = @json($saveUrl);
    const canvas = document.getElementById(idPrefix + 'Canvas');
    const input = document.getElementById(idPrefix + 'Input');
    const placeholder = document.getElementById(idPrefix + 'Placeholder');
    const clearBtn = document.getElementById(idPrefix + 'ClearBtn');
    const saveBtn = document.getElementById(idPrefix + 'SaveBtn');
    const saveBtnText = document.getElementById(idPrefix + 'SaveBtnText');
    const statusText = document.getElementById(idPrefix + 'Status');
    const drawPanel = document.getElementById(idPrefix + 'DrawPanel');
    const existingCard = document.getElementById(idPrefix + 'ExistingCard');
    const existingImg = document.getElementById(idPrefix + 'ExistingImg');
    const replaceBtn = document.getElementById(idPrefix + 'ReplaceBtn');
    const cancelReplaceBtn = document.getElementById(idPrefix + 'CancelReplaceBtn');
    const viewFullBtn = document.getElementById(idPrefix + 'ViewFullBtn');

    if (!canvas || !input) return;

    let ctx = null;
    let isDrawing = false;
    let hasStroke = false;

    const updateStatus = function (message, isSuccess, isError) {
        if (!statusText) return;
        statusText.textContent = message;
        statusText.classList.toggle('text-success', !!isSuccess);
        statusText.classList.toggle('text-danger', !!isError);
        statusText.classList.toggle('text-muted', !isSuccess && !isError);
    };

    const getCanvasPoint = function (event) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top,
        };
    };

    const resizeCanvas = function () {
        const rect = canvas.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) return;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const targetWidth = Math.floor(rect.width * ratio);
        const targetHeight = Math.floor(rect.height * ratio);

        if (canvas.width === targetWidth && canvas.height === targetHeight) {
            return;
        }

        // Backup current drawn strokes before resizing
        let tempCanvas = null;
        if (hasStroke && canvas.width > 0 && canvas.height > 0) {
            tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(canvas, 0, 0);
        }

        canvas.width = targetWidth;
        canvas.height = targetHeight;
        ctx = canvas.getContext('2d');
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0f172a';

        if (tempCanvas) {
            ctx.save();
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.drawImage(tempCanvas, 0, 0, targetWidth, targetHeight);
            ctx.restore();
        }
    };

    const clearCanvas = function () {
        if (!ctx) return;
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);
        hasStroke = false;
        input.value = '';
        if (placeholder) placeholder.classList.remove('d-none');
        updateStatus('Canvas cleared. Sign above.', false, false);
    };

    // Drawing Pointer Events
    canvas.addEventListener('pointerdown', function (event) {
        if (event.button !== undefined && event.button !== 0) return;
        if (!ctx) resizeCanvas();
        if (!ctx) return;

        isDrawing = true;
        hasStroke = true;
        try {
            canvas.setPointerCapture(event.pointerId);
        } catch (_) {}

        if (placeholder) placeholder.classList.add('d-none');
        const point = getCanvasPoint(event);
        ctx.beginPath();
        ctx.moveTo(point.x, point.y);
        updateStatus('Drawing...', false, false);
    });

    canvas.addEventListener('pointermove', function (event) {
        if (!isDrawing || !ctx) return;
        const point = getCanvasPoint(event);
        ctx.lineTo(point.x, point.y);
        ctx.stroke();
    });

    const stopDrawing = function (event) {
        if (!isDrawing) return;
        isDrawing = false;
        try {
            if (event && event.pointerId && canvas.hasPointerCapture(event.pointerId)) {
                canvas.releasePointerCapture(event.pointerId);
            }
        } catch (_) {}

        if (hasStroke) {
            // Keep hidden form input synchronized with latest data URL
            const dataUrl = canvas.toDataURL('image/png');
            input.value = dataUrl;
            updateStatus('Signature captured. Click "Save Signature" or Continue.', false, false);
        }
    };

    canvas.addEventListener('pointerup', stopDrawing);
    canvas.addEventListener('pointercancel', stopDrawing);
    canvas.addEventListener('pointerleave', stopDrawing);

    // Clear Button
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            clearCanvas();
        });
    }

    // Save Signature Button
    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            if (!hasStroke) {
                alert('Please draw your signature before saving.');
                updateStatus('Please draw your signature first.', false, true);
                return;
            }

            const dataUrl = canvas.toDataURL('image/png');
            input.value = dataUrl;

            if (!saveUrl) {
                updateStatus('Signature captured.', true, false);
                return;
            }

            // AJAX Save
            saveBtn.disabled = true;
            if (saveBtnText) saveBtnText.textContent = 'Saving signature...';
            updateStatus('Saving signature...', false, false);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '';

                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        signature_data: dataUrl,
                        _token: csrfToken,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    updateStatus('Signature saved.', true, false);
                    if (saveBtnText) saveBtnText.textContent = 'Save Signature';
                    saveBtn.disabled = false;

                    // Switch to preview mode with updated image URL
                    if (existingImg && data.url) {
                        existingImg.src = data.url;
                    }
                    if (viewFullBtn && data.url) {
                        viewFullBtn.href = data.url;
                    }
                    if (existingCard) {
                        existingCard.classList.remove('d-none');
                    }
                    if (drawPanel) {
                        drawPanel.classList.add('d-none');
                    }
                } else {
                    const errorMsg = data.message || 'Unable to save your signature. Please try again.';
                    updateStatus(errorMsg, false, true);
                    alert(errorMsg);
                    saveBtn.disabled = false;
                    if (saveBtnText) saveBtnText.textContent = 'Save Signature';
                }
            } catch (err) {
                console.error(err);
                updateStatus('Unable to save your signature. Please try again.', false, true);
                saveBtn.disabled = false;
                if (saveBtnText) saveBtnText.textContent = 'Save Signature';
            }
        });
    }

    // Replace and Cancel Replace Handlers
    if (replaceBtn) {
        replaceBtn.addEventListener('click', function () {
            if (existingCard) existingCard.classList.add('d-none');
            if (drawPanel) drawPanel.classList.remove('d-none');
            setTimeout(resizeCanvas, 50);
        });
    }

    if (cancelReplaceBtn) {
        cancelReplaceBtn.addEventListener('click', function () {
            clearCanvas();
            input.value = '';
            if (existingCard) existingCard.classList.remove('d-none');
            if (drawPanel) drawPanel.classList.add('d-none');
        });
    }

    // Auto-resize on window resize and container visibility changes
    window.addEventListener('resize', resizeCanvas);

    if (window.ResizeObserver && canvas.parentElement) {
        const ro = new ResizeObserver(function () {
            resizeCanvas();
        });
        ro.observe(canvas.parentElement);
    }

    // Initial resize when script runs
    setTimeout(resizeCanvas, 50);
})();
</script>
