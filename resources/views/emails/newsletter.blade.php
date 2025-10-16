<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #333;
            margin-top: 0;
        }
        .content p {
            margin-bottom: 15px;
        }
        .footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .unsubscribe-link {
            color: #dc3545;
            text-decoration: none;
        }
        .unsubscribe-link:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="content">
            <h2>{{ $subject }}</h2>
            
            @if($subscriber->name)
                <p>{{ __('newsletter.dear') }} {{ $subscriber->name }},</p>
            @else
                <p>{{ __('newsletter.dear_subscriber') }},</p>
            @endif

            <div>
                {!! nl2br(e($content)) !!}
            </div>

            <p>{{ __('newsletter.thank_you') }}</p>
        </div>

        <div class="footer">
            <p>{{ __('newsletter.email_footer_text') }}</p>
            <p>
                <a href="{{ $unsubscribeUrl }}" class="unsubscribe-link">
                    {{ __('newsletter.unsubscribe') }}
                </a>
            </p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('newsletter.all_rights_reserved') }}</p>
        </div>
    </div>
</body>
</html>
