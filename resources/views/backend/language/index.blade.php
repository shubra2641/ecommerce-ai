@extends('backend.layouts.master')
@section('main-content')

<div class="card">
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ trans('app.all_languages') }}</h6>
            <a href="{{route('language.create')}}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> {{ trans('app.add_language') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ trans('app.id') }}</th>
                        <th>{{ trans('app.name') }}</th>
                        <th>{{ trans('app.code') }}</th>
                        <th>{{ trans('app.flag') }}</th>
                        <th>{{ trans('app.direction') }}</th>
                        <th>{{ trans('app.default') }}</th>
                        <th>{{ trans('app.status') }}</th>
                        <th>{{ trans('app.sort_order') }}</th>
                        <th>{{ trans('app.actions') }}</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>{{ trans('app.id') }}</th>
                        <th>{{ trans('app.name') }}</th>
                        <th>{{ trans('app.code') }}</th>
                        <th>{{ trans('app.flag') }}</th>
                        <th>{{ trans('app.direction') }}</th>
                        <th>{{ trans('app.default') }}</th>
                        <th>{{ trans('app.status') }}</th>
                        <th>{{ trans('app.sort_order') }}</th>
                        <th>{{ trans('app.actions') }}</th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($languages as $language)
                    <tr>
                        <td>{{$language->id}}</td>
                        <td>{{$language->name}}</td>
                        <td>{{$language->code}}</td>
                        <td>
                            @if($language->flag)
                                <span class="flag-icon flag-icon-{{$language->flag}}"></span>
                            @else
                                <i class="fas fa-flag"></i>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{$language->direction == 'rtl' ? 'warning' : 'info'}}">
                                {{strtoupper($language->direction)}}
                            </span>
                        </td>
                        <td>
                            @if($language->is_default)
                                <span class="badge badge-success">{{ trans('app.default') }}</span>
                            @else
                                <a href="{{route('language.set-default', $language->id)}}" class="btn btn-sm btn-outline-primary">
                                    {{ trans('app.set_default') }}
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($language->is_active)
                                <span class="badge badge-success">{{ trans('app.active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ trans('app.inactive') }}</span>
                            @endif
                        </td>
                        <td>{{$language->sort_order}}</td>
                        <td>
                            <a href="{{route('language.show', $language->id)}}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{route('language.edit', $language->id)}}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{route('language.toggle-status', $language->id)}}" class="btn btn-sm btn-{{$language->is_active ? 'secondary' : 'success'}}">
                                <i class="fas fa-{{$language->is_active ? 'pause' : 'play'}}"></i>
                            </a>
                            @if(!$language->is_default)
                            <form method="POST" action="{{route('language.destroy', $language->id)}}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ trans('app.are_you_sure') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
