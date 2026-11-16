{{ $title }} - Online Boarding House System (OBHS)
Mindoro State University (MinSU)
============================================================

Hello {{ $recipientName }},

{{ $contentMessage }}

@if(!empty($meta['reason']))
Reason / Details:
{{ $meta['reason'] }}
@elseif(!empty($meta['due_date']))
Due Date: {{ $meta['due_date'] }}
@if(!empty($meta['gcash_summary']))
Payment Details: {{ $meta['gcash_summary'] }}
@endif
@endif

@if(!empty($actionUrl))
Action Link:
{{ $actionUrl }}
@endif

============================================================
This is an automated notification from the Online Boarding House System (OBHS).
(C) {{ date('Y') }} Mindoro State University. All rights reserved.
