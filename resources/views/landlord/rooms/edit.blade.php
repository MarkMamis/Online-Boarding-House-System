@extends('layouts.landlord')

@section('content')
<div class="room-edit-shell">
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="text-uppercase small text-muted fw-semibold">Room Settings</div>
        <h1 class="h3 mb-1">Edit Room - {{ $property->name }}</h1>
        <div class="text-muted small">Property address: {{ $property->address }}</div>
    </div>
    <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back to Property</a>
</div>

<div class="edit-summary mb-4">
  <div class="edit-summary-item">
    <div class="edit-summary-label">Room</div>
    <div class="edit-summary-value">{{ $room->room_number }}</div>
  </div>
  <div class="edit-summary-item">
    <div class="edit-summary-label">Capacity</div>
    <div class="edit-summary-value">{{ $room->capacity }} slots</div>
  </div>
  <div class="edit-summary-item">
    <div class="edit-summary-label">Current Price</div>
    <div class="edit-summary-value">P{{ number_format($room->price, 2) }}</div>
  </div>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@php
  $initialInclusions = collect(explode(',', (string) old('inclusions', $room->inclusions)))
      ->map(fn ($item) => trim($item))
      ->filter()
      ->values();
@endphp

<form id="updateRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.update', [$property->id, $room->id]) }}">
  @csrf
  @method('PUT')

  <div class="row g-4 room-split-grid mb-4">
    <div class="col-xl-7">
      {{-- ── Room Details ───────────────────────────────────────── --}}
      <div class="card shadow-sm room-form-card h-100">
        <div class="card-header fw-semibold bg-transparent border-0 pt-3 pb-0 px-3">Room Details</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Room Number</label>
              <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Capacity</label>
              <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}" required>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Price (₱)</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $room->price) }}" required>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" required>
                <option value="available" @selected(old('status', $room->status)==='available')>Available</option>
                <option value="occupied" @selected(old('status', $room->status)==='occupied')>Occupied</option>
                <option value="maintenance" @selected(old('status', $room->status)==='maintenance')>Maintenance</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Included in Rent (optional)</label>
              <input type="hidden" name="inclusions" id="inclusionsSerialized" value="{{ old('inclusions', $room->inclusions) }}">

              <div id="inclusionsList" class="vstack gap-2">
                @forelse($initialInclusions as $item)
                  <div class="input-group inclusion-row">
                    <input type="text" class="form-control inclusion-input" value="{{ $item }}" placeholder="e.g., Electric fan">
                    <button class="btn btn-outline-danger remove-inclusion" type="button" aria-label="Remove inclusion">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                @empty
                  <div class="input-group inclusion-row">
                    <input type="text" class="form-control inclusion-input" placeholder="e.g., Electric fan">
                    <button class="btn btn-outline-danger remove-inclusion" type="button" aria-label="Remove inclusion">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  </div>
                @endforelse
              </div>

              <button id="addInclusionBtn" type="button" class="btn btn-sm btn-outline-brand mt-2">
                <i class="bi bi-plus-lg me-1"></i>Add another
              </button>
              <div class="form-text">Add one item per field. These will be saved as comma-separated text.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-5">
      {{-- ── Room Cover Photo ───────────────────────────────────── --}}
      <div class="card shadow-sm room-form-card h-100 cover-panel">
        <div class="card-header fw-semibold bg-transparent border-0 pt-3 pb-0 px-3">Room Cover Photo</div>
        <div class="card-body d-flex flex-column">
          <p class="text-muted small mb-3">This is the main photo shown on listings and search results.</p>

          @if(!empty($room->image_path))
          <div class="mb-3">
            <div class="position-relative d-inline-block w-100">
              <img src="{{ asset('storage/'.$room->image_path) }}" alt="Cover photo"
                   class="img-thumbnail room-cover-thumb w-100" style="height:220px; object-fit:cover;">
              <span class="badge bg-success position-absolute top-0 start-0 m-2">Current Cover</span>
            </div>
          </div>
          @endif

          <label class="form-label">{{ !empty($room->image_path) ? 'Replace Cover Photo' : 'Upload Cover Photo' }} <span class="text-muted">(optional)</span></label>
          <input type="file" name="image" class="form-control" accept="image/*" id="coverInput">
          <div class="form-text">Leave blank to keep the current cover photo. Max 4 MB.</div>
          <div class="mt-2" id="coverPreviewWrap" style="display:none;">
            <img id="coverPreview" src="" alt="Preview" class="img-thumbnail w-100" style="height:180px; object-fit:cover;">
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Room Detail Photos ───────────────────────────────────── --}}
  <div class="card shadow-sm mb-4 room-form-card">
    <div class="card-header fw-semibold bg-transparent border-0 pt-3 pb-0 px-3">Room Detail Photos</div>
    <div class="card-body">
      <p class="text-muted small mb-3">Add labelled detail photos such as the bed, kitchen, comfort room, etc.</p>

      {{-- Existing detail images --}}
      @if($roomImages->isNotEmpty())
      <div class="row g-3 mb-4" id="existingDetailImages">
        @foreach($roomImages as $img)
        <div class="col-sm-6 col-md-4 col-lg-3" id="detail-img-{{ $img->id }}">
          <div class="card h-100 border detail-photo-card">
            <img src="{{ asset('storage/'.$img->image_path) }}" alt="{{ $img->label ?? 'Detail photo' }}"
                 class="card-img-top" style="height:160px; object-fit:cover;">
            <div class="card-body p-2">
              <input type="text"
                     name="existing_labels[{{ $img->id }}]"
                     class="form-control form-control-sm"
                     value="{{ old('existing_labels.'.$img->id, $img->label) }}"
                     placeholder="Label (e.g. Bed, Kitchen)">
            </div>
            <div class="card-footer p-2 bg-transparent border-top-0">
              <div class="form-check">
                <input class="form-check-input border-danger" type="checkbox"
                       name="delete_detail_images[]" value="{{ $img->id }}"
                       id="del_{{ $img->id }}"
                       onchange="toggleDeleteCard({{ $img->id }}, this.checked)">
                <label class="form-check-label text-danger small" for="del_{{ $img->id }}">Remove this photo</label>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @endif

      {{-- New detail image slots --}}
      <div id="newDetailSlots" class="detail-upload-stack">
        <div class="new-detail-slot" data-index="0">
          <div class="new-slot-head">
            <div class="slot-index">Photo 1</div>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 remove-slot-btn d-none" onclick="removeSlot(this)">
              <i class="bi bi-x-lg me-1"></i>Remove
            </button>
          </div>
          <div class="row g-3 align-items-start">
            <div class="col-md-4">
              <label class="form-label small fw-semibold mb-1">Select Photo</label>
              <input type="file" name="detail_images[]" class="form-control form-control-sm detail-file-input" accept="image/*">
              <div class="detail-preview-wrap mt-2">
                <img src="" alt="Preview" class="detail-preview-img d-none">
                <div class="detail-preview-empty">No photo selected yet</div>
              </div>
            </div>
            <div class="col-md-8">
              <label class="form-label small fw-semibold mb-1">Label <span class="text-muted">(optional)</span></label>
              <input type="text" name="detail_labels[]" class="form-control" placeholder="e.g. Bed, Kitchen, Comfort Room">
              <div class="form-text mt-2">Use clear labels so students can quickly understand what this photo shows.</div>
            </div>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-sm btn-outline-brand mt-2 rounded-pill px-3" onclick="addDetailSlot()">
        <i class="bi bi-plus-lg me-1"></i>Add Another Photo
      </button>
    </div>
  </div>

  {{-- ── Save button ──────────────────────────────────────────── --}}
  <div class="d-flex justify-content-end mb-4 action-bar">
    <button type="submit" form="updateRoomForm" class="btn btn-brand rounded-pill px-4">Save Changes</button>
  </div>
