# Validation and State Management

## 1. Client-Side Step Validation
- Before proceeding from a question panel, critical required fields are verified:
  - Panel 0: Room number and positive numeric capacity
  - Panel 1: Valid positive pricing corresponding to active pricing model
- Non-blocking optional panels (Inclusions, Photos) permit proceeding directly.

## 2. Server-Side Authoritative Validation
- Form submission routes to existing controllers:
  - `PropertyController@update`
  - `RoomController@update`
- In the event of validation failure (`$errors->any()`), the initial panel is dynamically computed based on error keys (`$initialPanel`), ensuring the user lands directly on the step requiring correction.
