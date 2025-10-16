@extends('backend.layouts.master')
@section('main-content')
<div class="card">
  <h5 class="card-header">{{ trans('app.message') }}</h5>
  <div class="card-body">
    @if($message)
        @if($message->photo)
        <img src="{{$message->photo}}" class="rounded-circle " style="margin-left:44%;">
        @else 
        <img src="{{asset('backend/img/avatar.png')}}" class="rounded-circle " style="margin-left:44%;">
        @endif
        <div class="py-4">{{ trans('app.from') }}: <br>
           {{ trans('app.name') }} :{{$message->name}}<br>
           {{ trans('app.email') }} :{{$message->email}}<br>
           {{ trans('app.phone_number') }} :{{$message->phone}}
        </div>
        <hr/>
  <h5 class="text-center" style="text-decoration:underline"><strong>{{ trans('app.subject') }} :</strong> {{$message->subject}}</h5>
        <p class="py-5">{{$message->message}}</p>

    @endif

  </div>
</div>
@endsection