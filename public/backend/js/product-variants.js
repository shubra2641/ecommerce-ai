/* Product variants manager
   - Adds/removes variant rows
   - Keeps inputs named as variants[][field]
   - Works with jQuery (already loaded in admin layout)
*/
(function($){
  'use strict';

  // rowIndex counter used to generate stable indexes for new variant rows
  var _rowCounter = Date.now();

  function nextRowIndex(){
    return 'new_' + (_rowCounter++);
  }

  // Build a variant row using an explicit index so PHP receives variants[index][field]
  function variantRow(data, index){
    data = data || {};
    // if an explicit index isn't provided, prefer using existing id or generate a new one
    if (typeof index === 'undefined' || index === null) {
      index = data.id ? data.id : nextRowIndex();
    }

    var idInput = data.id ? '<input type="hidden" name="variants['+index+'][id]" value="'+data.id+'">' : '';
    var size = data.size || '';
    var color = data.color || '';
    var price = data.price || '';
    var stock = data.stock || '';
    var sku = data.sku || '';

    var html = '<tr class="variant-row" data-variant-index="'+index+'">'+
      '<td>'+ idInput + '<input type="text" name="variants['+index+'][size]" class="form-control" value="'+size+'" placeholder="Size"></td>'+
      '<td><input type="text" name="variants['+index+'][color]" class="form-control" value="'+color+'" placeholder="Color"></td>'+
      '<td><input type="number" step="0.01" name="variants['+index+'][price]" class="form-control" value="'+price+'" placeholder="Price"></td>'+
      '<td><input type="number" name="variants['+index+'][stock]" class="form-control" value="'+stock+'" placeholder="Stock"></td>'+
      '<td><input type="text" name="variants['+index+'][sku]" class="form-control" value="'+sku+'" placeholder="SKU"></td>'+
      '<td><button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button></td>'+
    '</tr>';
    return html;
  }

  $(document).on('click', '#add-variant', function(e){
    e.preventDefault();
    $('#variants-table tbody').append(variantRow());
  });

  $(document).on('click', '.remove-variant', function(e){
    e.preventDefault();
    var $tr = $(this).closest('tr');
    $tr.remove();
  });

  // initialize if existing variants data is provided in a JSON script tag with id #initial-variants
  $(function(){
    var raw = $('#initial-variants').text();
    if(raw && raw.trim() !== ''){
      try{
        var variants = JSON.parse(raw);
        if(Array.isArray(variants)){
          $('#variants-table tbody').empty();
          variants.forEach(function(v){
            // use existing id as the index when available so rows keep stable indexes
            var idx = v.id ? v.id : null;
            $('#variants-table tbody').append(variantRow(v, idx));
          });
        }
      }catch(e){
        // ignore json errors
        console.warn('Could not parse initial variants', e);
      }
    }
  });

})(jQuery);
