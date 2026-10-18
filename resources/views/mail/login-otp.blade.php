<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $resent ? 'Nouveau code de connexion EDEN-UMG' : 'Code de connexion EDEN-UMG' }}</title>
</head>
<body style="margin:0;padding:0;background:#F3F6F4;color:#142019;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Votre code de sécurité EDEN est valable {{ $expiresInMinutes }} minutes.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3F6F4;padding:32px 12px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFFFF;border:1px solid #E1E8E3;border-radius:14px;overflow:hidden;">
                <tr><td style="height:5px;background:#13692C;font-size:0;line-height:0;">&nbsp;</td></tr>
                <tr><td style="padding:28px 32px 8px;">
                    <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                        <td width="42" height="42" align="center" valign="middle" style="width:42px;height:42px;border-radius:10px;background:#13692C;color:#FFFFFF;font-size:16px;font-weight:700;letter-spacing:1px;">E</td>
                        <td style="padding-left:11px;">
                            <div style="font-size:15px;font-weight:700;line-height:1.3;color:#142019;">EDEN · UMG</div>
                            <div style="font-size:12px;line-height:1.4;color:#647168;">École Doctorale Écosystèmes Naturels</div>
                        </td>
                    </tr></table>
                </td></tr>
                <tr><td style="padding:24px 32px 32px;">
                    <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#13692C;">{{ $resent ? 'Nouveau code de sécurité' : 'Vérification sécurisée' }}</p>
                    <h1 style="margin:0;font-size:25px;line-height:1.25;color:#142019;">Votre code de connexion</h1>
                    <p style="margin:16px 0 0;font-size:15px;line-height:1.65;color:#536158;">Bonjour {{ $recipientName }}, utilisez ce code pour terminer votre connexion à l’espace d’administration EDEN.</p>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0 18px;background:#F5F8F6;border:1px solid #DDE7E0;border-radius:10px;">
                        <tr><td align="center" style="padding:20px 12px 8px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#647168;">CODE DE VÉRIFICATION</td></tr>
                        <tr><td align="center" style="padding:4px 12px 20px;font-family:Consolas,'Courier New',monospace;font-size:34px;line-height:1.2;font-weight:700;letter-spacing:9px;color:#13692C;">{{ $code }}</td></tr>
                    </table>
                    <p style="margin:0;font-size:14px;line-height:1.6;color:#536158;">Ce code expire dans <strong style="color:#142019;">{{ $expiresInMinutes }} minutes</strong> et ne peut être utilisé qu’une seule fois.</p>
                    <p style="margin:18px 0 0;padding-top:16px;border-top:1px solid #E8EDE9;font-size:13px;line-height:1.6;color:#647168;">Si vous n’êtes pas à l’origine de cette connexion, ignorez cet e-mail. Ne communiquez ce code à personne ; l’équipe EDEN ne vous le demandera jamais.</p>
                </td></tr>
            </table>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
                <tr><td align="center" style="padding:18px 24px;font-size:11px;line-height:1.6;color:#7A857D;">Message automatique de l’École Doctorale EDEN · Université de Mahajanga<br>Merci de ne pas répondre directement à cet e-mail.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
