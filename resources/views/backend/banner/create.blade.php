@extends('backend.layouts.master')

@section('title', trans('app.add_banner'))

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.add_banner') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('banner.store')}}">
        {{csrf_field()}}
  {{-- activeLangs and defaultLang are provided by controller --}}
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
                    <label for="inputTitle" class="col-form-label">{{ trans('app.banner_title') }} ({{ $lang->name }}) <span class="text-danger">*</span></label>
                    <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{old('title')}}" class="form-control">
                  </div>
                  <div class="form-group">
                    <label for="inputDesc" class="col-form-label">Description</label>
                    <textarea class="form-control lang-summary" id="description" name="description">{{old('description')}}</textarea>
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.banner_title') }} ({{ $lang->name }})</label>
                    <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control" value="{{ old('translations.'.$lang->code.'.title') }}" placeholder="Title in {{ $lang->name }}">
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Description ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][description]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.description') }}</textarea>
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
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{old('photo')}}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">{{ trans('app.Reset') }} </button>
           <button class="btn btn-success" type="submit">{{ trans('app.Submit') }} </button>
        </div>
      </form>
    </div>
</div>

@endsection