</form>

{{-- ── Danger Zone (Delete Room) ── kept FAR from Save button ── --}}
<div class="card border-danger mt-2 mb-1 danger-zone-card">
  <div class="card-header bg-danger text-white fw-semibold">Danger Zone</div>
  <div class="card-body d-flex justify-content-between align-items-center">
    <div>
      <div class="fw-semibold">Delete this room</div>
      <div class="text-muted small">This will permanently delete Room {{ $room->room_number }} and all its data. This cannot be undone.</div>
    </div>
    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRoomModal">
      Delete Room
    </button>
  </div>
</div>

@push('styles')
<style>
  .room-edit-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .edit-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
  }
  .edit-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .7rem .8rem;
    min-width: 0;
  }
  .edit-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .edit-summary-value {
    font-size: .94rem;
    font-weight: 700;
    color: #14532d;
  }
  .room-form-card {
    border: 1px solid rgba(2,8,20,.08);
    box-shadow: 0 14px 30px rgba(2,8,20,.07);
    border-radius: 1rem;
  }
  .room-form-card .card-body {
    padding: 1.1rem 1rem 1rem;
  }
  .room-form-card .form-label {
    font-weight: 600;
    color: #0f172a;
  }
  .room-form-card .form-control,
  .room-form-card .form-select {
    border-color: rgba(2,8,20,.14);
  }
  .room-split-grid {
    align-items: stretch;
  }
  .cover-panel {
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
  }
  .room-cover-thumb {
    border-radius: .75rem;
    border: 1px solid rgba(2,8,20,.14);
  }
  .detail-photo-card {
    border-color: rgba(2,8,20,.14) !important;
    border-radius: .8rem;
    overflow: hidden;
  }
  .detail-upload-stack {
    display: grid;
    gap: .75rem;
  }
  .new-detail-slot {
    border: 1px solid rgba(2,8,20,.12);
    border-radius: .85rem;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    padding: .8rem;
  }
  .new-slot-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .6rem;
    margin-bottom: .65rem;
  }
  .slot-index {
    font-size: .76rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: rgba(2,8,20,.58);
    font-weight: 700;
  }
  .detail-preview-wrap {
    border: 1px dashed rgba(2,8,20,.22);
    border-radius: .7rem;
    min-height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    overflow: hidden;
  }
  .detail-preview-img {
    width: 100%;
    height: 140px;
    object-fit: cover;
  }
  .detail-preview-empty {
    font-size: .8rem;
    color: rgba(2,8,20,.5);
    font-weight: 600;
  }
  .action-bar {
    position: sticky;
    bottom: .5rem;
    z-index: 3;
    padding: .6rem;
    background: rgba(248,250,252,.88);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .9rem;
  }
  .danger-zone-card {
    border-radius: .9rem;
    overflow: hidden;
  }
  @media (max-width: 991.98px) {
    .edit-summary {
      grid-template-columns: 1fr;
    }
    .room-edit-shell {
      padding: .95rem;
    }
    .cover-panel {
      background: #ffffff;
    }
  }
