<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Crop Secure</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <div style="text-align:center;">
        <img src="{{ $data['logo'] ?? asset('public/admin/assets/img/logo.png') }}" 
             alt="Crop Secure Logo" style="margin-bottom: 35px; height: 125px;">
        <h2>Welcome to Crop Secure!</h2>
    </div>

    <p>Dear {{ $data['name'] ?? 'User' }},</p>

    <p>Thank you for registering on <strong>Crop Secure</strong>. Your account has been created successfully.</p>

    <p><strong>Here are your login credentials:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $data['email'] }}</li>
        <li><strong>Contact:</strong> {{ $data['contact'] ?? 'N/A' }}</li>
        <li><strong>Password:</strong> {{ $data['password'] }}</li>
    </ul>

    <p><em>Please keep this information safe and do not share your login details with anyone.</em></p>

    <p>If you need help, feel free to contact:</p>
    <ul>
        <li><strong>Admin Email:</strong> {{ $data['admin_email'] ?? 'admin@cropsecure.com' }}</li>
        <li><strong>Admin Phone:</strong> {{ $data['admin_phone'] ?? '+92-300-0000000' }}</li>
    </ul>

    <p>Thank you,<br>The Crop Secure Team</p>
</body>
</html>
