# Test Results & Regression Evidence

## 1. Targeted Feature Test Suite: `ChatbotRedirectAndPhotoUploadHotfixTest`
Command:
```powershell
php artisan test --filter=ChatbotRedirectAndPhotoUploadHotfixTest
```

### Result: 14/14 PASS (44 assertions)
```text
   PASS  Tests\Feature\ChatbotRedirectAndPhotoUploadHotfixTest
  ✓ authenticated landlord can get chatbot history as json                                                       2.36s  
  ✓ authenticated landlord can post chatbot message as json                                                      0.05s  
  ✓ landlord with completed profile is auto healed on chatbot request                                            0.03s  
  ✓ incomplete landlord setup returns 403 json for ajax                                                          0.03s  
  ✓ landlord setup show heals onboarding complete for completed accounts                                         0.04s  
  ✓ authenticated student can access chatbot                                                                     0.04s  
  ✓ authenticated admin can access chatbot                                                                       0.03s  
  ✓ unauthenticated request returns 401 json                                                                     0.03s  
  ✓ chatbot message validation failure returns 422                                                               0.06s  
  ✓ ai service failure returns graceful 500 json                                                                 0.04s  
  ✓ chatbot history is isolated per user                                                                         0.05s  
  ✓ room upload accepts photo between 2mb and 5mb                                                                0.08s  
  ✓ room upload rejects photo above 5mb with friendly error                                                      0.07s  
  ✓ property draft autosave accepts photo between 2mb and 5mb                                                    0.06s  

  Tests:    14 passed (44 assertions)
  Duration: 3.47s
```

---

## 2. Regression Suite: `LandlordPhotoUploadOptimizationTest`
Command:
```powershell
php artisan test --filter=LandlordPhotoUploadOptimizationTest
```

### Result: 24/24 PASS (82 assertions)
```text
   PASS  Tests\Feature\LandlordPhotoUploadOptimizationTest
  ✓ room creation accepts jpeg below 2mb                                                                         2.05s  
  ✓ room creation accepts jpeg between 2mb and 5mb                                                               0.06s  
  ✓ room creation accepts png within 5mb                                                                         0.09s  
  ✓ room creation accepts webp within 5mb                                                                        0.11s  
  ✓ room creation rejects image exceeding 5mb with friendly message                                              0.10s  
  ✓ room creation rejects invalid mime content                                                                   0.04s  
  ✓ room creation rejects malformed image                                                                        0.04s  
  ✓ room creation rejects unsupported format                                                                     0.04s  
  ✓ room creation succeeds without image                                                                         0.04s  
  ✓ room creation accepts additional detail images within 5mb                                                    0.10s  
  ✓ exact 5120kb boundary                                                                                        0.11s  
  ✓ property creation accepts image up to 5mb                                                                    0.08s  
  ✓ property creation rejects image exceeding 5mb                                                                0.08s  
  ✓ property editing replaces photo                                                                              0.08s  
  ✓ property editing retains existing photo when image not provided                                              0.07s  
  ✓ room editing replaces photo                                                                                  0.07s  
  ✓ room editing retains existing photo when unchanged                                                           0.05s  
  ✓ unauthorized landlord cannot upload to another landlords property                                            0.06s  
  ✓ unauthorized landlord cannot update another landlords room                                                   0.04s  
  ✓ property draft autosave accepts image up to 5mb                                                              0.06s  
  ✓ property draft autosave rejects image exceeding 5mb                                                          0.08s  
  ✓ no arbitrary file execution                                                                                  0.04s  
  ✓ room creation rejects more than 10 detail images                                                             0.05s  
  ✓ post too large exception handling                                                                            0.04s  

  Tests:    24 passed (82 assertions)
  Duration: 3.82s
```

---

## 3. Production Asset Build Verification
Command:
```powershell
npm run build
```

### Result: PASS (Exit Code 0)
```text
vite v7.3.1 building client environment for production...
transforming...
✓ 54 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json             0.33 kB │ gzip:  0.17 kB
public/build/assets/app-Ddjl58YA.css  44.19 kB │ gzip: 10.04 kB
public/build/assets/app-BxQyoG-E.js   39.71 kB │ gzip: 15.90 kB
✓ built in 2.49s
```

---

## 4. Blade View Compilation Verification
Command:
```powershell
php artisan view:cache
```

### Result: PASS (Exit Code 0)
```text
INFO  Blade templates cached successfully.
```

---

## 5. Route Verification
Command:
```powershell
php artisan route:list --path=chatbot -v
```

### Result: PASS (Exit Code 0)
```text
  GET|HEAD   chatbot/history ............................................. chatbot.history › ChatbotController@history
             ⇂ web
             ⇂ auth
             ⇂ verified
             ⇂ role.selected
  POST       chatbot/message ............................................. chatbot.message › ChatbotController@message
             ⇂ web
             ⇂ auth
             ⇂ verified
             ⇂ role.selected
             ⇂ throttle:chatbot
```
