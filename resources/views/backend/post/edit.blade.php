@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.edit_post') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('post.update',$post->id)}}">
        @csrf 
        @method('PATCH')
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">{{ trans('app.post_title') }} <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{ old('title', $post->getOriginal('title')) }}" class="form-control">
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

  {{-- activeLangs, defaultLang and translations are provided by controller --}}
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
                    <label for="quote" class="col-form-label">Quote</label>
                    <textarea class="form-control lang-summary" id="quote" name="quote">{{ old('quote', $post->quote) }}</textarea>
                    @error('quote')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="summary" class="col-form-label">Summary <span class="text-danger">*</span></label>
                    <div class="d-flex justify-content-end mb-2">
                      <button class="btn btn-sm btn-outline-secondary ai-generate-btn" data-field="summary" data-type="summary" data-title="{{ $post->title }}" type="button">{{ trans('app.generate_with_ai') }}</button>
                    </div>
                    <textarea class="form-control lang-summary" id="summary" name="summary">{{ old('summary', $post->summary) }}</textarea>
                    @error('summary')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="description" class="col-form-label">Description</label>
                    <div class="d-flex justify-content-end mb-2">
                      <button class="btn btn-sm btn-outline-secondary ai-generate-btn" data-field="description" data-type="description" data-title="{{ $post->title }}" type="button">{{ trans('app.generate_with_ai') }}</button>
                    </div>
                    <textarea class="form-control lang-summary" id="description" name="description">{{ old('description', $post->description) }}</textarea>
                    @error('description')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.post_title') }} ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="translations[{{ $lang->code }}][title]" value="{{ old('translations.'.$lang->code.'.title', $translations[$lang->code]['title'] ?? ($post->translations[$lang->code]['title'] ?? '') ) }}">
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Quote ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][quote]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.quote', $translations[$lang->code]['quote'] ?? '') }}</textarea>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Summary ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][summary]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.summary', $translations[$lang->code]['summary'] ?? '') }}</textarea>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Description ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][description]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.description', $translations[$lang->code]['description'] ?? '') }}</textarea>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif

        <div class="form-group">
          <label for="post_cat_id">Category <span class="text-danger">*</span></label>
          <select name="post_cat_id" class="form-control">
              <option value="">--Select any category--</option>
              @foreach($categories as $key=>$data)
                  <option value='{{$data->id}}' {{(($data->id==$post->post_cat_id)? 'selected' : '')}}>{{$data->title}}</option>
              @endforeach
          </select>
        </div>
        {{-- {{$post->tags}} --}}
        <div class="form-group">
          <label for="tags">Tag</label>
          <select name="tags[]" multiple  data-live-search="true" class="form-control selectpicker">
              <option value="">--Select any tag--</option>
              @foreach($tags as $key=>$data)
              
              <option value="{{$data->title}}"  {{(( in_array( "$data->title",$post_tags ) ) ? 'selected' : '')}}>{{$data->title}}</option>
              @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="added_by">Author</label>
          <select name="added_by" class="form-control">
              <option value="">--Select any one--</option>
              @foreach($users as $key=>$data)
                <option value='{{$data->id}}' {{(($post->added_by==$data->id)? 'selected' : '')}}>{{$data->name}}</option>
              @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">Photo <span class="text-danger">*</span></label>
          <div class="input-group">
              <span class="input-group-btn">
                  <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                  <i class="fa fa-picture-o"></i> Choose
                  </a>
              </span>
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$post->photo}}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>

          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
            <option value="active" {{(($post->status=='active')? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($post->status=='inactive')? 'selected' : '')}}>Inactive</option>
        </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">{{ trans('app.update') }}</button>
        </div>
      </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
    $('#lfm').filemanager('image');

    $(document).ready(function() {
    $('#summary').summernote({
      placeholder: "Write short description.....",
        tabsize: 2,
        height: 150
    });
    });

    $(document).ready(function() {
      $('#quote').summernote({
        placeholder: "Write short Quote.....",
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
</script>
@endpush