<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir ?? 'ltr' }}">
<head>
	@include('frontend.layouts.head')	
</head>
<body class="js {{ ($dir ?? 'ltr') === 'rtl' ? 'rtl' : '' }}"
	
	<!-- Preloader -->
	<div class="preloader">
		<div class="preloader-inner">
			<div class="preloader-icon">
				<span></span>
				<span></span>
			</div>
		</div>
	</div>
	<!-- End Preloader -->
	
	@include('frontend.layouts.notification')
	<!-- Header -->
	@include('frontend.layouts.header')
	<!--/ End Header -->
	@yield('main-content')
	
	@include('frontend.layouts.footer')

</body>
</html>