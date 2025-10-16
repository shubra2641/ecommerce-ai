@extends('backend.layouts.master')

@section('main-content')
<div class="card">
    <h5 class="card-header">{{ trans('app.all_payment_gateways') }}
    </h5>
    <div class="card-body">
        <div class="row">
            @if(!empty($availableGateways))
                @foreach($availableGateways as $slug => $info)
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="mr-3"><i class="{{ $info['icon'] }} fa-2x"></i></div>
                                <div>
                                    <h5 class="mb-0">{{ $info['name'] }}</h5>
                                    <div class="small text-muted">{{ $info['desc'] }}</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                @if(isset($configuredMap[$slug]) && $configuredMap[$slug])
                                    <span class="badge badge-info">{{ trans('app.configured') }}</span>
                                    @if($configuredMap[$slug]->enabled)
                                        <span class="badge badge-success">{{ trans('app.enabled') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ trans('app.disabled') }}</span>
                                    @endif
                                    <div class="mt-2">
                                        <a href="{{ route('admin.payment-gateways.edit', $configuredMap[$slug]->id) }}" class="btn btn-sm btn-outline-primary">{{ trans('app.configure') }}</a>
                                        <a href="{{ route('admin.payment-gateways.toggle', $configuredMap[$slug]->id) }}" data-toggle-gateway class="btn btn-sm btn-{{ $configuredMap[$slug]->enabled ? 'warning' : 'success' }} ml-1">{{ $configuredMap[$slug]->enabled ? trans('app.disable') : trans('app.enable') }}</a>
                                    </div>
                                @else
                                    <a href="{{ route('admin.payment-gateways.create') }}?prefill_slug={{ $slug }}&prefill_name={{ urlencode($info['name']) }}" class="btn btn-sm btn-primary">{{ trans('app.activate') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        
        <!-- Existing gateways list fallback -->
        <hr>
        <h6>All gateways</h6>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ trans('app.gateway_name') }}</th>
                    <th>{{ trans('app.mode') }}</th>
                    <th>{{ trans('app.status') }}</th>
                    <th>{{ trans('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gateways as $g)
                <tr>
                    <td>{{ $g->name }}</td>
                    <td>{{ ucfirst($g->mode) }}</td>
                    <td>{{ $g->enabled ? 'Enabled' : 'Disabled' }}</td>
                    <td>
                        <a href="{{ route('admin.payment-gateways.edit', $g->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('admin.payment-gateways.toggle', $g->id) }}" data-toggle-gateway class="btn btn-sm btn-{{ $g->enabled ? 'warning' : 'success' }} ml-1">{{ $g->enabled ? 'Disable' : 'Enable' }}</a>
                        <form action="{{ route('admin.payment-gateways.destroy', $g->id) }}" method="POST" class="d-inline-block ml-1">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
    @endsection
