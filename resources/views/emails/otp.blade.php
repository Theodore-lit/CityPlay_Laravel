<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Votre code de vérification CityPlay</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0a0a14;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #151521;
            border: 1px solid #0070ff33;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }
        .header {
            background: linear-gradient(135deg, #0070ff, #00f2ff);
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 900;
            font-style: italic;
        }
        .content {
            padding: 40px;
            text-align: center;
        }
        .otp-box {
            background-color: #0070ff1a;
            border: 1px dashed #0070ff;
            border-radius: 16px;
            padding: 20px;
            margin: 30px 0;
            font-size: 42px;
            font-weight: 900;
            letter-spacing: 8px;
            color: #00f2ff;
            text-shadow: 0 0 10px rgba(0, 242, 255, 0.5);
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #ffffff66;
            border-top: 1px solid #ffffff11;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #0070ff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CITYPLAY</h1>
        </div>
        <div class="content">
            <h2>Vérification de votre compte</h2>
            <p>Bonjour {{ $name }},</p>
            <p>Utilisez le code ci-dessous pour valider votre inscription sur CityPlay. Ce code est valable pendant 10 minutes.</p>
            
            <div class="otp-box">
                {{ $otpCode }}
            </div>
            
            <p>Si vous n'avez pas demandé ce code, vous pouvez ignorer cet e-mail en toute sécurité.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CityPlay — L'aventure urbaine commence ici.
        </div>
    </div>
</body>
</html>
