<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; color:#334155; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0;">Secure your account with a new password. This link expires in {{ $expiresIn }} minutes.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background:#f1f5f9;">
    <tr>
        <td align="center" style="padding:40px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:600px;">
                <tr>
                    <td style="padding:0 4px 24px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td align="center" width="44" height="44" style="width:44px; height:44px; border-radius:12px; background:#2563eb; color:#ffffff; font-size:22px; font-weight:700;">N</td>
                                <td style="padding-left:12px;">
                                    <div style="font-size:18px; line-height:22px; font-weight:700; color:#0f172a;">NoahFace</div>
                                    <div style="font-size:10px; line-height:14px; font-weight:700; letter-spacing:2px; color:#64748b;">SYNC</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="overflow:hidden; border:1px solid #e2e8f0; border-radius:16px; background:#ffffff; box-shadow:0 8px 24px rgba(15,23,42,.06);">
                        <div style="height:6px; background:#2563eb;"></div>
                        <div style="padding:42px 44px 38px;">
                            <div style="display:inline-block; margin-bottom:22px; padding:7px 11px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:11px; line-height:14px; font-weight:700; letter-spacing:1px;">ACCOUNT SECURITY</div>
                            <h1 style="margin:0 0 18px; color:#0f172a; font-size:30px; line-height:38px; font-weight:700; letter-spacing:-.5px;">Reset your password</h1>
                            <p style="margin:0 0 14px; font-size:16px; line-height:26px; color:#475569;">Hi {{ $name ?: 'there' }},</p>
                            <p style="margin:0 0 28px; font-size:16px; line-height:26px; color:#475569;">We received a request to reset the password for your NoahFace Sync account. Use the secure button below to choose a new password.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="border-radius:10px; background:#2563eb;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block; padding:15px 24px; border-radius:10px; color:#ffffff; font-size:16px; line-height:20px; font-weight:700; text-decoration:none;">Reset password&nbsp;&nbsp;→</a>
                                    </td>
                                </tr>
                            </table>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:30px; border-radius:10px; background:#f8fafc;">
                                <tr>
                                    <td style="padding:16px 18px; font-size:14px; line-height:21px; color:#64748b;"><strong style="color:#334155;">For your security:</strong> This link expires in {{ $expiresIn }} minutes and can only be used once.</td>
                                </tr>
                            </table>
                            <p style="margin:26px 0 0; font-size:14px; line-height:22px; color:#64748b;">If you didn’t request this change, you can safely ignore this email. Your password will remain unchanged.</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:24px 20px 0; font-size:12px; line-height:19px; color:#94a3b8;">This is an automated security message from NoahFace Sync.<br>Please don’t reply to this email.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
