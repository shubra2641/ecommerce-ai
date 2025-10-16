/* =====================================
Template Name: Eshop
Author Name: Naimur Rahman
Author URI: http://www.wpthemesgrid.com/
Description: Eshop - eCommerce HTML5 Template.
Version:1.0
========================================*/
/*=======================================
[Start Activation Code]
=========================================
	01. Mobile Menu JS
	02. Sticky Header JS
	03. Search JS
	04. Slider Range JS
	05. Home Slider JS
	06. Popular Slider JS
	07. Quick View Slider JS
	08. Home Slider 4 JS
	09. CountDown
	10. Flex Slider JS
	11. Cart Plus Minus Button
	12. Checkbox JS
	13. Extra Scroll JS
	14. Product page Quantity Counter
	15. Video Popup JS
	16. Scroll UP JS
	17. Nice Select JS
	18. Others JS
	19. Preloader JS
=========================================
[End Activation Code]
=========================================*/ 

window.onload = () => {
	'use strict';
  
	if ('serviceWorker' in navigator) {
	  navigator.serviceWorker
			   .register('./sw.js');
	}
  }
(function($) {
    "use strict";
     $(document).on('ready', function() {	
		
		/*====================================
			Mobile Menu
		======================================*/ 	
		$('.menu').slicknav({
			prependTo:".mobile-nav",
			duration:300,
			animateIn: 'fadeIn',
			animateOut: 'fadeOut',
			closeOnClick:true,
		});
		
		/*====================================
		03. Sticky Header JS
		======================================*/ 
		jQuery(window).on('scroll', function() {
			if ($(this).scrollTop() > 200) {
				$('.header').addClass("sticky");
			} else {
				$('.header').removeClass("sticky");
			}
		});
		
		/*=======================
		  Search JS JS
		=========================*/ 
		$('.top-search a').on( "click", function(){
			$('.search-top').toggleClass('active');
		});
		
		/*=======================
		  Slider Range JS
		=========================*/ 
		// $( function() {
		// 	$( "#slider-range" ).slider({
		// 	  range: true,
		// 	  min: 0,
		// 	  max: 1000,
		// 	  values: [ 120, 250 ],
		// 	  slide: function( event, ui ) {
		// 		$( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
		// 	  }
		// 	});
		// 	$( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
		// 	  " - $" + $( "#slider-range" ).slider( "values", 1 ) );
		// } );
		
		/*=======================
		  Home Slider JS
		=========================*/ 
		$('.home-slider').owlCarousel({
			items:1,
			autoplay:true,
			autoplayTimeout:5000,
			smartSpeed: 400,
			animateIn: 'fadeIn',
			animateOut: 'fadeOut',
			autoplayHoverPause:true,
			loop:true,
			nav:true,
			merge:true,
			dots:false,
			navText: ['<i class="ti-angle-left"></i>', '<i class="ti-angle-right"></i>'],
			responsive:{
				0: {
					items:1,
				},
				300: {
					items:1,
				},
				480: {
					items:2,
				},
				768: {
					items:3,
				},
				1170: {
					items:4,
				},
			}
		});
		
		/*=======================
		  Popular Slider JS
		=========================*/ 
		$('.popular-slider').owlCarousel({
			items:1,
			autoplay:true,
			autoplayTimeout:5000,
			smartSpeed: 400,
			animateIn: 'fadeIn',
			animateOut: 'fadeOut',
			autoplayHoverPause:true,
			loop:true,
			nav:true,
			merge:true,
			dots:false,
			navText: ['<i class="ti-angle-left"></i>', '<i class="ti-angle-right"></i>'],
			responsive:{
				0: {
					items:1,
				},
				300: {
					items:1,
				},
				480: {
					items:2,
				},
				768: {
					items:3,
				},
				1170: {
					items:4,
				},
			}
		});
		
		/*===========================
		  Quick View Slider JS
		=============================*/ 
		$('.quickview-slider-active').owlCarousel({
			items:1,
			autoplay:true,
			autoplayTimeout:5000,
			smartSpeed: 400,
			autoplayHoverPause:true,
			nav:true,
			loop:true,
			merge:true,
			dots:false,
			navText: ['<i class=" ti-arrow-left"></i>', '<i class=" ti-arrow-right"></i>'],
		});
		
		/*===========================
		  Home Slider 4 JS
		=============================*/ 
		$('.home-slider-4').owlCarousel({
			items:1,
			autoplay:true,
			autoplayTimeout:5000,
			smartSpeed: 400,
			autoplayHoverPause:true,
			nav:true,
			loop:true,
			merge:true,
			dots:false,
			navText: ['<i class=" ti-arrow-left"></i>', '<i class=" ti-arrow-right"></i>'],
		});
		
		/*====================================
		14. CountDown
		======================================*/ 
		$('[data-countdown]').each(function() {
			var $this = $(this),
				finalDate = $(this).data('countdown');
			$this.countdown(finalDate, function(event) {
				$this.html(event.strftime(
					'<div class="cdown"><span class="days"><strong>%-D</strong><p>Days.</p></span></div><div class="cdown"><span class="hour"><strong> %-H</strong><p>Hours.</p></span></div> <div class="cdown"><span class="minutes"><strong>%M</strong> <p>MINUTES.</p></span></div><div class="cdown"><span class="second"><strong> %S</strong><p>SECONDS.</p></span></div>'
				));
			});
		});
		
		/*====================================
		16. Flex Slider JS
		======================================*/
		(function($) {
			'use strict';	
				$('.flexslider-thumbnails').flexslider({
					animation: "slide",
					controlNav: "thumbnails",
					start: function(slider) {
						// Ensure first slide is visible
						slider.find('.slides > li:first-child').show();
					}
				});
				
				// Fallback: Force show first image if flexslider fails
				setTimeout(function() {
					$('.flexslider-thumbnails .slides > li:first-child').show();
					$('.flexslider-thumbnails .slides > li:first-child img').show();
					
					// Fix flexslider positioning
					$('.flexslider-thumbnails .slides').css({
						'transform': 'translate3d(0px, 0px, 0px)',
						'width': '100%'
					});
					
					$('.flexslider-thumbnails .slides > li').css({
						'width': '100%',
						'float': 'none'
					});
					
					$('.flexslider-thumbnails .slides > li:not(:first-child)').hide();
				}, 100);
		})(jQuery);
		
		/*====================================
		  Cart Plus Minus Button
		======================================*/
		var CartPlusMinus = $('.cart-plus-minus');
		CartPlusMinus.prepend('<div class="dec qtybutton">-</div>');
		CartPlusMinus.append('<div class="inc qtybutton">+</div>');
		$(".qtybutton").on("click", function() {
			var $button = $(this);
			var oldValue = $button.parent().find("input").val();
			if ($button.text() === "+") {
				var newVal = parseFloat(oldValue) + 1;
			} else {
				// Don't allow decrementing below zero
				if (oldValue > 0) {
					var newVal = parseFloat(oldValue) - 1;
				} else {
					newVal = 1;
				}
			}
			$button.parent().find("input").val(newVal);
		});
		
		/*=======================
		  Extra Scroll JS
		=========================*/
		$('.scroll').on("click", function (e) {
			var anchor = $(this);
				$('html, body').stop().animate({
					scrollTop: $(anchor.attr('href')).offset().top - 0
				}, 900);
			e.preventDefault();
		});
		
		/*===============================
		10. Checkbox JS
		=================================*/  
		$('input[type="checkbox"]').change(function(){
			if($(this).is(':checked')){
				$(this).parent("label").addClass("checked");
			} else {
				$(this).parent("label").removeClass("checked");
			}
		});
		
		/*==================================
		 12. Product page Quantity Counter
		 ===================================*/
		$('.qty-box .quantity-right-plus').on('click', function () {
			var $qty = $('.qty-box .input-number');
			var currentVal = parseInt($qty.val(), 10);
			if (!isNaN(currentVal)) {
				$qty.val(currentVal + 1);
			}
		});
		$('.qty-box .quantity-left-minus').on('click', function () {
			var $qty = $('.qty-box .input-number');
			var currentVal = parseInt($qty.val(), 10);
			if (!isNaN(currentVal) && currentVal > 1) {
				$qty.val(currentVal - 1);
			}
		});
		
		/*=====================================
		15.  Video Popup JS
		======================================*/ 
		$('.video-popup').magnificPopup({
			type: 'iframe',
			removalDelay: 300,
			mainClass: 'mfp-fade'
		});
		
		/*====================================
			Scroll Up JS
		======================================*/
		$.scrollUp({
			scrollText: '<span><i class="fa fa-angle-up"></i></span>',
			easingType: 'easeInOutExpo',
			scrollSpeed: 900,
			animation: 'fade'
		});  
		
	});
	
	/*====================================
	18. Nice Select JS
	======================================*/	
	$('select').niceSelect();
		
	/*=====================================
	 Others JS
	======================================*/ 	
	// $( function() {
	// 	$( "#slider-range" ).slider({
	// 		range: true,
	// 		min: 0,
	// 		max: 1000,
	// 		values: [ 0, 1000 ],
	// 		slide: function( event, ui ) {
	// 			$( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
	// 		}
	// 	});
	// 	$( "#amount" ).val( "$" + $( "#slider-range" ).slider( "values", 0 ) +
	// 	  " - $" + $( "#slider-range" ).slider( "values", 1 ) );
	// } );
	
	/*=====================================
	  Preloader JS
	======================================*/ 	
	//After 2s preloader is fadeOut
	$('.preloader').delay(2000).fadeOut('slow');
	setTimeout(function() {
	//After 2s, the no-scroll class of the body will be removed
	$('body').removeClass('no-scroll');
	}, 2000); //Here you can change preloader time
	 
})(jQuery);

