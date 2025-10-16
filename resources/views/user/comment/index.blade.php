@extends('user.layouts.master')

@section('title', getPageTitle(__('user.comments')))

@section('main-content')
<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
            @include('user.layouts.notification')
        </div>
    </div>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary float-left">
            <i class="fas fa-comments"></i> {{ __('user.comments') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(count($comments)>0)
                <table class="table table-bordered" id="comments-dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('user.table.sn') }}</th>
                            <th>{{ __('user.author') }}</th>
                            <th>{{ __('user.post_title') }}</th>
                            <th>{{ __('user.message') }}</th>
                            <th>{{ __('user.date') }}</th>
                            <th>{{ __('user.status') }}</th>
                            <th>{{ __('user.action') }}</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>{{ __('user.table.sn') }}</th>
                            <th>{{ __('user.author') }}</th>
                            <th>{{ __('user.post_title') }}</th>
                            <th>{{ __('user.message') }}</th>
                            <th>{{ __('user.date') }}</th>
                            <th>{{ __('user.status') }}</th>
                            <th>{{ __('user.action') }}</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @foreach($comments as $comment)
                            <tr>
                                <td>{{$comment->id}}</td>
                                <td>{{$comment->user_info['name']}}</td>
                                <td>{{$comment->post->title}}</td>
                                <td>{{Str::limit($comment->comment, 50)}}</td>
                                <td>{{$comment->created_at->format('M d D, Y g: i a')}}</td>
                                <td>
                                    @if($comment->status=='active')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> {{$comment->status}}
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> {{$comment->status}}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="{{ __('user.actions') }}">
                                        <a href="{{route('user.post-comment.edit',$comment->id)}}" 
                                           class="btn btn-primary btn-sm" 
                                           data-toggle="tooltip" 
                                           title="{{ __('user.edit') }}" 
                                           data-placement="bottom">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{route('user.post-comment.delete',[$comment->id])}}" class="d-inline">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn-danger btn-sm dltBtn" 
                                                    data-id="{{$comment->id}}" 
                                                    data-toggle="tooltip" 
                                                    data-placement="bottom" 
                                                    title="{{ __('user.delete') }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-end">
                    {{$comments->links()}}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">{{ __('user.no_comments') }}</h6>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
