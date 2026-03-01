@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Edit Room — {{ $property->name }}</h1>
        <div class="text-muted small">Property address: {{ $property->address }}</div>
    </div>
    <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary">Back to Property</a>
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

<form id="updateRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.update', [$property->id, $room->id]) }}">
  @csrf
  @method('PUT')

  {{-- ── Room Details ─────────────────────────────────────────── --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Room Details</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Room Number</label>
          <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Capacity</label>
          <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Price (₱)</label>
          <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $room->price) }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="available" @selected(old('status', $room->status)==='available')>Available</option>
            <option value="occupied" @selected(old('status', $room->status)==='occupied')>Occupied</option>
            <option value="maintenance" @selected(old('status', $room->status)==='maintenance')>Maintenance</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Included in Rent (optional)</label>
          <textarea name="inclusions" class="form-control" rows="3" placeholder="e.g., Electric fan, WiFi, Water, Bed frame">{{ old('inclusions', $room->inclusions) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Room Cover Photo ─────────────────────────────────────── --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Room Cover Photo</div>
    <div class="card-body">
      <p class="text-muted small mb-3">This is the main photo shown on listings and search results.</p>

      @if(!empty($room->image_path))
      <div class="mb-3">
        <div class="position-relative d-inline-block">
          <img src="{{ asset('storage/'.$room->image_path) }}" alt="Cover photo"
               class="img-thumbnail" style="height:200px; width:300px; object-fit:cover;">
          <span class="badge bg-success position-absolute top-0 start-0 m-1">Current Cover</span>
        </div>
      </div>
      @endif

      <label class="form-label">{{ !empty($room->image_path) ? 'Replace Cover Photo' : 'Upload Cover Photo' }} <span class="text-muted">(optional)</span></label>
      <input type="file" name="image" class="form-control" accept="image/*" id="coverInput">
      <div class="form-text">Leave blank to keep the current cover photo. Max 4 MB.</div>
      <div class="mt-2" id="coverPreviewWrap" style="display:none;">
        <img id="coverPreview" src="" alt="Preview" class="img-thumbnail" style="height:160px; object-fit:cover;">
      </div>
    </div>
  </div>

  {{-- ── Room Detail Photos ───────────────────────────────────── --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Room Detail Photos</div>
    <div class="card-body">
      <p class="text-muted small mb-3">Add labelled detail photos such as the bed, kitchen, comfort room, etc.</p>

      {{-- Existing detail images --}}
      @if($roomImages->isNotEmpty())
      <div class="row g-3 mb-4" id="existingDetailImages">
        @foreach($roomImages as $img)
        <div class="col-sm-6 col-md-4 col-lg-3" id="detail-img-{{ $img->id }}">
          <div class="card h-100 border">
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
      <div id="newDetailSlots">
        <div class="new-detail-slot mb-3 border rounded p-3 bg-light" data-index="0">
          <div class="row g-2 align-items-center">
            <div class="col-md-5">
              <label class="form-label small fw-semibold mb-1">New Photo</label>
              <input type="file" name="detail_images[]" class="form-control form-control-sm detail-file-input" accept="image/*">
              <div class="mt-2 detail-preview-wrap" style="display:none;">
                <img src="" alt="Preview" class="img-thumbnail" style="height:120px; object-fit:cover;">
              </div>
            </div>
            <div class="col-md-5">
              <label class="form-label small fw-semibold mb-1">Label <span class="text-muted">(optional)</span></label>
              <input type="text" name="detail_labels[]" class="form-control form-control-sm" placeholder="e.g. Bed, Kitchen, Comfort Room">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="button" class="btn btn-sm btn-outline-danger remove-slot-btn" style="display:none;" onclick="removeSlot(this)">✕ Remove</button>
            </div>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addDetailSlot()">
        + Add Another Photo
      </button>
    </div>
  </div>

  {{-- ── Save button ──────────────────────────────────────────── --}}
  <div class="d-flex justify-content-end mb-5">
    <button type="submit" form="updateRoomForm" class="btn btn-brand px-5">Save Changes</button>
  </div>
</form>

{{-- ── Danger Zone (Delete Room) ── kept FAR from Save button ── --}}
<div class="card border-danger mt-2 mb-5">
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
  clone.querySelector('.detail-preview-wrap').style.display = 'none';
  clone.querySelector('.detail-preview-wrap img').src = '';
  clone.querySelector('.remove-slot-btn').style.display = 'inline-block';
  // wire file preview
  clone.querySelector('.detail-file-input').addEventListener('change', handleDetailPreview);
  container.appendChild(clone);
  slotIndex++;
}

// Remove a slot
function removeSlot(btn) {
  btn.closest('.new-detail-slot').remove();
}

// Detail photo preview
function handleDetailPreview(e) {
  const wrap = e.target.closest('.new-detail-slot').querySelector('.detail-preview-wrap');
  const img = wrap.querySelector('img');
  if (e.target.files && e.target.files[0]) {
    img.src = URL.createObjectURL(e.target.files[0]);
    wrap.style.display = 'block';
  } else {
    wrap.style.display = 'none';
  }
}

// Wire preview on first slot
document.querySelector('.detail-file-input').addEventListener('change', handleDetailPreview);

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
</script>
@endsection
