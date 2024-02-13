@auth
    <form class="d-inline-block like-form"
        data-like-route="{{ route('like', [
            'type' => strtolower(class_basename($instance)),
            'id' => $instance->id,
        ]) }}"
        data-dislike-route="{{ route('dislike', [
            'type' => strtolower(class_basename($instance)),
            'id' => $instance->id,
        ]) }}">
        @csrf

        <button role="button" type="submit" class="btn-link like-button" data-type="like">
            <small>
                <i class="fa fa-thumbs-up"></i>
                @isset($instance->likes)
                    <span class="like-count">{{ '' . count($instance->likes) }}</span>
                @endisset
            </small>
        </button>

        <button role="button" type="submit" class="btn-link like-button" data-type="dislike">
            <small>
                <i class="fa fa-thumbs-down"></i>
                @isset($instance->dislikes)
                    <span class="dislike-count">{{ '' . count($instance->dislikes) }}</span>
                @endisset
            </small>
        </button>
    </form>
@else
    <small>
        <i class="fa fa-thumbs-up"></i>
        @isset($instance->likes)
            <span class="like-count">{{ '' . count($comment->likes) }}</span>
        @endisset
    </small>

    <small>
        <i class="fa fa-thumbs-down"></i>
        @isset($instance->dislikes)
            <span class="dislike-count">{{ '' . count($comment->dislikes) }}</span>
        @endisset
    </small>
@endauth
@section('script')
    @include('public.layouts.scripts.submitHandler')
@endsection