// Global small behaviors added to comply with rules.json (no inline scripts in Blade)
setTimeout(function(){
  jQuery('.alert').slideUp();
},5000);

// Multi level dropdowns (move from footer inline script)
jQuery(function($) {
	$("ul.dropdown-menu [data-toggle='dropdown']").on("click", function(event) {
		event.preventDefault();
		event.stopPropagation();

		$(this).siblings().toggleClass("show");

		if (!$(this).next().hasClass('show')) {
			$(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
		}
		$(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
			$('.dropdown-submenu .show').removeClass("show");
		});
	});
});

// Dynamic loader for Laravel File Manager stand-alone button (if used on the page)
(function(){
	if (document.getElementById('lfm')) {
		var script = document.createElement('script');
		script.src = '/vendor/laravel-filemanager/js/stand-alone-button.js';
		script.onload = function() {
			try {
				if (jQuery && typeof jQuery('#lfm').filemanager === 'function') {
					jQuery('#lfm').filemanager('image');
				}
			} catch (e) {
				// ignore
			}
		};
		document.head.appendChild(script);
	}
})();

// Payment gateway UI: toggle transfer details & proof upload on checkout page
jQuery(function($){
	var $gatewayInfo = $('#gateway-info');
	var $transferDetails = $('#gateway-transfer-details');
	var $proofUpload = $('#gateway-proof-upload');

	function updateGatewayUI($input){
		if(!$input || !$input.length) return;
		var details = $input.data('transfer-details') || '';
		var require = $input.data('require-proof') == 1 || $input.attr('data-require-proof') === '1';
		if(details && details.length){
			$transferDetails.html('<strong>Payment instructions:</strong><div>'+details.replace(/\n/g,'<br>')+'</div>');
			$gatewayInfo.show();
		} else {
			$transferDetails.html('');
		}
		if(require){
			$proofUpload.show();
			$gatewayInfo.show();
		} else {
			$proofUpload.hide();
		}
		// hide the whole block if nothing to show
		if(!$transferDetails.html() && !$proofUpload.is(':visible')){
			$gatewayInfo.hide();
		}
	}

	// on change
	$(document).on('change', 'input[name="payment_method"]', function(){
		updateGatewayUI($(this));
	});

	// on page load, if a radio is checked
	var $checked = $('input[name="payment_method"]:checked');
	if($checked.length){ updateGatewayUI($checked); }

	/*====================================
		RTL Language Switcher Enhancement
	======================================*/
	$(document).ready(function() {
		// Smooth transition for RTL changes
		$('.language-switcher a, .dropdown-item[href*="switch-language"]').on('click', function(e) {
			// Add loading indicator
			$('body').addClass('switching-language');
			
			// Show loading message
			var $loader = $('<div class="language-loading">').text('Switching language...');
			$('body').append($loader);
		});

		// RTL-specific adjustments after page load
		if ($('body').hasClass('rtl')) {
			// Fix dropdown positions for RTL
			$('.dropdown-menu').each(function() {
				var $dropdown = $(this);
				var $toggle = $dropdown.prev('.dropdown-toggle');
				
				// Adjust dropdown position for RTL
				$dropdown.addClass('dropdown-menu-right');
			});

			// Fix main navigation for RTL
			$('.nav.main-menu').addClass('rtl-nav');
			$('.nav.main-menu li').each(function() {
				$(this).css('float', 'right');
			});

			// Fix shopping cart dropdown for RTL
			$('.shopping-item').each(function() {
				$(this).css({
					'right': 'auto',
					'left': '0',
					'text-align': 'right'
				});
			});

			// Fix search bar for RTL
			$('.search-bar select').css({
				'text-align': 'right',
				'background-position': 'left 10px center'
			});

			// Fix carousel controls for RTL
			$('.hero-slider .owl-nav .owl-prev').css({
				'right': '0',
				'left': 'auto'
			});
			
			$('.hero-slider .owl-nav .owl-next').css({
				'left': '50px',
				'right': 'auto'
			});

			// Fix mobile menu for RTL
			$('.slicknav_menu').addClass('rtl-menu');

			// Fix newsletter form for RTL
			$('.newsletter .form input').css({
				'direction': 'rtl',
				'text-align': 'right'
			});
		}

		// Language switcher dropdown enhancement
		$('#languageDropdown').on('click', function(e) {
			e.preventDefault();
			$(this).next('.dropdown-menu').toggle();
		});

		// Close dropdown when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.dropdown').length) {
				$('.dropdown-menu').hide();
			}
		});

		// Fix broken product images in cart
		$('.cart-img img, .shopping-list .cart-img img, .shopping-summery .image img').on('error', function() {
			var $img = $(this);
			if (!$img.data('error-handled')) {
				$img.data('error-handled', true);
				$img.attr('src', '/frontend/img/logo.png');
			}
		});

		// Handle empty or invalid image sources
		$('.cart-img img, .shopping-list .cart-img img, .shopping-summery .image img').each(function() {
			var src = $(this).attr('src');
			if (!src || src === '' || src.includes('first_photo') || src.includes('undefined')) {
				$(this).attr('src', '/frontend/img/logo.png');
			}
		});
		
		/*====================================
			Enhanced Product Page Features
		======================================*/ 
		// Price Range Slider Enhancement
		if ($("#slider-range").length > 0) {
			const max_value = parseInt( $("#slider-range").data('max') ) || 500;
			const min_value = parseInt($("#slider-range").data('min')) || 0;
			const currency = $("#slider-range").data('currency') || '';
			let price_range = min_value+'-'+max_value;
			if($("#price_range").length > 0 && $("#price_range").val()){
				price_range = $("#price_range").val().trim();
			}

			let price = price_range.split('-');
			$("#slider-range").slider({
				range: true,
				min: min_value,
				max: max_value,
				values: price,
				slide: function (event, ui) {
					$("#amount").val(currency + ui.values[0] + " -  "+currency+ ui.values[1]);
					$("#price_range").val(ui.values[0] + "-" + ui.values[1]);
				}
			});
		}
		
		if ($("#amount").length > 0) {
			const m_currency = $("#slider-range").data('currency') || '';
			$("#amount").val(m_currency + $("#slider-range").slider("values", 0) +
				"  -  "+m_currency + $("#slider-range").slider("values", 1));
		}
		
		// Enhanced Product Card Animations
		$('.single-product').each(function() {
			$(this).hover(
				function() {
					$(this).find('.product-img').addClass('hover-effect');
				},
				function() {
					$(this).find('.product-img').removeClass('hover-effect');
				}
			);
		});
		
		// Enhanced Pagination Interactions
		$('.pagination .pagination-list li a').on('click', function(e) {
			$(this).addClass('clicked');
			setTimeout(() => {
				$(this).removeClass('clicked');
			}, 200);
		});
	});
});
