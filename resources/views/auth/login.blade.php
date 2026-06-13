<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | TaskFlow</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .auth-logo .icon-wrap {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #2563EB, #1a1a2e);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .75rem;
        }
        .auth-logo h1 { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin: 0; }
        .auth-logo p  { color: #6B7280; font-size: .9rem; margin: 0; }
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #D1D5DB;
            padding: .65rem 1rem;
            font-size: .95rem;
        }
        .form-control:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563EB, #1a1a2e);
            border: none;
            border-radius: 8px;
            padding: .7rem;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover { opacity: .9; }
        .input-group-text { border-radius: 8px 0 0 8px; background: #F3F4F6; border-color: #D1D5DB; }
        label { font-weight: 600; font-size: .875rem; color: #374151; }
        .auth-footer { text-align: center; margin-top: 1.25rem; font-size: .9rem; color: #6B7280; }
        .auth-footer a { color: #2563EB; font-weight: 600; }
        .invalid-feedback { display: block; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <div class="icon-wrap">
            <i class="fas fa-tasks text-white" style="font-size:1.5rem;"></i>
        </div>
        <h1>TaskFlow</h1>
        <p>Sign in to manage your tasks</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                </div>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       autofocus>
            </div>
            @error('email')
                <span class="invalid-feedback text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                </div>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••">
            </div>
            @error('password')
                <span class="invalid-feedback text-danger small">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group d-flex align-items-center justify-content-between">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                <label class="custom-control-label font-weight-normal" for="remember">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt mr-2"></i> Sign In
        </button>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">Create one</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>