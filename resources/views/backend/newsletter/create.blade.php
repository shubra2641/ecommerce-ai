@extends('backend.layouts.master')

@section('title')
    {{ __('newsletter.send_newsletter') }}
@endsection

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('newsletter.send_newsletter') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('newsletter.back_to_list') }}
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.newsletter.send') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Email Content -->
                                <div class="form-group">
                                    <label for="subject">{{ __('newsletter.subject') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject') }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="content">{{ __('newsletter.content') }} <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="15" required>{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('newsletter.content_help') }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Recipients Selection -->
                                <div class="form-group">
                                    <label for="recipients">{{ __('newsletter.recipients') }} <span class="text-danger">*</span></label>
                                    <select class="form-control @error('recipients') is-invalid @enderror" 
                                            id="recipients" name="recipients" required>
                                        <option value="">{{ __('newsletter.select_recipients') }}</option>
                                        <option value="active" {{ old('recipients') == 'active' ? 'selected' : '' }}>
                                            {{ __('newsletter.active_subscribers') }} ({{ \App\Models\Newsletter::active()->count() }})
                                        </option>
                                        <option value="inactive" {{ old('recipients') == 'inactive' ? 'selected' : '' }}>
                                            {{ __('newsletter.inactive_subscribers') }} ({{ \App\Models\Newsletter::inactive()->count() }})
                                        </option>
                                        <option value="all" {{ old('recipients') == 'all' ? 'selected' : '' }}>
                                            {{ __('newsletter.all_subscribers') }} ({{ \App\Models\Newsletter::whereIn('status', ['active', 'inactive'])->count() }})
                                        </option>
                                    </select>
                                    @error('recipients')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Test Email -->
                                <div class="form-group">
                                    <label for="test_email">{{ __('newsletter.test_email') }}</label>
                                    <input type="email" class="form-control @error('test_email') is-invalid @enderror" 
                                           id="test_email" name="test_email" value="{{ old('test_email') }}" 
                                           placeholder="{{ __('newsletter.test_email_placeholder') }}">
                                    @error('test_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('newsletter.test_email_help') }}
                                    </small>
                                </div>

                                <!-- Action Buttons -->
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block" name="action" value="test">
                                        <i class="fas fa-paper-plane"></i> {{ __('newsletter.send_test') }}
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-block" name="action" value="send"
                                            onclick="return confirm('{{ __('newsletter.confirm_send') }}')">
                                        <i class="fas fa-broadcast-tower"></i> {{ __('newsletter.send_to_all') }}
                                    </button>
                                </div>

                                <!-- Help Text -->
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> {{ __('newsletter.important_note') }}</h5>
                                    <ul class="mb-0">
                                        <li>{{ __('newsletter.test_first') }}</li>
                                        <li>{{ __('newsletter.unsubscribe_link') }}</li>
                                        <li>{{ __('newsletter.email_delivery') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-resize textarea
    $('#content').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Form validation
    $('form').on('submit', function(e) {
        const action = $('button[type="submit"]:focus').attr('name');
        
        if (action === 'send') {
            const recipients = $('#recipients').val();
            if (!recipients) {
                e.preventDefault();
                alert('{{ __('newsletter.please_select_recipients') }}');
                return false;
            }
        }
    });
});
</script>
@endpush
