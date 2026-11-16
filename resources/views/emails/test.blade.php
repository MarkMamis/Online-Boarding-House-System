<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OBHS Email Integration Test</title>
    <style>
        body {
            margin: 0; padding: 0; background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        .wrapper { width: 100%; padding: 32px 0 40px; }
        .main {
            background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px;
            border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header { background: linear-gradient(135deg, #14532d 0%, #166534 100%); padding: 24px 32px; }
        .header h1 { color: #ffffff; font-size: 18px; margin: 0; font-weight: 700; }
        .header p { color: #bbf7d0; font-size: 13px; margin: 4px 0 0; }
        .content { padding: 32px; }
        .badge { display: inline-block; background-color: #dcfce7; color: #166534; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; margin-bottom: 16px; }
        .footer { border-top: 1px solid #f1f5f9; padding: 20px 32px; background-color: #f8fafc; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Online Boarding House System</h1>
                <p>Mindoro State University (MinSU)</p>
            </div>
            <div class="content">
                <span class="badge">Integration Test Successful</span>
                <h2 style="margin: 0 0 16px; font-size: 20px; color: #0f172a;">Resend Mail Transport Verified</h2>
                <p style="font-size: 15px; line-height: 1.6; color: #475569;">
                    This is a verification email dispatched via the <strong>{{ $mailer }}</strong> mailer on {{ $sentAt }}.
                </p>
                <div style="background-color: #f8fafc; border-left: 4px solid #16a34a; padding: 14px 18px; border-radius: 6px; font-size: 14px; color: #334155; margin-top: 20px;">
                    <strong>Environment:</strong> {{ $environment }}<br>
                    <strong>Recipient:</strong> {{ $recipientEmail }}<br>
                    <strong>Application URL:</strong> {{ $appUrl }}
                </div>
            </div>
            <div class="footer">
                <p>Online Boarding House System (OBHS) &bull; Mindoro State University</p>
            </div>
        </div>
    </div>
</body>
</html>
