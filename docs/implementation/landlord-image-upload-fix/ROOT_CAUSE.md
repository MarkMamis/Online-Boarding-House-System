# Root Cause Analysis: Landlord Mobile Image Upload Failures

## Reported Production Incident
A landlord attempting to upload a room photo on the live production site (`mcc-boardinghouse.online`) from an Android phone using Facebook Messenger's in-app webview encountered the error:
> *"Please check the following:*
> *The image field must not be greater than 2048 kilobytes."*

---

## Technical Root Cause

### 1. Inconsistent & Overly Restrictive Controller Validation
Prior to this fix, the room and property endpoints had disparate and outdated file size validation rules:

| Controller Method | Endpoint | Old Rule for `image` | Old Rule for `detail_images.*` |
|---|---|---|---|
| `RoomController@store` | `POST /landlord/properties/{property}/rooms` | `max:2048` (2 MB) | `max:4096` (4 MB) |
| `RoomController@quickStore` | `POST /landlord/dashboard/properties/{property}/rooms` | `max:2048` (2 MB) | N/A |
| `RoomController@update` | `PUT /landlord/properties/{property}/rooms/{room}` | `max:4096` (4 MB) | `max:4096` (4 MB) |
| `PropertyController@store` | `POST /landlord/properties` | `max:5120` (5 MB) | N/A |
| `PropertyController@update` | `PUT /landlord/properties/{property}` | `max:5120` (5 MB) | N/A |
| `PropertyController@autoSave` | `POST /landlord/properties/autosave` | `max:5120` (5 MB) | N/A |

When a landlord used their smartphone camera (modern Android / iOS devices produce photos ranging between 2.5 MB to 8 MB), `RoomController@store` rejected the upload immediately at 2048 KB with Laravel's raw default validation string.

### 2. Lack of Automatic In-Browser Compression
There was no client-side optimization in place. Landlords were expected to manually compress their photos using third-party tools before uploading, which is an unrealistic expectation for non-technical users on mobile phones and webviews.

### 3. Missing Exception Handling for Server-Level Post Size
If a landlord attempted to upload multiple high-resolution photos simultaneously exceeding PHP's `post_max_size`, PHP dropped the request body, causing unhandled `PostTooLargeException` rather than providing an actionable, friendly message to the landlord.
