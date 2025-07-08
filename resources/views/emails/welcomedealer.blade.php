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

    <p>To proceed further, kindly contact our team. They will help you:</p>
    <ul>
        <li>Verify your account</li>
        <li>Enable product/item listings on your dashboard</li>
        <li>Assist you in navigating the Crop Secure platform effectively</li>
    </ul>

    <p><strong>Your login details:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $data['useremail'] ?? $data['email'] ?? 'N/A' }}</li>
        <li><strong>Password:</strong> {{ $data['password'] ?? 'N/A' }}</li>
    </ul>

<p><em>Note: Your registration has been received. To proceed further, please contact the admin for verification and approval.</em></p>

    <hr>

    <p><strong>Contact Admin:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $data['admin_email'] ?? 'admin@cropsecure.com' }}</li>
        <li><strong>Phone:</strong> {{ $data['admin_phone'] ?? '+92-300-0000000' }}</li>
    </ul>

    <p>We look forward to working with you.</p>
</body>
</html>