</style>
@endpush

{{-- Delete confirmation modal --}}
<div class="modal fade" id="deleteRoomModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Delete Room {{ $room->room_number }}?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Type <strong>{{ $room->room_number }}</strong> below to confirm deletion.</p>
        <input type="text" id="deleteConfirmInput" class="form-control" placeholder="Type room number to confirm">
        <div class="text-danger small mt-1" id="deleteConfirmError" style="display:none">Room number does not match.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" action="{{ route('landlord.properties.rooms.destroy', [$property->id, $room->id]) }}" id="deleteRoomForm">
          @csrf
          @method('DELETE')
          <button type="button" class="btn btn-danger" onclick="confirmDelete()">Yes, Delete Room</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Cover photo preview
document.getElementById('coverInput').addEventListener('change', function () {
  const wrap = document.getElementById('coverPreviewWrap');
  const preview = document.getElementById('coverPreview');
  if (this.files && this.files[0]) {
    preview.src = URL.createObjectURL(this.files[0]);
    wrap.style.display = 'block';
  } else {
    wrap.style.display = 'none';
  }
});

// Toggle red border when marking for deletion
function toggleDeleteCard(id, checked) {
  const card = document.getElementById('detail-img-' + id);
  if (card) {
    card.querySelector('.card').style.opacity = checked ? '0.4' : '1';
    card.querySelector('.card').style.outline = checked ? '2px solid #dc3545' : '';
  }
}

