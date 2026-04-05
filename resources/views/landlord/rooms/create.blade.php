@extends('layouts.landlord')

@section('content')
<div class="room-create-shell">
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="text-uppercase small text-muted fw-semibold">Room Setup</div>
        <h1 class="h3 mb-1">Add Room - {{ $property->name }}</h1>
        <div class="text-muted small">{{ $property->address }}</div>
    </div>
    <a href="{{ route('landlord.properties.rooms.index', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back</a>
</div>

<div class="create-summary mb-4">
  <div class="create-summary-item">
    <div class="create-summary-label">Property</div>
    <div class="create-summary-value text-truncate">{{ $property->name }}</div>
  </div>
  <div class="create-summary-item">
    <div class="create-summary-label">Default Status</div>
    <div class="create-summary-value">Available</div>
  </div>
  <div class="create-summary-item">
    <div class="create-summary-label">Address</div>
    <div class="create-summary-value text-truncate">{{ $property->address }}</div>
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
  $initialInclusions = collect(explode(',', (string) old('inclusions')))
      ->map(fn ($item) => trim($item))
      ->filter()
      ->values();
@endphp

<form id="createRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.store', $property->id) }}" class="card shadow-sm room-form-card">
  @csrf
  <div class="card-body">
    <div class="row g-4 room-split-grid">
      <div class="col-xl-7">
        <div class="edit-panel h-100">
          <div class="section-kicker mb-3">Room Details</div>
          <div class="row g-3">
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Room Name/Number</label>
              <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}" required>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Capacity</label>
              <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', 1) }}" required>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Price (₱)</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price') }}" required>
            </div>
            <div class="col-md-6 col-lg-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select" required>
                <option value="available" @selected(old('status')==='available')>Available</option>
                <option value="occupied" @selected(old('status')==='occupied')>Occupied</option>
                <option value="maintenance" @selected(old('status')==='maintenance')>Maintenance</option>
              </select>
            </div>

            @if(!empty($supportsAdvanceRequirement))
            <div class="col-12">
              <div class="form-check mt-1">
                <input
                  class="form-check-input"
                  type="checkbox"
                  value="1"
                  id="requiresAdvancePayment"
                  name="requires_advance_payment"
                  @checked(old('requires_advance_payment') == '1')
                >
                <label class="form-check-label" for="requiresAdvancePayment">
                  Require 1 month advance payment for this room
                </label>
              </div>
              <div class="form-text">If enabled, students cannot turn off advance payment during booking.</div>
            </div>
            @endif

            <div class="col-12">
              <label class="form-label">Included in Rent (optional)</label>
              <input type="hidden" name="inclusions" id="inclusionsSerialized" value="{{ old('inclusions') }}">

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

      <div class="col-xl-5">
        <div class="edit-panel cover-panel h-100">
          <div class="section-kicker mb-3">Room Cover Photo</div>
          <p class="text-muted small mb-3">Upload a clear main photo for room listings and search results.</p>
          <label class="form-label">Room Photo <span class="text-muted">(optional)</span></label>
          <input type="file" id="roomImageInput" name="image" class="form-control" accept="image/*">
          <div class="form-text">JPG/PNG/WebP/GIF, up to 2MB.</div>
          <div class="mt-2" id="roomImagePreviewWrap" style="display:none;">
            <img id="roomImagePreview" alt="Room photo preview" class="img-thumbnail w-100 room-cover-preview" style="max-height: 220px; object-fit: cover;">
          </div>
        </div>
      </div>
    </div>

    <div class="detail-photos-panel mt-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
          <div class="section-kicker">Detail Photos</div>
          <div class="text-muted small">Optional photos for bed, kitchen, comfort room, and other room highlights.</div>
        </div>
      </div>

      <div id="newDetailSlots" class="detail-upload-stack">
        <div class="new-detail-slot" data-index="0">
          <div class="new-slot-head">
            <div class="slot-index">Photo 1</div>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 remove-slot-btn d-none" onclick="removeDetailSlot(this)">
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

      <button type="button" id="addDetailSlotBtn" class="btn btn-sm btn-outline-brand mt-2 rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i>Add Another Photo
      </button>
    </div>
  </div>
  <div class="card-footer bg-light d-flex justify-content-end action-bar">
    <button type="submit" class="btn btn-brand rounded-pill px-4">Save Room</button>
  </div>
</form>
</div>

