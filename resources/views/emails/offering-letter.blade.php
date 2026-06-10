<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penawaran Pekerjaan</title>
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
            background-color: #6b38d4;
            padding: 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
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
        .highlight-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 24px;
            margin: 32px 0;
        }
        .highlight-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .highlight-box td {
            padding: 6px 0;
            font-size: 15px;
        }
        .highlight-box td.label {
            color: #6b7280;
            font-weight: 500;
            width: 140px;
        }
        .highlight-box td.value {
            color: #111827;
            font-weight: 600;
        }
        .btn-container {
            text-align: center;
            margin: 40px 0;
        }
        .btn {
            display: inline-block;
            background-color: #6b38d4;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(107, 56, 212, 0.2);
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #592cb3;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
        .warning-text {
            color: #da1a1a;
            font-weight: 600;
            font-size: 14px;
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Surat Penawaran Pekerjaan</h1>
        </div>
        <div class="content">
            @include('emails.templates.offering-text', ['name' => $candidate->name, 'jobTitle' => $vacancy->job_title])
            
            <div class="highlight-box">
                <table>
                    <tr>
                        <td class="label">Posisi Jabatan</td>
                        <td class="value">{{ $vacancy->job_title }}</td>
                    </tr>
                    <tr>
                        <td class="label">Departemen</td>
                        <td class="value">{{ $vacancy->department }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tipe Pekerjaan</td>
                        <td class="value" style="text-transform: capitalize;">{{ $vacancy->employment_type }}</td>
                    </tr>
                </table>
            </div>

            <div class="btn-container">
                <a href="{{ url('/offering/'.$token) }}" class="btn">Tinjau Penawaran</a>
            </div>

            <span class="warning-text">
                * Anda memiliki waktu 3 hari (hingga {{ $expiresAt->format('d M Y H:i') }}) untuk merespons tawaran ini.
            </span>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh Sistem Rekrutmen Internal HR.</p>
            <p>&copy; {{ date('Y') }} HR Recruitment System. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
