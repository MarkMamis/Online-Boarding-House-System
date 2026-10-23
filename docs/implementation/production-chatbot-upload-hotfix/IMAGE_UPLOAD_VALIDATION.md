# Property & Room Image Upload Validation

## Unified Policy
- **Maximum Accepted File Size**: `5120 KiB` (5 MiB) per image.
- **Accepted Formats**: `JPEG` (`.jpg`, `.jpeg`), `PNG` (`.png`), `WebP` (`.webp`), `GIF` (`.gif`).
- **Additional Detail Photos Limit**: Up to 10 images per batch.
- **Friendly Validation Message**: `"This photo is too large. Please choose one under 5 MB."`

---

## Validated Endpoints & Rules

### 1. Room Creation (`RoomController@store`)
- **Route**: `POST /landlord/properties/{property}/rooms`
- **Validation Rules**:
  ```php
  'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
  'detail_images' => 'nullable|array|max:10',
  'detail_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
  'detail_labels.*' => 'nullable|string|max:100',
  ```
- **Custom Messages**:
  ```php
  'image.max' => 'This photo is too large. Please choose one under 5 MB.',
  'image.image' => 'Please select a valid image file (JPG, PNG, or WebP).',
  'image.mimes' => 'Please select a valid image file (JPG, PNG, or WebP).',
  'detail_images.max' => 'You can upload a maximum of 10 additional photos at once.',
  'detail_images.*.max' => 'One or more additional photos are too large. Please choose images under 5 MB.',
  ```

### 2. Room Update (`RoomController@update`)
- **Route**: `PUT /landlord/properties/{property}/rooms/{room}`
- **Validation Rules**: Identical to `store`, plus `delete_detail_images.* => 'integer'`.
- **Image Replacement**: If a new `image` is provided, the previous cover image is removed via `FileStorageService::replace` and the new image stored. If omitted, existing photos remain intact.

### 3. Quick Room Creation (`RoomController@quickStore`)
- **Route**: `POST /landlord/dashboard/properties/{property}/rooms`
- **Validation Rules**: `'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120'`.

### 4. Property Creation & Update (`PropertyController@store`, `@update`)
- **Routes**:
  - `POST /landlord/properties`
  - `PUT /landlord/properties/{property}`
- **Validation Rules**:
  ```php
  'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
  ```
- **Custom Messages**:
  ```php
  'image.max' => 'This photo is too large. Please choose one under 5 MB.',
  ```

### 5. Property Draft Auto-Save (`PropertyController@autoSave`)
- **Route**: `POST /landlord/properties/autosave` (JSON endpoint)
- **Validation Rules**:
  ```php
  'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
  ```
- Returns `422 Unprocessable Entity` with JSON validation error when exceeded.

---

## PostTooLargeException Global Handling
In `bootstrap/app.php`:
```php
$exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $exception, Request $request) {
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
            'success' => false,
            'message' => 'The upload is too large. Try fewer photos at once.',
            'errors' => [
                'image' => ['The upload is too large. Try fewer photos at once.'],
            ],
        ], 413);
    }

    return back()->withInput()->with('error', 'The upload is too large. Try fewer photos at once.');
});
```
This protects against PHP `post_max_size` violations when uploading multiple high-resolution photos.
