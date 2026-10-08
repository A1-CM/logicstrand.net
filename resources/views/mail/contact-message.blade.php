<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>New LogicStrand contact message</title></head>
<body style="font-family:Arial,sans-serif;color:#172039;line-height:1.6">
    <h1 style="font-size:20px">New contact message</h1>
    <p><strong>Name:</strong> {{ $name }}<br>
    <strong>Email:</strong> {{ $email }}<br>
    @if($organization)<strong>Organization:</strong> {{ $organization }}<br>@endif
    <strong>Subject:</strong> {{ $subjectLine }}</p>
    <hr>
    <p style="white-space:pre-wrap">{{ $body }}</p>
</body>
</html>
