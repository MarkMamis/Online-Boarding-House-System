# Production Smoke Test Verification Plan

Perform these non-destructive operational checks immediately after deploying the hotfix files to `https://mcc-boardinghouse.online`.

---

## 1. Chatbot Verification

### Step 1.1: Landlord Chatbot Access
1. Sign in as an active Landlord at `https://mcc-boardinghouse.online/login`.
2. Observe the green circular chatbot widget toggle in the bottom right corner of `/landlord/dashboard`.
3. Open the browser DevTools (F12) -> **Network** tab.
4. Click the chatbot toggle to open the panel:
   - Verify request: `GET /chatbot/history`.
   - Verify HTTP Status: **`200 OK`** (NOT `302 Found`).
   - Verify Response Content-Type: **`application/json`**.
   - Verify greeting message or previous conversation loads inside the widget bubbles.
5. In the message input, type: `"Hello assistant, how do I manage rooms?"` and click Send:
   - Verify request: `POST /chatbot/message`.
   - Verify HTTP Status: **`200 OK`** (NOT `302 Found`).
   - Verify assistant reply appears in a white bubble with actionable links if applicable.

### Step 1.2: Student Chatbot Access
1. Sign in as an active Student.
2. Open the chatbot widget:
   - Verify `GET /chatbot/history` returns **`200 OK`**.
3. Send a message: `"Are there rooms near MinSU?"`:
   - Verify assistant responds with appropriate guidance.

### Step 1.3: Session Expiry Behavior
1. In an Incognito window or after logging out, attempt an AJAX request to `/chatbot/history`:
   - Verify response: **`401 Unauthorized`** (JSON).
   - Verify widget shows: `"Your session has expired. Please sign in again."` rather than crashing.

---

## 2. Landlord Photo Upload Verification

### Step 2.1: Mobile Camera Photo Upload (Android / Messenger)
1. Using an Android smartphone (or Chrome DevTools responsive device mode), sign in as Landlord.
2. Navigate to **My Properties** -> select a property -> **Add Room** (`/landlord/properties/{id}/rooms/create`).
3. Under **Room Cover Photo**, tap **Choose Photo** and select an uncompressed camera photo (3 MB – 5 MB).
4. Observe the upload card:
   - Verify a brief `"Preparing photo..."` message appears while client-side canvas optimizes the photo.
   - Verify the thumbnail preview displays cleanly.
   - Verify the file size displayed is reduced to ~500 KB - 900 KB.
5. Fill Room Number and Capacity, click **Continue**, and navigate to **Review**.
6. Verify the selected photo thumbnail displays in the **Review** step.
7. Click **Create Room**:
   - Verify submission succeeds immediately without any 2048 KB error.
   - Verify the room appears in the property room list with the uploaded photo.

### Step 2.2: Photo Larger Than 5 MB Validation
1. Attempt to upload a photo exceeding 5 MB where canvas compression is disabled or file is an unoptimizable raw format:
   - Verify the user-friendly alert appears:
     `"This photo is too large. Please choose one under 5 MB."`
   - Verify form state and all entered text inputs are preserved.
