<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Votre Code Secret CityPlay</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2d3748;
            margin: 0;
            font-size: 24px;
        }
        .code-box {
            background-color: #edf2f7;
            border: 2px dashed #4a5568;
            padding: 20px;
            margin: 30px 0;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #2d3748;
            display: inline-block;
            border-radius: 8px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue sur CityPlay, {{ $user->name }} !</h1>
        </div>
        <div class="content">
            <p>Merci de vous être inscrit. Voici votre <strong>code secret de joueur</strong> :</p>
            
            <div class="code-box">
                {{ $user->secret_code }}
            </div>
            
            <p>Conservez précieusement ce code. Il pourrait vous être demandé pour accéder à certaines zones exclusives ou pour valider vos exploits.</p>
            
            <p>L'aventure commence maintenant !</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CityPlay - Explorez le Bénin comme jamais auparavant.</p>
        </div>
    </div>
</body>
</html>
