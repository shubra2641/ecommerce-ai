@extends('layouts.app')

@section('title', __('auth.verify_email'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-envelope mr-2"></i>
                        {{ __('auth.verify_email') }}
                    </h1>
                </div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ __('auth.verification_sent') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open-text fa-3x text-primary mb-3"></i>
                        <p class="text-muted">{{ __('auth.before_check_email') }}</p>
                    </div>

                    <div class="text-center">
                        <p class="mb-3">{{ __('auth.if_not_received') }}</p>
                        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane mr-2"></i>
                                {{ __('auth.click_request_another') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
