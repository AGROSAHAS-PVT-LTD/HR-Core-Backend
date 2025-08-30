@php
    $settings = \App\Models\Settings::first();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Business Account Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 20px 0;
        }
        .content {
            padding: 20px;
            color: #333333;
        }
        .footer {
            text-align: center;
            padding: 10px 20px;
            font-size: 12px;
            background: #f4f4f9;
            color: #666666;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            color: #ffffff;
            background: #28a745;
            text-decoration: none;
            border-radius: 5px;
        }
        .info {
            margin-top: 20px;
            font-size: 14px;
            color: #555555;
        }
        .contact {
            margin-top: 30px;
            font-size: 14px;
        }
        .contact p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{$settings->app_name}}!</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}</strong>,</p>
            <p>Your business account "<strong>{{ $business->name }}</strong>" has been successfully created.</p>
            
            <h3>🛠️ Account Details:</h3>
            <ul>
                <li><strong>Business Name:</strong> {{ $business->name }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Phone Number:</strong> {{ $user->phone_number }}</li>
                <!--<li><strong>Username:</strong> {{ $user->user_name }}</li>-->
                <li><strong>Password:</strong> {{ $password }}</li>
            </ul>

            <p>You can access your admin panel using the button below:</p>
            <a href="{{ $adminUrl }}" class="button">Go to Admin Panel</a>

            <div class="info">
                <p>Please make sure to change your password after your first login for better security.</p>
            </div>

            <div class="contact">
                <h4>📞 Need Help?</h4>
                <p>Email: <a href="mailto:support@gps.digifrica.com">support@gps.digifrica.com</a></p>
                <p>Phone:  256-2344477898</p>
                <p>Website: <a href="https://gps.digifrica.com">gps.digifrica.com</a></p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{$settings->app_name}}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
