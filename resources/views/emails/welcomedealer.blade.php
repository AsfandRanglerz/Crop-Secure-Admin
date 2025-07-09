<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Crop Secure</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <div style="text-align:center;">
        <img src="{{ $data['logo'] ?? 'logo' }}" 
             alt="Crop Secure Logo" style="margin-bottom: 35px; height: 125px;">
        <h2>Welcome to Crop Secure!</h2>
    </div>

    <p>Dear {{ $data['name'] ?? 'User' }},</p>

    <p>Thank you for registering on <strong>Crop Secure</strong>.</p>

    <p>Your registration has been received. To move forward, please contact the admin to add your products for sale on crop secure.</p>
    <hr>

    <p><strong>Contact Admin:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $data['admin_email'] ?? 'admin@cropsecure.com' }}</li>
        <li><strong>Phone:</strong> {{ $data['admin_phone'] ?? '+92-300-0000000' }}</li>
    </ul>

    <p>We look forward to working with you.</p>
</body>
</html>
