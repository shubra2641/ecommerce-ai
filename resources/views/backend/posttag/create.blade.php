@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.add_tag') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('post-tag.store')}}">
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
                    <label for="inputTitle" class="col-form-label">{{ trans('app.tag_title') }} ({{ $lang->name }})</label>
                    <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{old('title')}}" class="form-control">
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">{{ trans('app.tag_title') }} ({{ $lang->name }})</label>
                    <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control" value="{{ old('translations.'.$lang->code.'.title') }}" placeholder="Title in {{ $lang->name }}">
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif

        <div class="form-group">
          <label for="status" class="col-form-label">Status</label>
          <select name="status" class="form-control">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
          </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">{{ trans('app.Submit') }}
           <button class="btn btn-success" type="submit">{{ trans('app.Submit') }}
        </div>
      </form>
    </div>
</div>

@endsection
