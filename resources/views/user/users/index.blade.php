@extends('user.layouts.master')

@section('title', getPageTitle(__('user.users_list')))

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
  <h6 class="m-0 font-weight-bold text-primary float-left">{{ __('user.users_list') }}</h6>
  <a href="{{route('users.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="{{ __('user.add_user') }}"><i class="fas fa-plus"></i> {{ __('user.add_user') }}</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="user-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>{{ __('user.table.sn') }}</th>
              <th>{{ __('user.table.name') }}</th>
              <th>{{ __('user.table.email') }}</th>
              <th>{{ __('user.photo') }}</th>
              <th>{{ __('user.join_date') }}</th>
              <th>{{ __('user.role') }}</th>
              <th>{{ __('user.table.status') }}</th>
              <th>{{ __('user.table.action') }}</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
                <th>{{ __('user.table.sn') }}</th>
                <th>{{ __('user.table.name') }}</th>
                <th>{{ __('user.table.email') }}</th>
                <th>{{ __('user.photo') }}</th>
                <th>{{ __('user.join_date') }}</th>
                <th>{{ __('user.role') }}</th>
                <th>{{ __('user.table.status') }}</th>
                <th>{{ __('user.table.action') }}</th>
              </tr>
          </tfoot>
          <tbody>
            @foreach($users as $user)   
                <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td>
                        @if($user->photo)
                            <img src="{{$user->photo}}" class="img-fluid rounded-circle" alt="{{$user->photo}}">
                        @else
                            <img src="{{asset('backend/img/avatar.png')}}" class="img-fluid rounded-circle" alt="avatar.png">
                        @endif
                    </td>
                    <td>{{(($user->created_at)? $user->created_at->diffForHumans() : '')}}</td>
                    <td>{{$user->role}}</td>
                    <td>
                        @if($user->status=='active')
                            <span class="badge badge-success">{{$user->status}}</span>
                        @else
                            <span class="badge badge-warning">{{$user->status}}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{route('users.edit',$user->id)}}" class="btn btn-primary btn-sm float-left mr-1 rounded-circle p-0" data-toggle="tooltip" title="{{ __('user.edit') }}" data-placement="bottom"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{route('users.destroy',[$user->id])}}">
                      @csrf 
                      @method('delete')
                          <button class="btn btn-danger btn-sm dltBtn rounded-circle p-0" data-id={{$user->id}} data-toggle="tooltip" data-placement="bottom" title="{{ __('user.delete') }}"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                    {{-- Delete Modal --}}
                    {{-- <div class="modal fade" id="delModal{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="#delModal{{$user->id}}Label" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="#delModal{{$user->id}}Label">Delete user</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <form method="post" action="{{ route('users.destroy',$user->id) }}">
                                @csrf 
                                @method('delete')
                                <button type="submit" class="btn btn-danger">Parmanent delete user</button>
                              </form>
                            </div>
                          </div>
                        </div>
                    </div> --}}
                </tr>  
            @endforeach
          </tbody>
        </table>
  <span class="float-right">{{$users->links()}}</span>
      </div>
    </div>
</div>
@endsection
