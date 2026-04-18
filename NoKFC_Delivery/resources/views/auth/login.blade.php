<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NO_KFC Delivery</title>
    <style>
        :root {
            --brand-red: #e4002b;
            --brand-red-dark: #bf0024;
            --brand-black: #111111;
            --brand-white: #ffffff;
            --brand-bg: #f4f4f4;
            --brand-border: #e7e7e7;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(180deg, #fbfbfb 0%, #f2f2f2 100%);
            color: var(--brand-black);
            display: grid;
            place-items: center;
            padding: 20px;
        }
        .card {
            width: min(420px, 100%);
            background: var(--brand-white);
            border: 1px solid var(--brand-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        }
        .card-top {
            height: 12px;
            background: repeating-linear-gradient(90deg, var(--brand-red), var(--brand-red) 30px, #ffffff 30px, #ffffff 60px);
        }
        .card-body {
            padding: 20px;
        }
        h1 {
            margin: 0;
            font-size: 30px;
            letter-spacing: 1px;
            font-weight: 900;
            color: var(--brand-red);
        }
        .subtitle {
            margin: 4px 0 14px;
            color: #575757;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .9px;
            font-weight: 700;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #444;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d9d9d9;
            border-radius: 9px;
            background: #fff;
            color: var(--brand-black);
            font-size: 14px;
        }
        input:focus {
            outline: 2px solid #ffd4de;
            border-color: var(--brand-red);
        }
        .field {
            margin-bottom: 12px;
        }
        .error {
            margin-top: 6px;
            color: #9f1736;
            font-size: 13px;
        }
        .status {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fff4f6;
            border: 1px solid #ffd6df;
            color: #7e2435;
            font-size: 14px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 13px;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .link {
            color: #7e2435;
            font-size: 13px;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
        .btn {
            border: 0;
            background: linear-gradient(180deg, var(--brand-red), var(--brand-red-dark));
            color: var(--brand-white);
            cursor: pointer;
            font-weight: 700;
            border-radius: 9px;
            padding: 10px 14px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="card-top"></div>
    <div class="card-body">
        <h1>NO_KFC</h1>
        <p class="subtitle">Delivery CRM Login</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <div class="actions">
                @if (Route::has('password.request'))
                    <a class="link" href="{{ route('password.request') }}">Forgot password?</a>
                @else
                    <span></span>
                @endif
                <button class="btn" type="submit">Log in</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
