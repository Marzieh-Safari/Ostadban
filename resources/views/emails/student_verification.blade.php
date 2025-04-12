<!DOCTYPE html>
<html>
<head>
    <title>تأیید ایمیل</title>
</head>
<body>
    <h1>سلام {{ $user->name }}!</h1>
    <p>برای تأیید ایمیل خود روی لینک زیر کلیک کنید:</p>
    <a href="{{ $verificationLink }}" style="...">تأیید ایمیل</a>
</body>
</html>