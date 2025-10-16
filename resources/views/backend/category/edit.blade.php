@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.edit_category') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('category.update',$category->id)}}">
        @csrf 
        @method('PATCH')

    {{-- activeLangs, defaultLang and translations provided by controller/shared data --}}

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
                    <input type="text" class="form-control" name="title" placeholder="{{ trans('app.enter_title') }}" value="{{ old('title', $category->getOriginal('title')) }}">
                @error('title')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <div class="form-group">
                <label class="col-form-label">{{ trans('app.summary') }} ({{ $lang->name }})</label>
                <div class="d-flex justify-content-end mb-2">
                  <button class="btn btn-sm btn-outline-secondary ai-generate-btn" data-field="summary" data-type="summary" data-title="{{ $category->title }}" type="button">{{ trans('app.generate_with_ai') }}</button>
                </div>
                <textarea class="form-control lang-summary" id="summary" name="summary">{{ old('summary', $category->summary) }}</textarea>
                @error('summary')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.title') }} ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="translations[{{ $lang->code }}][title]" placeholder="{{ trans('app.title') }} {{ trans('app.in') }} {{ $lang->name }}" value="{{ old('translations.'.$lang->code.'.title', $translations[$lang->code]['title'] ?? '') }}">
                  </div>
              <div class="form-group">
                <label class="col-form-label">{{ trans('app.summary') }} ({{ $lang->name }})</label>
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][summary]" data-lang="{{ $lang->code }}">{{ old('translations.'.$lang->code.'.summary', $translations[$lang->code]['summary'] ?? '') }}</textarea>
              </div>
            @endif

          </div>
        @endforeach
      </div>
    @endif

        <div class="form-group">
          <label for="is_parent">{{ trans('app.is_parent') }}</label><br>
          <input type="checkbox" name='is_parent' id='is_parent' value='{{$category->is_parent}}' {{(($category->is_parent==1)? 'checked' : '')}}> {{ trans('app.yes') }}                        
        </div>
        {{-- {{$parent_cats}} --}}
        {{-- {{$category}} --}}

      <div class="form-group {{(($category->is_parent==1) ? 'd-none' : '')}}" id='parent_cat_div'>
          <label for="parent_id">{{ trans('app.parent_category') }}</label>
          <select name="parent_id" class="form-control">
              <option value="">{{ trans('app.select_any_category') }}</option>
              @foreach($parent_cats as $key=>$parent_cat)
              
                  <option value='{{$parent_cat->id}}' {{(($parent_cat->id==$category->parent_id) ? 'selected' : '')}}>{{$parent_cat->title}}</option>
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
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$category->photo}}">
        </div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">{{ trans('app.status') }} <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
              <option value="active" {{(($category->status=='active')? 'selected' : '')}}>{{ trans('app.active') }}</option>
              <option value="inactive" {{(($category->status=='inactive')? 'selected' : '')}}>{{ trans('app.inactive') }}</option>
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
