@php
    $locale = ($locale ?? 'en') === 'ar' ? 'ar' : 'en';
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';

    $copy = $locale === 'ar'
        ? [
            'preheader' => 'رابط آمن لإعادة تعيين كلمة المرور الخاصة بك.',
            'greeting' => 'مرحباً '.($user->name ?: '—'),
            'title' => 'إعادة تعيين كلمة المرور',
            'body' => 'تلقّينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك. اضغط على الزر أدناه لإنشاء كلمة مرور جديدة.',
            'cta' => 'إعادة تعيين كلمة المرور',
            'expiry' => 'ستنتهي صلاحية هذا الرابط خلال :minutes دقيقة.',
            'ignore' => 'إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة بكل أمان.',
            'fallback' => 'إذا لم يعمل الزر، انسخ هذا الرابط والصقه في المتصفح:',
            'signature' => 'مع التحية',
            'team' => config('app.name', 'Laravel'),
        ]
        : [
            'preheader' => 'A secure link to reset your account password.',
            'greeting' => 'Hello '.($user->name ?: 'there').',',
            'title' => 'Reset your password',
            'body' => 'We received a request to reset your account password. Use the button below to create a new one.',
            'cta' => 'Reset Password',
            'expiry' => 'This link will expire in :minutes minutes.',
            'ignore' => 'If you did not request a password reset, you can safely ignore this email.',
            'fallback' => 'If the button does not work, copy and paste this link into your browser:',
            'signature' => 'Regards',
            'team' => config('app.name', 'Laravel'),
        ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $copy['title'] }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; color:#1e293b; font-family:{{ $locale === 'ar' ? '\'Tajawal\', Arial, sans-serif' : '\'Inter\', Arial, sans-serif' }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        {{ $copy['preheader'] }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;">
                    <tr>
                        <td style="padding-bottom:16px; text-align:center;">
                            <div style="display:inline-block; background:linear-gradient(135deg, #0B3D2E 0%, #115C42 100%); color:#ffffff; border-radius:24px; padding:14px 24px; font-size:14px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">
                                {{ config('app.name', 'Laravel') }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff; border:1px solid #e2e8f0; border-radius:24px; padding:40px 32px; box-shadow:0 10px 30px -10px rgba(11, 61, 46, 0.18);">
                            <p style="margin:0 0 16px; font-size:18px; font-weight:700; color:#0f172a;">{{ $copy['greeting'] }}</p>
                            <h1 style="margin:0 0 16px; font-size:30px; line-height:1.2; color:#115C42;">{{ $copy['title'] }}</h1>
                            <p style="margin:0 0 24px; font-size:16px; line-height:1.8; color:#475569;">{{ $copy['body'] }}</p>

                            <div style="margin:0 0 28px;">
                                <a href="{{ $resetUrl }}" style="display:inline-block; background:#115C42; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:24px; font-size:16px; font-weight:700;">
                                    {{ $copy['cta'] }}
                                </a>
                            </div>

                            <div style="margin:0 0 24px; background:#ecf5ef; border:1px solid #d0e6d6; border-radius:24px; padding:18px 20px;">
                                <p style="margin:0; font-size:14px; line-height:1.7; color:#115C42;">
                                    {{ str_replace(':minutes', (string) $expiresInMinutes, $copy['expiry']) }}
                                </p>
                            </div>

                            <p style="margin:0 0 20px; font-size:14px; line-height:1.8; color:#64748b;">{{ $copy['ignore'] }}</p>
                            <p style="margin:0 0 10px; font-size:13px; line-height:1.7; color:#64748b;">{{ $copy['fallback'] }}</p>
                            <p style="margin:0 0 28px; word-break:break-all;">
                                <a href="{{ $resetUrl }}" style="color:#115C42; font-size:13px; line-height:1.7;">{{ $resetUrl }}</a>
                            </p>

                            <p style="margin:0; font-size:14px; line-height:1.8; color:#475569;">
                                {{ $copy['signature'] }}<br>
                                <span style="font-weight:700; color:#0f172a;">{{ $copy['team'] }}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
