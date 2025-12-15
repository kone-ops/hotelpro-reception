<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Hotel Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h1 {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }
        .register-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .register-body {
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
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
        .btn-register {
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
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(26, 75, 140, 0.3);
        }
        .login-link {
            color: #1a4b8c;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .login-link:hover {
            text-decoration: underline;
        }
        .logo-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .logo-icon i {
            font-size: 24px;
            color: #1a4b8c;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h1>Créer un compte</h1>
                <p>Rejoignez Hotel Pro</p>
            </div>
            
            <div class="register-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom complet</label>
                        <div class="input-group">
                            <i class="bi bi-person input-icon"></i>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                   placeholder="Votre nom complet">
                        </div>
                        @error('name')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <div class="input-group">
                            <i class="bi bi-envelope input-icon"></i>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autocomplete="username"
                                   placeholder="votreemail@exemple.com">
                        </div>
                        @error('email')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <i class="bi bi-lock input-icon"></i>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="new-password"
                                   placeholder="Minimum 8 caractères">
                        </div>
                        @error('password')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input id="password_confirmation" type="password" class="form-control" 
                                   name="password_confirmation" required autocomplete="new-password"
                                   placeholder="Confirmez votre mot de passe">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register">
                        <i class="bi bi-person-plus me-2"></i>S'inscrire
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Déjà inscrit ? <a href="{{ route('login') }}" class="login-link">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>