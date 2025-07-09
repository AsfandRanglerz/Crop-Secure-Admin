<!DOCTYPE html>
<html>

<head>
    <title>Password Reset - Crop Secure</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">

        <div style="text-align:center;">
            <img src="{{ $data['logo'] ?? asset('public/admin/assets/img/logo.png') }}" alt="Crop Secure Logo"
                style="margin-bottom: 25px; height: 100px;">
            <h2 style="color: #2f855a;">Reset Your Password</h2>
        </div>

        <p style="font-size: 16px; color: #333;">
            We have received a request to reset your password. Click the button below to proceed:
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $data['url'] }}"
                style="background-color: #3490dc; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-size: 16px;">
                Reset Password
            </a>
        </div>

        <p style="font-size: 14px; color: #555;">
            If you did not request this, please ignore this email
        </p>

        <hr style="margin: 30px 0;">

        <p style="font-size: 14px; color: #888;">
            Thanks,<br>
            <strong>{{ config('app.name', 'Crop Secure') }}</strong>
        </p>
    </div>
</body>

</html>
