@extends('backend.layouts.master')
@section('main-content')

<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ trans('app.language_details') }}: {{$language->name}}</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>{{ trans('app.id') }}</th>
                        <td>{{$language->id}}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.name') }}</th>
                        <td>{{$language->name}}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.code') }}</th>
                        <td>{{$language->code}}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.flag') }}</th>
                        <td>
                            @if($language->flag)
                                <span class="flag-icon flag-icon-{{$language->flag}}"></span> {{$language->flag}}
                            @else
                                <i class="fas fa-flag"></i> {{ trans('app.no_flag_set') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.direction') }}</th>
                        <td>
                            <span class="badge badge-{{$language->direction == 'rtl' ? 'warning' : 'info'}}">
                                {{strtoupper($language->direction)}}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>{{ trans('app.default_language') }}</th>
                        <td>
                            @if($language->is_default)
                                <span class="badge badge-success">{{ trans('app.yes') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ trans('app.no') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.status') }}</th>
                        <td>
                            @if($language->is_active)
                                <span class="badge badge-success">{{ trans('app.active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans('app.inactive') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.sort_order') }}</th>
                        <td>{{$language->sort_order}}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.created_at') }}</th>
                        <td>{{$language->created_at->format('Y-m-d H:i:s')}}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.updated_at') }}</th>
                        <td>{{$language->updated_at->format('Y-m-d H:i:s')}}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <h5>{{ trans('app.language_files') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ trans('app.file_name') }}</th>
                                <th>{{ trans('app.status') }}</th>
                                <th>{{ trans('app.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $file)
                            <tr>
                                <td>{{$file}}</td>
                                <td>
                                    @if(File::exists($langPath . '/' . $file))
                                        <span class="badge badge-success">{{ trans('app.exists') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ trans('app.missing') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(File::exists($langPath . '/' . $file))
                                        <a href="{{route('language.edit-file', [$language->id, $file])}}" class="btn btn-sm btn-info">{{ trans('app.edit') }}</a>
                                    @else
                                        <a href="{{route('language.create-file', [$language->id, $file])}}" class="btn btn-sm btn-warning">{{ trans('app.create') }}</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-group mt-4">
            <a href="{{route('language.index')}}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ trans('app.back_to_languages') }}
            </a>
            <a href="{{route('language.edit', $language->id)}}" class="btn btn-warning">
                <i class="fas fa-edit"></i> {{ trans('app.edit_language') }}
            </a>
        </div>
    </div>
</div>

@endsection
