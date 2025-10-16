@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.add_category') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('category.store')}}">
        {{csrf_field()}}


        {{-- Translation inputs per active language --}}
        {{-- activeLangs and defaultLang are shared via controller --}}

        @if(!empty($activeLangs) && $activeLangs->count() >= 1)
          <hr />
          <h5>{{ trans('app.translations') }}</h5>

          <ul class="nav nav-tabs" id="langTabs" role="tablist">
            @foreach($activeLangs as $i => $lang)
              <li class="nav-item">
                <a class="nav-link {{ $i==0 ? 'active' : '' }}" id="tab-{{ $lang->code }}" data-toggle="tab" href="#pane-{{ $lang->code }}" role="tab" aria-controls="pane-{{ $lang->code }}" aria-selected="{{ $i==0 ? 'true' : 'false' }}">{{ $lang->name }}</a>
              </li>
            @endforeach
          </ul>

          <div class="tab-content mt-3" id="langTabsContent">
            @foreach($activeLangs as $i => $lang)
              <div class="tab-pane fade {{ $i==0 ? 'show active' : '' }}" id="pane-{{ $lang->code }}" role="tabpanel" aria-labelledby="tab-{{ $lang->code }}">

                @if($lang->code == ($defaultLang->code ?? null))
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.title') }} ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="title" placeholder="{{ trans('app.enter_title') }}" value="{{ old('title') }}">
                    @error('title')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.summary') }} ({{ $lang->name }})</label>
                    <textarea id="summary" class="form-control lang-summary" name="summary" placeholder="{{ trans('app.summary') }} {{ trans('app.in') }} {{ $lang->name }}">{{ old('summary') }}</textarea>
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.title') }} ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="translations[{{ $lang->code }}][title]" placeholder="{{ trans('app.title') }} {{ trans('app.in') }} {{ $lang->name }}" value="{{ old('translations.'.$lang->code.'.title') }}">
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.summary') }} ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][summary]" data-lang="{{ $lang->code }}" placeholder="{{ trans('app.summary') }} {{ trans('app.in') }} {{ $lang->name }}">{{ old('translations.'.$lang->code.'.summary') }}</textarea>
                  </div>
                @endif

              </div>
            @endforeach
          </div>
        @endif

        <div class="form-group">
          <label for="is_parent">{{ trans('app.is_parent') }}</label><br>
          <input type="checkbox" name='is_parent' id='is_parent' value='1' checked> {{ trans('app.yes') }}                        
        </div>
        {{-- {{$parent_cats}} --}}

        <div class="form-group d-none" id='parent_cat_div'>
          <label for="parent_id">{{ trans('app.parent_category') }}</label>
          <select name="parent_id" class="form-control">
              <option value="">{{ trans('app.select_any_category') }}</option>
              @foreach($parent_cats as $key=>$parent_cat)
                  <option value='{{$parent_cat->id}}'>{{$parent_cat->title}}</option>
              @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">{{ trans('app.photo') }}</label>
          <div class="input-group">
              <span class="input-group-btn">
                  <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                  <i class="fa fa-picture-o"></i> {{ trans('app.choose') }}
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
              <option value="active">{{ trans('app.active') }}</option>
              <option value="inactive">{{ trans('app.inactive') }}</option>
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
