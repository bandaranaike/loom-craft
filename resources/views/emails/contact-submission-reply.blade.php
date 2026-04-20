<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <p>Hello {{ $contactSubmission->name }},</p>

    <p>{!! nl2br(e($replyMessage)) !!}</p>

    <p>Original message:</p>

    <blockquote style="margin: 0; padding-left: 16px; border-left: 4px solid #d1d5db; color: #4b5563;">
        {!! nl2br(e($contactSubmission->message)) !!}
    </blockquote>

    <p style="margin-top: 24px;">LoomCraft Support</p>
</body>
</html>
