<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Hotel Pro</title>
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #1a4b8c 0%, #2563a8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 30px;
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(26, 75, 140, 0.3);
        }

        .form-check-input:checked {
            background-color: #1a4b8c;
            border-color: #1a4b8c;
        }

        .forgot-password {
            color: #1a4b8c;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .logo-icon i {
            font-size: 40px;
            color: #1a4b8c;
        }

        .brand-name {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .brand-tagline {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 400;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-icon">
                    <img src="{{ asset('Template/logo.jpg') }}" alt="HotelPro Logo" class="logo-img"
                        style="max-width: 80px; width: 90px; height: 80px; border-radius: 30%; object-fit: cover; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.2); transition: all 0.3s ease;" />

                </div>
                <h1 class="brand-name">HOTEL PRO</h1>
                <p class="brand-tagline">Système de Gestion Hôtelière Professionnel</p>
            </div>

            <div class="login-body">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <div class="input-group">
                            <i class="bi bi-envelope input-icon"></i>
                            <input id="email" type="email"
                                class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="votre email@exemple.com">
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
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="current-password" placeholder="Entrez votre mot de passe">
                        </div>
                        @error('password')
                            <div class="text-danger mt-2 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                            <label class="form-check-label" for="remember_me">
                                Se souvenir de moi
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="forgot-password" href="{{ route('password.request') }}">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
