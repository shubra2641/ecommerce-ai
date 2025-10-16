/* Page-specific handlers moved from product_detail.blade.php to comply with asset rules */
(function($){
	'use strict';

	$(document).ready(function(){
		// Example handler for cart add in product detail (originally inline in blade)
		$(document).on('click', '.cart', function(e){
			e.preventDefault();
			var quantity = $('#quantity').val() || 1;
			var pro_id = $(this).data('id');
			if(!pro_id) return;

			$.ajax({
				url: $(this).data('url') || '{{ route("add-to-cart") }}',
				type: 'POST',
				data: {
					_token: $('meta[name="csrf-token"]').attr('content'),
					quantity: quantity,
					pro_id: pro_id
				},
				success: function(response){
					try { if(typeof response !== 'object') response = $.parseJSON(response); } catch(e){}
					if(response && response.status){
						alert(response.msg || 'Added');
						location.reload();
					} else {
						alert(response.msg || 'Error');
					}
				}
			});
		});

	/* Slider range handler moved from product-lists Blade */
	if ("#slider-range".length > 0) {
		// This check used to live in the product-lists inline script; it will run when the DOM contains #slider-range
		try {
			const $slider = $("#slider-range");
			const max_value = parseInt( $slider.data('max') ) || 500;
			const min_value = parseInt($slider.data('min')) || 0;
			const currency = $slider.data('currency') || '';
			let price_range = min_value+'-'+max_value;
			if($("#price_range").length > 0 && $("#price_range").val()){
				price_range = $("#price_range").val().trim();
			}
			let price = price_range.split('-');
			$slider.slider({
				range: true,
				min: min_value,
				max: max_value,
				values: price,
				slide: function (event, ui) {
					$("#amount").val(currency + ui.values[0] + " -  "+currency+ ui.values[1]);
					$("#price_range").val(ui.values[0] + "-" + ui.values[1]);
				}
			});
			if ($("#amount").length > 0) {
				$("#amount").val(currency + $slider.slider("values", 0) + "  -  "+currency + $slider.slider("values", 1));
			}
		} catch (e) {
			// fail silently; progressive enhancement ensures functionality without JS
		}
	}

	});
})(jQuery);

