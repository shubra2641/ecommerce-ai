@extends('backend.layouts.master')
@section('title', trans('app.edit_banner'))
@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.edit_banner') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('banner.update',$banner->id)}}">
        @csrf 
        @method('PATCH')
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
              {{-- translations for tab inputs are provided by the controller as $translations --}}
              <div class="tab-pane fade {{ $i==0 ? 'show active' : '' }}" id="pane-{{ $lang->code }}">
                @if($lang->code == ($defaultLang->code ?? null))
                  <div class="form-group">
                    <label for="inputTitle" class="col-form-label">{{ trans('app.banner_title') }} ({{ $lang->name }}) <span class="text-danger">*</span></label>
                    <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{ old('title', $banner->title) }}" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="inputDesc" class="col-form-label">Description</label>
                    <textarea class="form-control lang-summary" id="description" name="description">{{ old('description', $banner->description) }}</textarea>
                    @error('description')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.banner_title') }} ({{ $lang->name }})</label>
                    <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control" value="{{ old('translations.'.$lang->code.'.title', $t['title'] ?? '') }}" placeholder="Title in {{ $lang->name }}">
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Description ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][description]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.description', $t['description'] ?? '') }}</textarea>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif

        <div class="form-group">
        <label for="inputPhoto" class="col-form-label">Photo <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-btn">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                <i class="fa fa-picture-o"></i> Choose
                </a>
            </span>
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$banner->photo}}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
            <option value="active" {{(($banner->status=='active') ? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($banner->status=='inactive') ? 'selected' : '')}}>Inactive</option>
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