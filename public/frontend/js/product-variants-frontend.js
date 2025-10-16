(function($){
  'use strict';

  // When user changes variant selection, pick matching variant and set hidden input
  function findVariant(variants, size, color){
    for(var i=0;i<variants.length;i++){
      var v = variants[i];
      if((!size || v.size==size) && (!color || v.color==color)) return v;
    }
    return null;
  }

  $(function(){
    var raw = $('#product-variants-json').text();
    var variants = [];
    try{ variants = JSON.parse(raw || '[]'); }catch(e){ variants = []; }

    $(document).on('change', '#variant-size, #variant-color', function(){
      var size = $('#variant-size').val();
      var color = $('#variant-color').val();
      var matched = findVariant(variants, size, color);
      if(matched){
        $('#selected-variant-id').val(matched.id);
        // update price display
        if(typeof matched.price !== 'undefined' && matched.price !== null && matched.price !== ''){
          var price = parseFloat(matched.price);
          if(!isNaN(price)){
            $('.price .discount').text('$' + price.toFixed(2));
          }
        }
        // update stock display
        $('.availability').find('span.badge').text(matched.stock);
      } else {
        $('#selected-variant-id').val('');
      }
    });
  });

})(jQuery);