<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title ?? 'Notification' }} - OBHS</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 32px 0 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #14532d 0%, #166534 100%);
            padding: 24px 32px;
            text-align: left;
        }
        .header h1 {
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.025em;
        }
        .header p {
            color: #bbf7d0;
            font-size: 13px;
            margin: 4px 0 0;
            font-weight: 500;
        }
        .content {
            padding: 32px;
        }
        .title {
            color: #0f172a;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 16px;
            line-height: 1.3;
        }
        .greeting {
            font-size: 15px;
            font-weight: 600;
            color: #334155;
            margin: 0 0 14px;
        }
        .body-text {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin: 0 0 20px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #16a34a;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 20px 0;
            font-size: 14px;
            line-height: 1.5;
            color: #334155;
        }
        .btn-wrapper {
            margin: 28px 0;
            text-align: left;
        }
        .btn {
            background-color: #166534;
            color: #ffffff !important;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            letter-spacing: 0.025em;
        }
        .footer {
            border-top: 1px solid #f1f5f9;
            padding: 24px 32px;
            background-color: #f8fafc;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .footer p {
            margin: 0 0 6px;
        }
        .fallback-link {
            font-size: 12px;
            color: #64748b;
            word-break: break-all;
            margin-top: 24px;
            line-height: 1.4;
        }
        .fallback-link a {
            color: #166534;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" width="100%">
            <tr>
                <td align="center">
                    <div class="main">
                        <div class="header">
                            <h1>Online Boarding House System</h1>
                            <p>Mindoro State University (MinSU)</p>
                        </div>
                        <div class="content">
                            <h2 class="title">{{ $title }}</h2>
                            <p class="greeting">Hello {{ $recipientName }},</p>
                            <p class="body-text">{{ $contentMessage }}</p>

                            @if(!empty($meta['reason']))
                                <div class="meta-box">
                                    <strong>Reason / Details:</strong><br>
                                    {{ $meta['reason'] }}
                                </div>
                            @elseif(!empty($meta['due_date']))
                                <div class="meta-box">
                                    <strong>Due Date:</strong> {{ $meta['due_date'] }}
                                    @if(!empty($meta['gcash_summary']))
                                        <br><strong>Payment Details:</strong> {{ $meta['gcash_summary'] }}
                                    @endif
                                </div>
                            @endif

                            @if(!empty($actionUrl))
                                <div class="btn-wrapper">
                                    <a href="{{ $actionUrl }}" class="btn" target="_blank" rel="noopener noreferrer">{{ $actionText ?? 'View in OBHS' }}</a>
                                </div>
                                <p class="fallback-link">
                                    If the button above does not work, open the following link directly in your browser:<br>
                                    <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
                                </p>
                            @endif
                        </div>
                        <div class="footer">
                            <p>This is an automated notification from the Online Boarding House System (OBHS).</p>
                            <p>&copy; {{ date('Y') }} Mindoro State University. All rights reserved.</p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
