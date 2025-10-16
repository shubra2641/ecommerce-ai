/**
 * Checkout Page JavaScript
 * Handles payment gateway selection, shipping options, and form interactions
 */

$(document).ready(function() {
    // Initialize select2 and nice-select
    $("select.select2").select2();
    $('select.nice-select').niceSelect();

    // Gateway card selection UX: highlight selected card and show gateway-info
    $(document).on('change', 'input[name="payment_method"]', function(){
        $('label.gateway-card').removeClass('active');
        var $label = $(this).closest('label.gateway-card');
        if($label.length) $label.addClass('active');

        // Get transfer details and require-proof from selected radio (support radios inside labels)
        var requireProof = $(this).data('require-proof') || $(this).attr('data-require-proof') || 0;
        var details = $(this).data('transfer-details') || $(this).attr('data-transfer-details') || '';
        if(details && details.length){
            $('#gateway-transfer-details').html(details);
            $('#gateway-info').removeClass('d-none');
        } else {
            $('#gateway-transfer-details').html('');
        }
        if(parseInt(requireProof)){
            $('#gateway-proof-upload').removeClass('d-none');
            $('#gateway-info').removeClass('d-none');
        } else {
            $('#gateway-proof-upload').addClass('d-none');
            // if no details and no proof, hide the wrapper
            if(!details || !details.length) $('#gateway-info').addClass('d-none');
        }
    });

    // Shipping cost calculation
    $('.shipping select[name=shipping]').change(function(){
        let cost = parseFloat( $(this).find('option:selected').data('price') ) || 0;
        let subtotal = parseFloat( $('.order_subtotal').data('price') ); 
        let coupon = parseFloat( $('.coupon_price').data('price') ) || 0; 
        $('#order_total_price span').text('$'+(subtotal + cost-coupon).toFixed(2));
    });
});

/**
 * Toggle shipping address visibility
 * @param {string} box - The element ID to toggle
 */
function showMe(box){
    var checkbox = document.getElementById('shipping').style.display;
    var vis = 'none';
    if(checkbox == "none"){
        vis = 'block';
    }
    if(checkbox == "block"){
        vis = "none";
    }
    document.getElementById(box).style.display = vis;
}
