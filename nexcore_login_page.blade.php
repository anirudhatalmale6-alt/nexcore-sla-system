<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <title>NexCore ERP | Secure Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --cyan: #06b6d4;
        --cyan-bright: #22d3ee;
        --blue: #3b82f6;
        --green: #10b981;
        --red: #ef4444;
        --text-primary: #e2e8f0;
        --text-secondary: #94a3b8;
        --text-dim: #475569;
        --font-display: 'Montserrat', sans-serif;
        --font-mono: 'JetBrains Mono', monospace;
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: var(--font-display);
        background: #060a14;
        color: var(--text-primary);
        min-height: 100dvh;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }

    .nx-page {
        min-height: 100dvh;
        background: url('{{ url("/") }}/public/images/nexcore-login-bg.jpg') center center / contain no-repeat;
        background-color: #060a14;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        position: relative;
    }

    .nx-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background: transparent;
        pointer-events: none;
    }

    .nx-card {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 320px;
        background: rgba(10,15,30,0.75);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        border: 3px solid rgba(255,255,255,0.85);
        border-radius: 16px;
        padding: 24px 22px;
        overflow: hidden;
        animation: cardIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        margin-left: 25vw;
        margin-bottom: 8vh;
    }

    .nx-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--cyan), transparent);
        opacity: 0.4;
    }

    @@keyframes cardIn {
        0% { opacity: 0; transform: translateY(24px) scale(0.97); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .nx-shield {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .nx-shield-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(6,182,212,0.12) 0%, rgba(6,182,212,0.03) 70%);
        border: 1px solid rgba(6,182,212,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        animation: shieldPulse 3s ease-in-out infinite;
    }

    .nx-shield-icon::before {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 1px solid rgba(6,182,212,0.08);
        animation: ringExpand 3s ease-in-out infinite;
    }

    .nx-shield-icon i {
        font-size: 28px;
        color: var(--cyan);
        filter: drop-shadow(0 0 10px rgba(6,182,212,0.4));
    }

    @@keyframes shieldPulse {
        0%, 100% { box-shadow: 0 0 20px rgba(6,182,212,0.08); }
        50% { box-shadow: 0 0 30px rgba(6,182,212,0.15); }
    }

    @@keyframes ringExpand {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.08); opacity: 0.2; }
    }

    .nx-header {
        text-align: center;
        margin-bottom: 18px;
    }

    .nx-header h2 {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 4px;
    }

    .nx-header p {
        font-size: 11px;
        color: var(--text-secondary);
        font-weight: 400;
    }

    .nx-alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 18px;
        display: none;
    }

    .nx-alert.is-visible { display: block; }

    .nx-alert-error {
        background: rgba(239,68,68,0.08);
        border: 1px solid rgba(239,68,68,0.2);
        color: #fca5a5;
    }

    .nx-alert-success {
        background: rgba(16,185,129,0.08);
        border: 1px solid rgba(16,185,129,0.2);
        color: #6ee7b7;
    }

    .nx-form {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .nx-field { display: flex; flex-direction: column; gap: 7px; }

    .nx-field label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text-secondary);
    }

    .nx-input-wrap {
        display: flex;
        align-items: center;
        background: rgba(15,20,36,0.7);
        border: 1px solid rgba(6,182,212,0.1);
        border-radius: 10px;
        padding: 0 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .nx-input-wrap:focus-within {
        border-color: rgba(6,182,212,0.35);
        box-shadow: 0 0 0 3px rgba(6,182,212,0.06), 0 0 20px rgba(6,182,212,0.05);
        background: rgba(20,26,46,0.8);
    }

    .nx-input-wrap .nx-icon {
        font-size: 14px;
        color: var(--text-dim);
        width: 18px;
        text-align: center;
        transition: color 0.3s;
        flex-shrink: 0;
    }

    .nx-input-wrap:focus-within .nx-icon { color: var(--cyan); }

    .nx-input-wrap input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 10px 10px;
        font-size: 13px;
        font-family: var(--font-display);
        font-weight: 500;
        color: var(--text-primary);
        outline: none;
        width: 100%;
    }

    .nx-input-wrap input::placeholder {
        color: var(--text-dim);
        font-weight: 400;
    }

    .nx-toggle-pw {
        border: none;
        background: none;
        color: var(--text-dim);
        font-size: 15px;
        cursor: pointer;
        padding: 4px 6px;
        transition: color 0.2s;
        flex-shrink: 0;
    }

    .nx-toggle-pw:hover { color: var(--cyan); }

    .nx-form-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nx-remember {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        font-size: 11px;
        color: var(--text-secondary);
    }

    .nx-remember input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--cyan);
        cursor: pointer;
    }

    .nx-forgot {
        font-size: 11px;
        font-weight: 600;
        color: var(--cyan);
        text-decoration: none;
        transition: color 0.2s;
    }

    .nx-forgot:hover { color: var(--cyan-bright); }

    .nx-submit {
        width: 100%;
        padding: 11px 20px;
        font-size: 12px;
        font-weight: 700;
        font-family: var(--font-display);
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #fff;
        background: linear-gradient(135deg, var(--cyan) 0%, var(--blue) 100%);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 4px;
    }

    .nx-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(6,182,212,0.25), 0 0 50px rgba(6,182,212,0.08);
    }

    .nx-submit:active { transform: translateY(0) scale(0.98); }
    .nx-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .nx-submit .nx-spinner {
        display: none;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spinRotate 0.7s linear infinite;
    }

    @@keyframes spinRotate { to { transform: rotate(360deg); } }

    .nx-submit.is-loading .nx-btn-label { display: none; }
    .nx-submit.is-loading .nx-spinner { display: block; }
    .nx-submit.is-loading .nx-loading-text { display: inline; }
    .nx-loading-text { display: none; }

    .nx-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid rgba(6,182,212,0.08);
        font-size: 10px;
        font-family: var(--font-mono);
        color: var(--text-dim);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .nx-security i { font-size: 10px; color: var(--green); }

    @@media (max-width: 1024px) {
        .nx-page {
            padding: 40px 30px;
        }
        .nx-page::before { background: transparent; }
        .nx-card { margin-left: 0; }
    }

    @@media (max-width: 768px) {
        .nx-page {
            padding: 24px 16px;
            align-items: flex-start;
            padding-top: 50px;
        }
        .nx-page::before { background: transparent; }
        .nx-card {
            padding: 30px 24px;
            border-radius: 16px;
            max-width: 100%;
        }
        .nx-mobile-brand { display: flex !important; }
    }

    @@media (max-width: 480px) {
        .nx-card { padding: 26px 20px; }
        .nx-header h2 { font-size: 20px; }
        .nx-input-wrap input { font-size: 14px; padding: 12px 10px; }
        .nx-submit { padding: 13px 20px; font-size: 13px; }
        .nx-shield-icon { width: 60px; height: 60px; }
        .nx-shield-icon i { font-size: 24px; }
    }

    .nx-mobile-brand {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .nx-mobile-brand .nx-m-logo {
        width: 36px;
        height: 36px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
    }

    .nx-mobile-brand .nx-m-logo .q { border-radius: 3px; }
    .nx-mobile-brand .nx-m-logo .q1 { background: #059669; }
    .nx-mobile-brand .nx-m-logo .q2 { background: #2563eb; }
    .nx-mobile-brand .nx-m-logo .q3 { background: #d97706; }
    .nx-mobile-brand .nx-m-logo .q4 { background: #7c3aed; }

    .nx-mobile-brand .nx-m-text {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 3px;
        text-transform: uppercase;
        background: linear-gradient(135deg, var(--cyan-bright), var(--blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    </style>
</head>
<body>
    <div class="nx-page">
        <div class="nx-card">
            <div class="nx-mobile-brand">
                <div class="nx-m-logo">
                    <div class="q q1"></div>
                    <div class="q q2"></div>
                    <div class="q q3"></div>
                    <div class="q q4"></div>
                </div>
                <span class="nx-m-text">NexCore</span>
            </div>

            <div class="nx-header">
                <h2>Welcome Back!</h2>
                <p>Sign in to continue to NexCore ERP</p>
            </div>

            <div class="nx-alert nx-alert-error" id="nxError"></div>
            <div class="nx-alert nx-alert-success" id="nxSuccess"></div>

            <form class="nx-form" id="nxLoginForm">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="action" value="initial">
                <input type="hidden" name="redirect_url" value="{{ url('nexcore/otp') }}">

                <div class="nx-field">
                    <label for="nxEmail">Email</label>
                    <div class="nx-input-wrap">
                        <i class="fas fa-user nx-icon"></i>
                        <input id="nxEmail" type="email" name="email" placeholder="Username" required autocomplete="email" autofocus>
                    </div>
                </div>

                <div class="nx-field">
                    <label for="nxPassword">Password</label>
                    <div class="nx-input-wrap">
                        <i class="fas fa-lock nx-icon"></i>
                        <input id="nxPassword" type="password" name="password" placeholder="Password" required autocomplete="current-password">
                        <button type="button" class="nx-toggle-pw" id="nxTogglePw"><i class="fas fa-eye-slash"></i></button>
                    </div>
                </div>

                <div class="nx-form-meta">
                    <label class="nx-remember">
                        <input type="checkbox" name="remember_me">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ url('forgotpassword') }}" class="nx-forgot">Forgot Password?</a>
                </div>

                <button type="submit" class="nx-submit" id="nxSubmit">
                    <span class="nx-btn-label">Sign In <i class="fas fa-arrow-right" style="margin-left:6px;"></i></span>
                    <span class="nx-spinner"></span>
                    <span class="nx-loading-text">Verifying...</span>
                </button>
            </form>

        </div>
    </div>

    <script>
    (function(){
        var toggle = document.getElementById('nxTogglePw');
        var pw = document.getElementById('nxPassword');
        if (toggle && pw) {
            toggle.addEventListener('click', function(){
                var hidden = pw.type === 'password';
                pw.type = hidden ? 'text' : 'password';
                toggle.innerHTML = hidden ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }

        var form = document.getElementById('nxLoginForm');
        var btn = document.getElementById('nxSubmit');
        var errBox = document.getElementById('nxError');
        var successBox = document.getElementById('nxSuccess');

        if (form && btn) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                errBox.classList.remove('is-visible');
                errBox.textContent = '';
                btn.disabled = true;
                btn.classList.add('is-loading');

                var formData = new FormData(form);

                fetch('{{ url("login") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(response){
                    if (!response.ok) {
                        return response.text().then(function(text){
                            var msg = 'Invalid login details';
                            try {
                                var data = JSON.parse(text);
                                msg = data.message || msg;
                            } catch(e) {
                                if (text) msg = text;
                            }
                            throw new Error(msg);
                        });
                    }
                    return response.json();
                })
                .then(function(data){
                    successBox.textContent = 'Login successful. Redirecting...';
                    successBox.classList.add('is-visible');
                    var redirect = data.redirect_url || '{{ url("nexcore/otp") }}';
                    window.location.href = redirect;
                })
                .catch(function(err){
                    errBox.innerHTML = '<i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>' + (err.message || 'Invalid login details');
                    errBox.classList.add('is-visible');
                    btn.disabled = false;
                    btn.classList.remove('is-loading');
                });
            });
        }
    })();
    </script>
</body>
</html>
