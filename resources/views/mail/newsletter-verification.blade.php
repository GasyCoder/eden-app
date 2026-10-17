@php($en = $language === 'en')
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'fr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $en ? 'Confirm your email' : 'Confirmez votre e-mail' }}</title></head>
<body style="margin:0;padding:32px 16px;background:#F6F8F6;color:#101412;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:auto;background:#fff;border-radius:12px;">
        <tr><td style="padding:32px;">
            <p style="color:#13692C;font-weight:bold;">EDEN · Université de Mahajanga</p>
            <h1 style="font-size:24px;">{{ $en ? 'Confirm your email address' : 'Confirmez votre adresse e-mail' }}</h1>
            <p style="line-height:1.6;">{{ $en ? 'Click below to confirm your newsletter request. An administrator will then review it before you receive any news.' : 'Cliquez ci-dessous pour confirmer votre demande d’abonnement. Un administrateur devra ensuite la valider avant que vous receviez des actualités.' }}</p>
            <p style="margin:24px 0;"><a href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 22px;border-radius:8px;background:#13692C;color:#fff;text-decoration:none;">{{ $en ? 'Confirm my email' : 'Confirmer mon e-mail' }}</a></p>
            <p style="line-height:1.6;color:#5F6A63;">{{ $en ? 'This link expires in 24 hours. If you did not request this subscription, ignore this email. You will not be subscribed.' : 'Ce lien expire dans 24 heures. Si vous n’êtes pas à l’origine de cette demande, ignorez cet e-mail : aucun abonnement ne sera activé.' }}</p>
            <p style="font-size:12px;line-height:1.6;overflow-wrap:anywhere;">{{ $en ? 'If the button does not work, copy this link into your browser:' : 'Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :' }}<br><a href="{{ $verificationUrl }}" style="color:#13692C;word-break:break-all;">{{ $verificationUrl }}</a></p>
        </td></tr>
    </table>
</body>
</html>
