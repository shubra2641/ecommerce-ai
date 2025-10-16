{{-- depth is supplied by the including view; default behavior expects it to be set by the caller --}}
@foreach($comments as $comment)
{{-- {{dd($comments)}} --}}
{{-- Comment depth handled in controller --}}
<div class="display-comment"   @if($comment->parent_id != null) style="margin-left:40px;" @endif>
    <div class="comment-list">
        <div class="single-comment">
            @if($comment->user_info['photo'])
                <img src="{{$comment->user_info['photo']}}" alt="#">
            @else 
                <img src="{{asset('backend/img/avatar.png')}}" alt="">
            @endif
            <div class="content">
                {{-- {{$post}} --}}
            <h4>{{$comment->user_info['name']}} <span>At {{$comment->created_at->format('g: i a')}} On {{$comment->created_at->format('M d Y')}}</span></h4>
                <p>{{$comment->comment}}</p>
                @if(!empty($depth))
                <div class="button">
                    <a href="#" class="btn btn-reply reply" data-id="{{$comment->id}}"><i class="fa fa-reply" aria-hidden="true"></i>{{ __('frontend.comment.reply') }}</a>
                    <a href="" class="btn btn-reply cancel" style="display: none;" ><i class="fa fa-trash" aria-hidden="true"></i>{{ __('frontend.comment.cancel') }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @include('frontend.pages.comment', ['comments' => $comment->replies, 'depth' => $depth])

</div>    
@endforeach