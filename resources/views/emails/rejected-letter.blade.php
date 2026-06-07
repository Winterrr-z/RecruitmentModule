<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Lamaran</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        .header {
            background-color: #f3f4f6;
            padding: 32px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .header h1 {
            color: #1f2937;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 40px;
            color: #1f2937;
            line-height: 1.6;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Status Lamaran</h1>
        </div>
        <div class="content">
            @include('emails.templates.rejected-text', ['name' => $name, 'jobTitle' => $jobTitle])
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh Sistem Rekrutmen Internal HR.</p>
            <p>&copy; {{ date('Y') }} HR Recruitment System. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
