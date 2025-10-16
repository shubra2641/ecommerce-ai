@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Add Post Category</h5>
    <div class="card-body">
      <form method="post" action="{{route('post-category.store')}}">
        {{csrf_field()}}
        {{-- activeLangs and defaultLang provided by shared controller data --}}

        @if(!empty($activeLangs) && count($activeLangs) >= 1)
          <hr />
          <h5>Translations</h5>

          {{-- Nav tabs for languages --}}
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

                {{-- For default language, we keep the main title input name for backward-compatibility --}}
                @if($lang->code == ($defaultLang->code ?? null))
                  <div class="form-group">
                    <label class="col-form-label">Title ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter title" value="{{ old('title') }}">
                    @error('title')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">Title ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="translations[{{ $lang->code }}][title]" placeholder="Title in {{ $lang->name }}" value="{{ old('translations.'.$lang->code.'.title') }}">
                  </div>
                @endif

                <div class="form-group">
                  <label class="col-form-label">Summary ({{ $lang->name }})</label>
                  @if($lang->code == ($defaultLang->code ?? null))
                    <textarea id="summary" class="form-control lang-summary" name="summary" placeholder="Summary in {{ $lang->name }}">{{ old('summary') }}</textarea>
                  @else
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][summary]" data-lang="{{ $lang->code }}" rows="3" placeholder="Summary in {{ $lang->name }}">{{ old('translations.'.$lang->code.'.summary') }}</textarea>
                  @endif
                </div>

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
