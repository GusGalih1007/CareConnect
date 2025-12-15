<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OTP Verification</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #333;">CareConnect OTP Verification</h2>
        <p>Gunakan kode OTP berikut untuk melanjutkan {{ strtolower ($purpose) }}</p>
        
        <div style="background-color: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;">
            <h1 style="font-size: 32px; letter-spacing: 10px; color: #007bff;">{{ $otp }}</h1>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            Kode ini akan kadaluarsa dalam 5 menit.
            Jangan bagikan kode ini kepada siapa pun.
        </p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px;">
            Jika Anda tidak meminta kode ini, abaikan email ini.
        </p>
    </div>
</body>
</html>