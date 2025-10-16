@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.add_product') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('product.store')}}">
        {{csrf_field()}}
  {{-- activeLangs and defaultLang are provided by controller --}}
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">{{ trans('app.product_title') }} <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{old('title')}}" class="form-control">
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        @if(!empty($activeLangs) && $activeLangs->count())
          <ul class="nav nav-tabs" id="langTabs" role="tablist">
            @foreach($activeLangs as $i => $lang)
              <li class="nav-item">
                <a class="nav-link {{ $i==0 ? 'active' : '' }}" id="tab-{{ $lang->code }}" data-toggle="tab" href="#pane-{{ $lang->code }}" role="tab">{{ $lang->name }}</a>
              </li>
            @endforeach
          </ul>
          <div class="tab-content mt-3">
            @foreach($activeLangs as $i => $lang)
              <div class="tab-pane fade {{ $i==0 ? 'show active' : '' }}" id="pane-{{ $lang->code }}">
                @if($lang->code == ($defaultLang->code ?? null))
                  <div class="form-group">
                    <label for="summary" class="col-form-label">{{ trans('app.summary') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control lang-summary" id="summary" name="summary">{{old('summary')}}</textarea>
                    @error('summary')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="description" class="col-form-label">{{ trans('app.description') }}</label>
                    <textarea class="form-control lang-summary" id="description" name="description">{{old('description')}}</textarea>
                    @error('description')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.product_title') }} ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="translations[{{ $lang->code }}][title]" value="{{ old('translations.'.$lang->code.'.title') }}">
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.summary') }} ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][summary]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.summary') }}</textarea>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.description') }} ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][description]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.description') }}</textarea>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif


        <div class="form-group">
          <label for="is_featured">{{ trans('app.is_featured') }}</label><br>
          <input type="checkbox" name='is_featured' id='is_featured' value='1' checked> {{ trans('app.yes') }}                        
        </div>
              {{-- {{$categories}} --}}

        <div class="form-group">
          <label for="cat_id">{{ trans('app.category') }} <span class="text-danger">*</span></label>
          <select name="cat_id" id="cat_id" class="form-control">
              <option value="">--Select any category--</option>
              @foreach($categories as $key=>$cat_data)
                  <option value='{{$cat_data->id}}'>{{$cat_data->title}}</option>
              @endforeach
          </select>
        </div>

        <div class="form-group d-none" id="child_cat_div">
          <label for="child_cat_id">{{ trans('app.child_category') }}</label>
          <select name="child_cat_id" id="child_cat_id" class="form-control">
              <option value="">--Select any category--</option>
              {{-- @foreach($parent_cats as $key=>$parent_cat)
                  <option value='{{$parent_cat->id}}'>{{$parent_cat->title}}</option>
              @endforeach --}}
          </select>
        </div>

        <div class="form-group">
          <label for="price" class="col-form-label">{{ trans('app.price') }}(NRS) <span class="text-danger">*</span></label>
          <input id="price" type="number" name="price" placeholder="Enter price"  value="{{old('price')}}" class="form-control">
          @error('price')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="discount" class="col-form-label">{{ trans('app.discount') }}(%)</label>
          <input id="discount" type="number" name="discount" min="0" max="100" placeholder="Enter discount"  value="{{old('discount')}}" class="form-control">
          @error('discount')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group">
          <label>{{ trans('app.variants') ?? 'Variants' }}</label>
          <p class="small text-muted">Add product variants (size, color) with specific price and stock. Leave empty to use base product price/stock.</p>
          <table class="table table-bordered" id="variants-table">
            <thead>
              <tr>
                <th>Size</th>
                <th>Color</th>
                <th>Price</th>
                <th>Stock</th>
                <th>SKU</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <!-- rows injected by JS -->
            </tbody>
          </table>
          <button type="button" id="add-variant" class="btn btn-sm btn-primary">Add Variant</button>

          {{-- placeholder for initial variants (JSON) - empty on create --}}
          <script type="application/json" id="initial-variants">[]</script>
        </div>

        <div class="form-group">
          <label for="brand_id">{{ trans('app.brand') }}</label>
          {{-- {{$brands}} --}}

          <select name="brand_id" class="form-control">
              <option value="">--Select Brand--</option>
             @foreach($brands as $brand)
              <option value="{{$brand->id}}">{{$brand->title}}</option>
             @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="condition">{{ trans('app.condition') }}</label>
          <select name="condition" class="form-control">
              <option value="">--Select Condition--</option>
              <option value="default">Default</option>
              <option value="new">New</option>
              <option value="hot">Hot</option>
          </select>
        </div>

        <div class="form-group">
          <label for="stock">Quantity <span class="text-danger">*</span></label>
          <input id="quantity" type="number" name="stock" min="0" placeholder="Enter quantity"  value="{{old('stock')}}" class="form-control">
          @error('stock')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">Photo <span class="text-danger">*</span></label>
          <div class="input-group">
              <span class="input-group-btn">
                  <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                  <i class="fa fa-picture-o"></i> Choose
                  </a>
              </span>
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{old('photo')}}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">{{ trans('app.status') }} <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">{{ trans('app.reset') }}</button>
           <button class="btn btn-success" type="submit">{{ trans('app.submit') }}</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
    $('#lfm').filemanager('image');

    $(document).ready(function() {
      $('#summary').summernote({
        placeholder: "Write short description.....",
          tabsize: 2,
          height: 100
      });
    });

    $(document).ready(function() {
      $('#description').summernote({
        placeholder: "Write detail description.....",
          tabsize: 2,
          height: 150
      });
    });
    // $('select').selectpicker();

</script>

<script>
  $('#cat_id').change(function(){
    var cat_id=$(this).val();
    // alert(cat_id);
    if(cat_id !=null){
      // Ajax call
      $.ajax({
        url:"/admin/category/"+cat_id+"/child",
        data:{
          _token:"{{csrf_token()}}",
          id:cat_id
        },
        type:"POST",
        success:function(response){
          if(typeof(response) !='object'){
            response=$.parseJSON(response)
          }
          // console.log(response);
          var html_option="<option value=''>----Select sub category----</option>"
          if(response.status){
            var data=response.data;
            // alert(data);
            if(response.data){
              $('#child_cat_div').removeClass('d-none');
              $.each(data,function(id,title){
                html_option +="<option value='"+id+"'>"+title+"</option>"
              });
            }
            else{
            }
          }
          else{
            $('#child_cat_div').addClass('d-none');
          }
          $('#child_cat_id').html(html_option);
        }
      });
    }
    else{
    }
  })
</script>
@endpush