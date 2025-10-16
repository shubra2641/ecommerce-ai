@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Edit Post Category</h5>
    <div class="card-body">
      <form method="post" action="{{route('post-category.update',$postCategory->id)}}">
        @csrf 
        @method('PATCH')
        {{-- activeLangs, defaultLang and translations provided by controller or shared view data --}}

        @if(!empty($activeLangs) && count($activeLangs) >= 1)
          <hr />
          <h5>Translations</h5>

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
                    <label class="col-form-label">Title ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter title" value="{{ old('title', $postCategory->getOriginal('title')) }}">
                    @error('title')
                      <span class="text-danger">{{ $message }}</span>
                    @enderror
                  </div>
                @else
                  <div class="form-group">
                    <label class="col-form-label">Title ({{ $lang->name }})</label>
                    <input type="text" class="form-control" name="translations[{{ $lang->code }}][title]" placeholder="Title in {{ $lang->name }}" value="{{ old('translations.'.$lang->code.'.title', $translations[$lang->code]['title'] ?? ($postCategory->translations[$lang->code]['title'] ?? '') ) }}">
                  </div>
                @endif

                <div class="form-group">
                  <label class="col-form-label">Summary ({{ $lang->name }})</label>
                  @if($lang->code == ($defaultLang->code ?? null))
                    <textarea id="summary" class="form-control lang-summary" name="summary">{{ old('summary', $postCategory->getOriginal('summary')) }}</textarea>
                  @else
                    <textarea class="form-control lang-summary" name="translations[{{ $lang->code }}][summary]" data-lang="{{ $lang->code }}" rows="3">{{ old('translations.'.$lang->code.'.summary', $translations[$lang->code]['summary'] ?? ($postCategory->translations[$lang->code]['summary'] ?? '') ) }}</textarea>
                  @endif
                </div>

              </div>
            @endforeach
          </div>
        @endif

        <div class="form-group">
          <label for="status" class="col-form-label">Status</label>
          <select name="status" class="form-control">
            <option value="active" {{(($postCategory->status=='active') ? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($postCategory->status=='inactive') ? 'selected' : '')}}>Inactive</option>
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
