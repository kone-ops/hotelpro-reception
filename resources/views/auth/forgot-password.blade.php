<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Hotel Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .forgot-container {
            max-width: 450px;
            width: 100%;
        }
        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .forgot-header h1 {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }
        .forgot-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .forgot-body {
            padding: 40px 30px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #1a4b8c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .info-box p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #1a4b8c;
            box-shadow: 0 0 0 0.2rem rgba(26, 75, 140, 0.15);
        }
        .input-group {
            position: relative;
        }
        .input-group .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }
        .input-group .form-control {
            padding-left: 45px;
        }
        .btn-reset {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(26, 75, 140, 0.3);
        }
        .back-link {
            color: #1a4b8c;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .logo-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border: 4px solid rgba(255,255,255,0.3);
        }
        .logo-icon i {
            font-size: 40px;
            color: #1a4b8c;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <div class="logo-icon">
                    <i class="bi bi-building-fill"></i>
                </div>
                <h1 class="brand-name">HOTEL PRO</h1>
                <p>Récupération de mot de passe</p>
            </div>
            
            <div class="forgot-body">
                <div class="info-box">
                    <p>
                        <i class="bi bi-info-circle me-2"></i>
                        Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                    </p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <div class="input-group">
                            <i class="bi bi-envelope input-icon"></i>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="votreemail@exemple.com">
                        </div>
                        @error('email')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-reset">
                        <i class="bi bi-send me-2"></i>Envoyer le lien de réinitialisation
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="bi bi-arrow-left me-2"></i>Retour à la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>