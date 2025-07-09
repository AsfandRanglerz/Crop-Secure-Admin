<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Crop Secure</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <div style="text-align:center;">
        <img src="{{ $user['logo'] ?? asset('public/admin/assets/img/logo.png') }}" 
             alt="Crop Secure Logo" style="margin-bottom: 35px; height: 125px;">
        <h2>Welcome to Crop Secure!</h2>
    </div>

    <p>Dear {{ $user['name'] ?? 'User' }},</p>

    <p>Your account as a <strong>Sub-Admin</strong> has been successfully created on <strong>Crop Secure</strong>.</p>

    <p><strong>Your login details:</strong></p>
    <ul>
        <li><strong>URL:</strong> <a href="{{ $user['url'] ?? '#' }}">{{ $user['url'] ?? 'N/A' }}</a></li>
        <li><strong>Email:</strong> {{ $user['email'] ?? 'N/A' }}</li>
        <li><strong>Password:</strong> {{ $user['password'] ?? 'N/A' }}</li>
    </ul>

    <p><em>Note: If you face any issues accessing the system, please contact the admin team for assistance.</em></p>

    <hr>

    <p><strong>Contact Admin:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $user['admin_email'] ?? 'admin@cropsecure.com' }}</li>
        <li><strong>Phone:</strong> {{ $user['admin_phone'] ?? '+92-300-0000000' }}</li>
    </ul>

    <p>We look forward to working with you.</p>
</body>
</html>
