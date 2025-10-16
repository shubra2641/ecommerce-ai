@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.edit_tag') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('post-tag.update',$postTag->id)}}">
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
                    <label for="inputTitle" class="col-form-label">{{ trans('app.tag_title') }} ({{ $lang->name }})</label>
                    <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{ old('title', $postTag->getOriginal('title')) }}" class="form-control">
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.tag_title') }} ({{ $lang->name }})</label>
                    <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control" value="{{ old('translations.'.$lang->code.'.title', $translations[$lang->code]['title'] ?? '') }}" placeholder="Title in {{ $lang->name }}">
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif

        <div class="form-group">
          <label for="status" class="col-form-label">Status</label>
          <select name="status" class="form-control">
            <option value="active" {{(($postTag->status=='active') ? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($postTag->status=='inactive') ? 'selected' : '')}}>Inactive</option>
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
