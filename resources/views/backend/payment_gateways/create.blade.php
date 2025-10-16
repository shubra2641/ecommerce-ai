@extends('backend.layouts.master')

@section('main-content')
<div class="card">
    <h5 class="card-header">{{ trans('app.add_payment_gateway') }}
        @if(request('prefill_name'))
            <small class="text-muted"> - {{ request('prefill_name') }}</small>
        @endif
    </h5>
    <div class="card-body">
    <form method="post" action="{{ route('admin.payment-gateways.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label>Type</label>
                @if(!empty($type))
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="form-control-plaintext">{{ $availableGateways[$type]['name'] ?? $type }}</div>
                @else
                    <select name="type" class="form-control">
                        <option value="">-- Select gateway type --</option>
                        @foreach($availableGateways as $slug => $info)
                            <option value="{{ $slug }}" {{ request('prefill_slug') == $slug ? 'selected' : '' }}>{{ $info['name'] }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

    {{-- selected_type is provided by controller as $selected_type --}}
        <div class="form-row align-items-end">
            <div class="form-group col-md-4">
                @if(!in_array($selected_type, ['offline','cod']))
                <label>Mode</label>
                <select name="mode" class="form-control">
                    <option value="sandbox">Sandbox</option>
                    <option value="live">Live</option>
                </select>
                @endif
            </div>
            <div class="form-group col-md-2">
                <label>Enabled</label>
                <div><input type="checkbox" name="enabled" value="1"></div>
            </div>
            <div class="form-group col-md-6 text-right">
                <!-- Buttons moved to bottom of form -->
            </div>
        </div>

        {{-- Offline-specific fields --}}
        @if($selected_type === 'offline')
        <div class="form-group">
            <label>Offline transfer details (optional)</label>
            <textarea name="transfer_details" class="form-control" rows="3" placeholder="e.g. Bank: X, Account: 123456, IBAN: ...">{{ old('transfer_details') }}</textarea>
            <small class="form-text text-muted">If this gateway is an offline transfer method, put account details or instructions here.</small>
        </div>
        <div class="form-group">
            <label>Require transfer proof upload</label>
            <input type="checkbox" name="require_proof" value="1" {{ old('require_proof') ? 'checked' : '' }}>
            <small class="form-text text-muted">Tick to require customers to upload a transfer/receipt image at checkout when selecting this gateway.</small>
        </div>
        @endif

        {{-- Credentials only for online gateways --}}
        @if(!in_array($selected_type, ['offline','cod']))
        <div class="form-group" id="gateway-credentials-area">
            <label>Credentials</label>
            @if(!empty($selected_type) && isset($availableGateways[$selected_type]))
                @foreach($availableGateways[$selected_type]['fields'] as $inputName => $label)
                    <div class="form-group">
                        <label>{{ $label }}</label>
                        <input type="text" name="{{ $inputName }}" class="form-control" value="{{ old(str_replace(['[',']'], ['.',''], $inputName)) }}">
                    </div>
                @endforeach
            @else
                <small class="form-text text-muted">Select a gateway type to show required credential fields.</small>
            @endif
        </div>
        @endif

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> {{ trans('app.save') }}
            </button>
            <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> {{ trans('app.cancel') }}
            </a>
        </div>
    </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('select[name="type"]');
    const credentialsArea = document.getElementById('gateway-credentials-area');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            if (selectedType && ['paypal', 'stripe'].includes(selectedType)) {
                // Show credentials fields based on type
                if (selectedType === 'paypal') {
                    credentialsArea.innerHTML = `
                        <label>Credentials</label>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <h6>Sandbox</h6>
                                <input type="text" name="credentials[sandbox][client_id]" class="form-control" placeholder="Sandbox Client ID" value="{{ old('credentials.sandbox.client_id') }}">
                                <input type="text" name="credentials[sandbox][client_secret]" class="form-control mt-2" placeholder="Sandbox Client Secret" value="{{ old('credentials.sandbox.client_secret') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <h6>Live</h6>
                                <input type="text" name="credentials[live][client_id]" class="form-control" placeholder="Live Client ID" value="{{ old('credentials.live.client_id') }}">
                                <input type="text" name="credentials[live][client_secret]" class="form-control mt-2" placeholder="Live Client Secret" value="{{ old('credentials.live.client_secret') }}">
                            </div>
                        </div>
                    `;
                } else if (selectedType === 'stripe') {
                    credentialsArea.innerHTML = `
                        <label>Credentials</label>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <h6>Test</h6>
                                <input type="text" name="credentials[test][publishable_key]" class="form-control" placeholder="Test Publishable Key" value="{{ old('credentials.test.publishable_key') }}">
                                <input type="text" name="credentials[test][secret_key]" class="form-control mt-2" placeholder="Test Secret Key" value="{{ old('credentials.test.secret_key') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <h6>Live</h6>
                                <input type="text" name="credentials[live][publishable_key]" class="form-control" placeholder="Live Publishable Key" value="{{ old('credentials.live.publishable_key') }}">
                                <input type="text" name="credentials[live][secret_key]" class="form-control mt-2" placeholder="Live Secret Key" value="{{ old('credentials.live.secret_key') }}">
                            </div>
                        </div>
                    `;
                }
            } else {
                credentialsArea.innerHTML = '<small class="form-text text-muted">Select a gateway type to show required credential fields.</small>';
            }
        });
        
        // Trigger change event if type is pre-selected
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush
