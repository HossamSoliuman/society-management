<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Society Management SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 600px;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(232,75,30,0.2) 0%, transparent 70%);
        }
        .login-brand {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .login-brand-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #E84B1E, #F97316);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .login-brand-icon svg { width: 40px; height: 40px; }
        .login-brand h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .login-brand p { font-size: 14px; opacity: 0.8; }
        .login-features {
            position: relative;
            z-index: 1;
            margin-top: 40px;
            list-style: none;
        }
        .login-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            opacity: 0.9;
        }
        .login-features li i { color: #FDBA74; }
        .login-right {
            flex: 1;
            padding: 60px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-header { margin-bottom: 32px; }
        .login-header h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .login-header p { font-size: 13px; color: #64748b; }
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            color: #1e293b;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: #E84B1E;
            box-shadow: 0 0 0 3px rgba(232,75,30,0.1);
        }
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }
        .input-wrapper .form-control { padding-left: 40px; }
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 12px;
        }
        .form-check { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .form-check input { accent-color: #E84B1E; }
        .form-check label { color: #475569; cursor: pointer; }
        .forgot-link { color: #E84B1E; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { text-decoration: underline; }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #E84B1E;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-login:hover { background: #C63D15; }
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
        @media (max-width: 768px) {
            .login-wrapper { flex-direction: column; }
            .login-left { display: none; }
            .login-right { padding: 40px 24px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-left">
            <div class="login-brand">
                <div class="login-brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 40px; height: 40px; color: #fff;">
                        <path d="M3 21h18M5 21V7l8-4 8 4v14M9 21v-6h6v6"/>
                        <path d="M9 9h1v1H9zM14 9h1v1h-1z"/>
                    </svg>
                </div>
                <h1>Society Management</h1>
                <p>SaaS Platform</p>
            </div>
            <ul class="login-features">
                <li><i class="fas fa-check-circle"></i> Manage multiple societies</li>
                <li><i class="fas fa-check-circle"></i> Subscription & billing management</li>
                <li><i class="fas fa-check-circle"></i> Real-time analytics & reports</li>
                <li><i class="fas fa-check-circle"></i> Secure role-based access</li>
            </ul>
        </div>
        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your super admin account</p>
            </div>

            @if($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>
                <div class="form-options">
                    <label class="form-check">
                        <input type="checkbox" name="remember">
                        <label>Remember me</label>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            <div class="login-footer">
                <p>Default: superadmin@society.com / password</p>
            </div>
        </div>
    </div>
</body>
</html>
