@extends('backend.layouts.master')

@section('main-content')
<div class="card">
    <h5 class="card-header">{{ trans('app.edit_payment_gateway') }}</h5>
    <div class="card-body">
    <form method="post" action="{{ route('admin.payment-gateways.update', $gateway->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $gateway->name }}" required>
        </div>
    {{-- slug is managed automatically based on gateway type/name --}}
        @if(!in_array($type, ['offline','cod']))
        <div class="form-group">
            <label>Mode</label>
            <select name="mode" class="form-control">
                <option value="sandbox" {{ $gateway->mode=='sandbox'?'selected':'' }}>Sandbox</option>
                <option value="live" {{ $gateway->mode=='live'?'selected':'' }}>Live</option>
            </select>
        </div>
        @endif
        <div class="form-group">
            <label>Enabled</label>
            <input type="checkbox" name="enabled" value="1" {{ $gateway->enabled ? 'checked' : '' }}>
        </div>
        {{-- Offline-specific fields --}}
        @if($gateway->slug === 'offline')
        <div class="form-group">
            <label>Offline transfer details (optional)</label>
            <textarea name="transfer_details" class="form-control" rows="3">{{ old('transfer_details', $gateway->transfer_details ?? '') }}</textarea>
            <small class="form-text text-muted">If this gateway is an offline transfer method, put account details or instructions here.</small>
        </div>
        <div class="form-group">
            <label>Require transfer proof upload</label>
            <input type="checkbox" name="require_proof" value="1" {{ ($gateway->require_proof ?? false) ? 'checked' : '' }}>
            <small class="form-text text-muted">Tick to require customers to upload a transfer/receipt image at checkout when selecting this gateway.</small>
        </div>
        @endif
        @if(!in_array($type, ['offline','cod']))
        <div class="form-group">
            <label>Credentials</label>
            @if($type && isset($availableGateways[$type]))
                @foreach($availableGateways[$type]['fields'] as $inputName => $label)
                    <div class="form-group">
                        <label>{{ $label }}</label>
                        <input type="text" name="{{ $inputName }}" class="form-control" value="{{ old($credentialKeys[$inputName] ?? str_replace(['[',']'], ['.',''], $inputName), $credentialExisting[$inputName] ?? '') }}">
                    </div>
                @endforeach
            @else
                {{-- fallback: show generic fields based on gateway type --}}
                @if($gateway->slug === 'paypal')
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <h6>Sandbox</h6>
                            <input type="text" name="credentials[sandbox][client_id]" class="form-control" placeholder="Sandbox Client ID" value="{{ old('credentials.sandbox.client_id', optional($gateway->credentials)['sandbox']['client_id'] ?? '') }}">
                            <input type="text" name="credentials[sandbox][client_secret]" class="form-control mt-2" placeholder="Sandbox Client Secret" value="{{ old('credentials.sandbox.client_secret', optional($gateway->credentials)['sandbox']['client_secret'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <h6>Live</h6>
                            <input type="text" name="credentials[live][client_id]" class="form-control" placeholder="Live Client ID" value="{{ old('credentials.live.client_id', optional($gateway->credentials)['live']['client_id'] ?? '') }}">
                            <input type="text" name="credentials[live][client_secret]" class="form-control mt-2" placeholder="Live Client Secret" value="{{ old('credentials.live.client_secret', optional($gateway->credentials)['live']['client_secret'] ?? '') }}">
                        </div>
                    </div>
                @elseif($gateway->slug === 'stripe')
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <h6>Test</h6>
                            <input type="text" name="credentials[test][publishable_key]" class="form-control" placeholder="Test Publishable Key" value="{{ old('credentials.test.publishable_key', optional($gateway->credentials)['test']['publishable_key'] ?? '') }}">
                            <input type="text" name="credentials[test][secret_key]" class="form-control mt-2" placeholder="Test Secret Key" value="{{ old('credentials.test.secret_key', optional($gateway->credentials)['test']['secret_key'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <h6>Live</h6>
                            <input type="text" name="credentials[live][publishable_key]" class="form-control" placeholder="Live Publishable Key" value="{{ old('credentials.live.publishable_key', optional($gateway->credentials)['live']['publishable_key'] ?? '') }}">
                            <input type="text" name="credentials[live][secret_key]" class="form-control mt-2" placeholder="Live Secret Key" value="{{ old('credentials.live.secret_key', optional($gateway->credentials)['live']['secret_key'] ?? '') }}">
                        </div>
                    </div>
                @endif
            @endif
        </div>
        @endif

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> {{ trans('app.update') }}
            </button>
            <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> {{ trans('app.cancel') }}
            </a>
        </div>
    </form>
    </div>
</div>
@endsection
