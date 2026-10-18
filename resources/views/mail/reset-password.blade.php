<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Réinitialisez votre mot de passe EDEN-UMG</title>
    <style>
        @media only screen and (max-width: 480px) {
            .email-outer { padding: 20px 12px !important; }
            .email-content { padding-left: 22px !important; padding-right: 22px !important; }
            .email-title { font-size: 25px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#F3F6F4;color:#142019;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">Choisissez un nouveau mot de passe pour votre compte EDEN. Votre lien personnel est valable {{ $expiresInMinutes }} minutes.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F6F4;">
        <tr><td class="email-outer" align="center" style="padding:36px 16px;">
            <!--[if mso]><table role="presentation" width="560" align="center"><tr><td><![endif]-->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFFFF;border:1px solid #E1E8E3;border-radius:16px;overflow:hidden;">
                <tr><td height="5" style="height:5px;background:#13692C;font-size:0;line-height:0;">&nbsp;</td></tr>
                <tr><td class="email-content" style="padding:28px 32px 0;">
                    <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                        <td width="42" height="42" align="center" valign="middle" style="width:42px;height:42px;background:#13692C;border-radius:10px;color:#FFFFFF;font-size:19px;font-weight:700;">E</td>
                        <td style="padding-left:12px;">
                            <p style="margin:0;font-size:15px;line-height:1.4;font-weight:700;color:#142019;">EDEN · UMG</p>
                            <p style="margin:2px 0 0;font-size:12px;line-height:1.5;color:#647168;">École Doctorale Écosystèmes Naturels</p>
                        </td>
                    </tr></table>
                </td></tr>
                <tr><td class="email-content" style="padding:30px 32px 32px;">
                    <p style="margin:0 0 10px;font-size:12px;line-height:1.5;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#13692C;">Sécurité de votre compte</p>
                    <h1 class="email-title" style="margin:0;font-size:29px;line-height:1.25;letter-spacing:-.5px;color:#142019;">Un nouveau<br>mot de passe</h1>
                    <p style="margin:24px 0 10px;font-size:15px;line-height:1.7;color:#142019;">Bonjour {{ $recipientName }},</p>
                    <p style="margin:0;font-size:15px;line-height:1.7;color:#536158;">Une réinitialisation du mot de passe de votre compte EDEN a été demandée. Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.</p>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                        <tr><td align="center">
                            <table role="presentation" cellpadding="0" cellspacing="0"><tr><td align="center" bgcolor="#13692C" style="background:#13692C;border-radius:8px;mso-padding-alt:16px 22px;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;padding:16px 22px;border:1px solid #13692C;border-radius:8px;color:#FFFFFF;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.5;font-weight:700;text-align:center;text-decoration:none;mso-padding-alt:0;">Réinitialiser mon mot de passe</a>
                            </td></tr></table>
                        </td></tr>
                    </table>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F8F6;border:1px solid #DDE7E0;border-radius:10px;">
                        <tr><td style="padding:18px 20px;">
                            <p style="margin:0 0 6px;font-size:14px;line-height:1.5;font-weight:700;color:#142019;">Votre lien expire dans {{ $expiresInMinutes }} minutes</p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#536158;">Ce lien est personnel et utilisable une seule fois. Votre mot de passe actuel reste valable tant que vous n’en avez pas choisi un nouveau.</p>
                        </td></tr>
                    </table>
                    <p style="margin:22px 0 0;font-size:13px;line-height:1.7;color:#536158;">Si vous n’avez pas demandé cette réinitialisation, vous pouvez ignorer cet e-mail.</p>
                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #E8EDE9;">
                        <p style="margin:0 0 8px;font-size:12px;line-height:1.7;color:#647168;">Le bouton ne fonctionne pas ? Copiez ce lien dans votre navigateur :</p>
                        <p style="margin:0;font-size:12px;line-height:1.7;word-break:break-all;overflow-wrap:anywhere;"><a href="{{ $resetUrl }}" style="color:#13692C;text-decoration:underline;word-break:break-all;">{{ $resetUrl }}</a></p>
                    </div>
                </td></tr>
            </table>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                <tr><td align="center" style="padding:20px 16px 0;font-size:11px;line-height:1.8;color:#7A857D;">École Doctorale Écosystèmes Naturels<br>Université de Mahajanga<br>Message automatique · Merci de ne pas répondre à cet e-mail.</td></tr>
            </table>
            <!--[if mso]></td></tr></table><![endif]-->
        </td></tr>
    </table>
</body>
</html>
