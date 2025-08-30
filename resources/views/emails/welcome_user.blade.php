<!DOCTYPE html>
<html>
<head>
    <title>Welcome to {{ $businessName }}</title>
</head>
<body>
    <h1>Welcome to {{ $businessName }}, {{ $user->first_name }}!</h1>
    <p>Your account has been successfully created under the business: <strong>{{ $businessName }}</strong>.</p>
    <p>Below are your login credentials:</p>

    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>

    <p>You can download our app here:</p>
    <p>
        <a href="{{ $appDownloadLink }}" style="color: #fff; background-color: #007BFF; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Download App</a>
    </p>

    <p>After downloading, log in using the provided credentials.</p>
    <p>Thank you for joining {{ $businessName }}!</p>

    <p>Best Regards,<br><strong>The {{ $businessName }} Team</strong></p>
</body>
</html>
