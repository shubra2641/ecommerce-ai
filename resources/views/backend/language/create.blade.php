@extends('backend.layouts.master')
@section('main-content')

<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ trans('app.add_new_language') }}</h6>
    </div>
    <div class="card-body">
        <form method="post" action="{{route('language.store')}}">
            @csrf
            <div class="form-group">
                <label for="name" class="col-form-label">{{ trans('app.language_name') }} <span class="text-danger">*</span></label>
                <input id="name" type="text" name="name" placeholder="{{ trans('app.enter_language_name') }}" value="{{old('name')}}" class="form-control">
                @error('name')
                <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="code" class="col-form-label">{{ trans('app.language_code') }} <span class="text-danger">*</span></label>
                <input id="code" type="text" name="code" placeholder="{{ trans('app.language_code_example') }}" value="{{old('code')}}" class="form-control">
                @error('code')
                <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="flag" class="col-form-label">{{ trans('app.flag_code') }}</label>
                <input id="flag" type="text" name="flag" placeholder="{{ trans('app.flag_code_example') }}" value="{{old('flag')}}" class="form-control">
                <small class="form-text text-muted">{{ trans('app.flag_code_help') }}</small>
                @error('flag')
                <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="direction" class="col-form-label">{{ trans('app.text_direction') }} <span class="text-danger">*</span></label>
                <select name="direction" class="form-control">
                    <option value="ltr" {{old('direction') == 'ltr' ? 'selected' : ''}}>{{ trans('app.left_to_right') }}</option>
                    <option value="rtl" {{old('direction') == 'rtl' ? 'selected' : ''}}>{{ trans('app.right_to_left') }}</option>
                </select>
                @error('direction')
                <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="sort_order" class="col-form-label">{{ trans('app.sort_order') }}</label>
                <input id="sort_order" type="number" name="sort_order" placeholder="0" value="{{old('sort_order', 0)}}" class="form-control">
                <small class="form-text text-muted">{{ trans('app.sort_order_help') }}</small>
                @error('sort_order')
                <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_default" id="is_default" {{old('is_default') ? 'checked' : ''}}>
                    <label class="form-check-label" for="is_default">
                        {{ trans('app.set_as_default_language') }}
                    </label>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{old('is_active', true) ? 'checked' : ''}}>
                    <label class="form-check-label" for="is_active">
                        {{ trans('app.active') }}
                    </label>
                </div>
            </div>

            <div class="form-group mb-3">
                <a href="{{route('language.index')}}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ trans('app.back') }}
                </a>
                <button type="reset" class="btn btn-warning">
                    <i class="fas fa-undo"></i> {{ trans('app.reset') }}
                </button>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save"></i> {{ trans('app.create') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