// Add new detail slot
let slotIndex = 1;
function addDetailSlot() {
  const container = document.getElementById('newDetailSlots');
  const first = container.querySelector('.new-detail-slot');
  const clone = first.cloneNode(true);
  clone.setAttribute('data-index', slotIndex);
  // reset inputs
  clone.querySelector('input[type=file]').value = '';
  clone.querySelector('input[type=text]').value = '';
  clone.querySelector('.detail-preview-img').src = '';
  clone.querySelector('.detail-preview-img').classList.add('d-none');
  clone.querySelector('.detail-preview-empty').classList.remove('d-none');
  // wire file preview
  clone.querySelector('.detail-file-input').addEventListener('change', handleDetailPreview);
  container.appendChild(clone);
  updateDetailSlotState();
  slotIndex++;
}

// Remove a slot
function removeSlot(btn) {
  btn.closest('.new-detail-slot').remove();
  updateDetailSlotState();
}

// Detail photo preview
function handleDetailPreview(e) {
  const wrap = e.target.closest('.new-detail-slot').querySelector('.detail-preview-wrap');
  const img = wrap.querySelector('.detail-preview-img');
  const empty = wrap.querySelector('.detail-preview-empty');
  if (e.target.files && e.target.files[0]) {
    img.src = URL.createObjectURL(e.target.files[0]);
    img.classList.remove('d-none');
    empty.classList.add('d-none');
  } else {
    img.src = '';
    img.classList.add('d-none');
    empty.classList.remove('d-none');
  }
}

function updateDetailSlotState() {
  const slots = Array.from(document.querySelectorAll('#newDetailSlots .new-detail-slot'));
  slots.forEach((slot, index) => {
    const label = slot.querySelector('.slot-index');
    if (label) label.textContent = `Photo ${index + 1}`;
    const removeBtn = slot.querySelector('.remove-slot-btn');
    if (removeBtn) {
      if (slots.length === 1) {
        removeBtn.classList.add('d-none');
      } else {
        removeBtn.classList.remove('d-none');
      }
    }
  });
}

// Wire preview on first slot
document.querySelector('.detail-file-input').addEventListener('change', handleDetailPreview);
updateDetailSlotState();

// Delete room confirmation — must type room number
function confirmDelete() {
  const input = document.getElementById('deleteConfirmInput').value.trim();
  const expected = '{{ $room->room_number }}';
  if (input !== expected) {
    document.getElementById('deleteConfirmError').style.display = 'block';
    return;
  }
  document.getElementById('deleteRoomForm').submit();
}

// Inclusions: short fields -> comma-separated text
const updateRoomForm = document.getElementById('updateRoomForm');
const inclusionsList = document.getElementById('inclusionsList');
const addInclusionBtn = document.getElementById('addInclusionBtn');
const inclusionsSerialized = document.getElementById('inclusionsSerialized');

if (updateRoomForm && inclusionsList && addInclusionBtn && inclusionsSerialized) {
  const createInclusionRow = (value = '') => {
    const row = document.createElement('div');
    row.className = 'input-group inclusion-row';
    row.innerHTML = `
      <input type="text" class="form-control inclusion-input" placeholder="e.g., Electric fan">
      <button class="btn btn-outline-danger remove-inclusion" type="button" aria-label="Remove inclusion">
        <i class="bi bi-x-lg"></i>
      </button>
    `;
    row.querySelector('.inclusion-input').value = value;
    return row;
  };

  const serializeInclusions = () => {
    const values = Array.from(inclusionsList.querySelectorAll('.inclusion-input'))
      .map((el) => el.value.trim())
      .filter((value) => value.length > 0);
    inclusionsSerialized.value = values.join(', ');
  };

  addInclusionBtn.addEventListener('click', () => {
    inclusionsList.appendChild(createInclusionRow());
    serializeInclusions();
  });

  inclusionsList.addEventListener('click', (event) => {
    const button = event.target.closest('.remove-inclusion');
    if (!button) return;

    const rows = inclusionsList.querySelectorAll('.inclusion-row');
    if (rows.length <= 1) {
      const inputEl = rows[0]?.querySelector('.inclusion-input');
      if (inputEl) inputEl.value = '';
    } else {
      button.closest('.inclusion-row')?.remove();
    }
    serializeInclusions();
  });

  inclusionsList.addEventListener('input', (event) => {
    if (!event.target.classList.contains('inclusion-input')) return;
    serializeInclusions();
  });

  updateRoomForm.addEventListener('submit', () => {
    serializeInclusions();
  });

  serializeInclusions();
}
</script>
@endsection
