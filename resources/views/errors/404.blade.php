<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Page not found - 404 Error">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Title -->
    <title>{{ __('Page Not Found - 404') }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Custom Styles -->
    <style>
        .error-404 {
            font-size: 8rem;
            font-weight: 700;
            color: #e74c3c;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            line-height: 1;
        }
        
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
            margin: 2rem;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        
        .btn-home {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .btn-back {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .btn-back:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .error-404 {
                font-size: 5rem;
            }
            
            .error-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .btn-back {
                margin-left: 0;
                margin-top: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <div class="error-404 mb-3">404</div>
            
            <h1 class="h2 mb-3 text-dark">
                {{ __('Page Not Found') }}
            </h1>
            
            <p class="lead text-muted mb-4">
                {{ __('Sorry, the page you are looking for could not be found.') }}
            </p>
            
            <p class="text-muted mb-4">
                {{ __('The page might have been moved, deleted, or you might have entered the wrong URL.') }}
            </p>
            
            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
                <a href="{{ url('/') }}" class="btn-home">
                    <i class="fas fa-home"></i>
                    {{ __('Back to Home') }}
                </a>
                
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Go Back') }}
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    {{ __('Error Code') }}: 404 | 
                    {{ __('Timestamp') }}: {{ now()->format('Y-m-d H:i:s') }}
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Custom Script -->
    <script>
        // Auto redirect after 30 seconds (optional)
        // setTimeout(function() {
        //     window.location.href = '{{ url("/") }}';
        // }, 30000);
        
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const errorCard = document.querySelector('.error-card');
            errorCard.style.opacity = '0';
            errorCard.style.transform = 'translateY(50px)';
            
            setTimeout(function() {
                errorCard.style.transition = 'all 0.6s ease';
                errorCard.style.opacity = '1';
                errorCard.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>

</html>
