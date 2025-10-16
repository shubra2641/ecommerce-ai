@extends('backend.layouts.master')

@section('title')
    {{ __('newsletter.newsletter_management') }}
@endsection

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('newsletter.newsletter_management') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.newsletter.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> {{ __('newsletter.send_newsletter') }}
                        </a>
                        <a href="{{ route('admin.newsletter.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> {{ __('newsletter.export_csv') }}
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                            <p class="mb-0">{{ __('newsletter.total_subscribers') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $stats['active'] }}</h3>
                                            <p class="mb-0">{{ __('newsletter.active_subscribers') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-check fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $stats['inactive'] }}</h3>
                                            <p class="mb-0">{{ __('newsletter.inactive_subscribers') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-times fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">{{ $stats['unsubscribed'] }}</h3>
                                            <p class="mb-0">{{ __('newsletter.unsubscribed') }}</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-slash fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.newsletter.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">{{ __('newsletter.all_statuses') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('newsletter.active') }}</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('newsletter.inactive') }}</option>
                                    <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>{{ __('newsletter.unsubscribed') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="{{ __('newsletter.search_placeholder') }}" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">{{ __('newsletter.filter') }}</button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary">{{ __('newsletter.clear_filters') }}</a>
                            </div>
                        </div>
                    </form>

                    <!-- Subscribers Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('newsletter.email') }}</th>
                                    <th>{{ __('newsletter.name') }}</th>
                                    <th>{{ __('newsletter.status') }}</th>
                                    <th>{{ __('newsletter.subscribed_at') }}</th>
                                    <th>{{ __('newsletter.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                <tr>
                                    <td>{{ $subscriber->email }}</td>
                                    <td>{{ $subscriber->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $subscriber->status == 'active' ? 'success' : ($subscriber->status == 'inactive' ? 'warning' : 'danger') }}">
                                            {{ __('newsletter.' . $subscriber->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $subscriber->subscribed_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('admin.newsletter.toggle-status', $subscriber->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $subscriber->status == 'active' ? 'warning' : 'success' }}" 
                                                        title="{{ $subscriber->status == 'active' ? __('newsletter.deactivate') : __('newsletter.activate') }}">
                                                    <i class="fas fa-{{ $subscriber->status == 'active' ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('newsletter.unsubscribe') }}"
                                                        onclick="return confirmUnsubscribe()">
                                                    <i class="fas fa-user-slash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('newsletter.no_subscribers') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $subscribers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function confirmUnsubscribe() {
    return confirm('Are you sure you want to unsubscribe this user?');
}
</script>
