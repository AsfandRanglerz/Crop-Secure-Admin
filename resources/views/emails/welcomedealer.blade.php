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

    <p>We’re excited to have you join <strong>Crop Secure</strong> — your trusted companion for agricultural data and crop management.</p>

    <p>With your account, you’ll be able to:</p>
    <ul>
        <li>Provide verified agricultural products to registered farmers</li>
        <li>Manage your product inventory through the dealer dashboard</li>
        <li>Stay informed about product guidelines and supply updates</li>
    </ul>

    <p>Here are your login credentials to access your dashboard:</p>
    <ul>
        <li><strong>Email:</strong> {{ $data['useremail'] ?? $data['email'] ?? 'N/A' }}</li>
        <li><strong>Password:</strong> {{ $data['password'] ?? 'N/A' }}</li>
    </ul>

    <p><em> keep this information safe and secure. Do not share your login credentials with anyone.</em></p>

    {{-- Uncomment the button below if you want to provide a login link --}}
    {{-- <p>
        <a href="{{ $data['url'] ?? 'http://yourdomain.com/login' }}" 
           style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">
           Access Your Account
        </a>
    </p> --}}

    <p>If you need any help or have questions, our support team is always ready to assist you.</p>

    <p>Welcome aboard!<br>The Crop Secure Team</p>
</body>
</html>
