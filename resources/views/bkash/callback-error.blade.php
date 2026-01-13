<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Binding Failed</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }
        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out;
        }
        .error-icon svg {
            width: 48px;
            height: 48px;
            color: white;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        h1 {
            color: #1f2937;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        p {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 32px;
            line-height: 1.5;
        }
        .redirect-text {
            color: #667eea;
            font-size: 14px;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h1>Wallet Binding Cancelled</h1>
        <p>{{ $message ?? 'The wallet binding process was cancelled or failed. Please try again.' }}</p>
        <div>
            <span class="redirect-text">Closing window...</span>
        </div>
    </div>

    <script>
        // Close popup and notify parent
        if (window.opener) {
            // Notify parent window that binding was cancelled
            window.opener.postMessage({
                type: 'wallet-bound-cancelled',
                message: '{{ $message ?? "Agreement cancelled" }}'
            }, '*');
            
            // Close popup after a short delay
            setTimeout(() => {
                window.close();
            }, 2000);
        } else {
            // If not in popup, redirect directly
            setTimeout(() => {
                window.location.href = '{{ $redirectUrl ?? "/wallet" }}';
            }, 2000);
        }
    </script>
</body>
</html>