@push('styles')
<style>
  .room-create-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .create-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
  }
  .create-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .7rem .8rem;
    min-width: 0;
  }
  .create-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .create-summary-value {
    font-size: .94rem;
    font-weight: 700;
    color: #14532d;
  }
  .room-form-card {
    border: 1px solid rgba(2,8,20,.08) !important;
    box-shadow: 0 14px 30px rgba(2,8,20,.08) !important;
    border-radius: 1rem;
    overflow: hidden;
  }
  .room-form-card .card-body {
    padding: 1.35rem;
  }
  .room-split-grid {
    align-items: stretch;
  }
  .edit-panel {
    border: 1px solid rgba(2,8,20,.09);
    border-radius: 1rem;
    background: linear-gradient(180deg, #ffffff 0%, #fcfffd 100%);
    padding: 1rem;
    box-shadow: 0 8px 20px rgba(2,8,20,.05);
  }
  .cover-panel {
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
  }
  .section-kicker {
    font-size: .78rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: rgba(2,8,20,.6);
    font-weight: 700;
  }
  .room-form-card .form-label {
    font-weight: 600;
    color: #0f172a;
  }
  .room-form-card .form-control,
  .room-form-card .form-select {
    border-color: rgba(2,8,20,.14);
    background: #ffffff;
  }
  .room-cover-preview {
    border-radius: .75rem;
    border: 1px solid rgba(2,8,20,.14);
  }
  .detail-photos-panel {
    border: 1px solid rgba(2,8,20,.1);
    border-radius: 1rem;
    background: #ffffff;
    padding: 1rem;
    box-shadow: 0 8px 20px rgba(2,8,20,.05);
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
    border-top: 1px solid rgba(2,8,20,.08);
  }
  @media (max-width: 991.98px) {
    .create-summary {
      grid-template-columns: 1fr;
    }
    .room-create-shell {
      padding: .95rem;
    }
    .cover-panel {
      background: #ffffff;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('roomImageInput');
    const wrap = document.getElementById('roomImagePreviewWrap');
    const img = document.getElementById('roomImagePreview');

    if (input && wrap && img) {
      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) {
          wrap.style.display = 'none';
          img.removeAttribute('src');
          return;
        }
        const url = URL.createObjectURL(file);
        img.src = url;
        wrap.style.display = '';
        img.onload = () => URL.revokeObjectURL(url);
      });
    }

    const form = document.getElementById('createRoomForm');
    const inclusionsList = document.getElementById('inclusionsList');
    const addInclusionBtn = document.getElementById('addInclusionBtn');
    const inclusionsSerialized = document.getElementById('inclusionsSerialized');
    const addDetailSlotBtn = document.getElementById('addDetailSlotBtn');
    const newDetailSlots = document.getElementById('newDetailSlots');

    if (!form || !inclusionsList || !addInclusionBtn || !inclusionsSerialized) return;

    const createRow = (value = '') => {
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
      inclusionsList.appendChild(createRow());
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

    form.addEventListener('submit', () => {
      serializeInclusions();
    });

    serializeInclusions();

    const wireDetailPreview = (inputEl) => {
      inputEl.addEventListener('change', (event) => {
        const slot = event.target.closest('.new-detail-slot');
        if (!slot) return;
        const previewImg = slot.querySelector('.detail-preview-img');
        const previewEmpty = slot.querySelector('.detail-preview-empty');
        const file = event.target.files && event.target.files[0];

        if (!file) {
          previewImg.src = '';
          previewImg.classList.add('d-none');
          previewEmpty.classList.remove('d-none');
          return;
        }

        const url = URL.createObjectURL(file);
        previewImg.src = url;
        previewImg.classList.remove('d-none');
        previewEmpty.classList.add('d-none');
        previewImg.onload = () => URL.revokeObjectURL(url);
      });
    };

    const updateDetailSlotState = () => {
      const slots = Array.from(document.querySelectorAll('#newDetailSlots .new-detail-slot'));
      slots.forEach((slot, index) => {
        const label = slot.querySelector('.slot-index');
        if (label) label.textContent = `Photo ${index + 1}`;
        const removeBtn = slot.querySelector('.remove-slot-btn');
        if (!removeBtn) return;
        if (slots.length === 1) {
          removeBtn.classList.add('d-none');
        } else {
          removeBtn.classList.remove('d-none');
        }
      });
    };

    window.removeDetailSlot = (button) => {
      const slot = button.closest('.new-detail-slot');
      if (!slot) return;
      slot.remove();
      updateDetailSlotState();
    };

    if (addDetailSlotBtn && newDetailSlots) {
      addDetailSlotBtn.addEventListener('click', () => {
        const first = newDetailSlots.querySelector('.new-detail-slot');
        if (!first) return;
        const clone = first.cloneNode(true);

        const fileInput = clone.querySelector('.detail-file-input');
        if (fileInput) fileInput.value = '';

        const textInput = clone.querySelector('input[name="detail_labels[]"]');
        if (textInput) textInput.value = '';

        const previewImg = clone.querySelector('.detail-preview-img');
        const previewEmpty = clone.querySelector('.detail-preview-empty');
        if (previewImg) {
          previewImg.src = '';
          previewImg.classList.add('d-none');
        }
        if (previewEmpty) {
          previewEmpty.classList.remove('d-none');
        }

        newDetailSlots.appendChild(clone);
        const newInput = clone.querySelector('.detail-file-input');
        if (newInput) wireDetailPreview(newInput);
        updateDetailSlotState();
      });

      const firstInput = newDetailSlots.querySelector('.detail-file-input');
      if (firstInput) wireDetailPreview(firstInput);
      updateDetailSlotState();
    }
  });
</script>
@endpush
@endsection
