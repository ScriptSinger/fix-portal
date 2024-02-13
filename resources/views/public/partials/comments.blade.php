<div class="custombox clearfix">
    <button data-toggle="collapse" data-target="#collapsedComment" role="button" class="btn btn-dark btn-link small-title">
        {{ $instance->comments->count() }}
        {{ trans_choice('комментарий|комментария|комментариев', $instance->comments->count(), [], 'ru') }}</button>
    <div class="row">
        <div class="col-lg-12">
            <div id="collapsedComment" class="comments-list collapse show">
                @foreach ($instance->comments as $comment)
                    <div>
                        <div class="media">
                            <div class="media-left">
                                <a>
                                    <img src="{{ $comment->user->avatar !== null ? asset('storage/' . $comment->user->avatar) : asset('assets/front/images/avatar.png') }}"
                                        alt="" class="rounded-circle" width="64px" height="64px">
                                </a>
                            </div>
                            <div class="media-body">
                                <div class="media-heading user_name">
                                    {{ $comment->user->name }}
                                    <small>{{ $comment->dateAsCarbon->diffForHumans() }}</small>
                                    @can('delete', $comment)
                                        <form
                                            action="{{ route('comments.destroy', [
                                                'id' => $comment->id,
                                            ]) }}"
                                            method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button role="button" type="submit" class="btn-link btn-sm text-reset"
                                                onclick="return confirm('Подтвердите удаление')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="">{!! $comment->text !!}</div>
                        <hr class="invis">
                        <div class="blog-meta big-meta">
                            @include('public.partials.likes', [
                                'instance' => $comment,
                            ])
                            @auth('web')
                                <a data-toggle="collapse" data-target="#replyForm{{ $comment->id }}"
                                    role="button">Ответить
                                </a>
                                <div id="replyForm{{ $comment->id }}" class="collapse media-body mb-4">
                                    <form class="form-wrapper" method="POST" enctype="multipart/form-data"
                                        action="{{ route('comments.replies.store', ['id' => $comment->id]) }}">
                                        @csrf
                                        <div class="form-group">
                                            <textarea class="form-control summernote" name="text" placeholder="Ваш ответ"
                                                data-upload-url="{{ route('api.summernote.upload') }}"></textarea>
                                        </div>
                                        <div class="text-right">
                                            <button role="button" type="submit" class="btn btn-dark">Отправить</button>
                                        </div>
                                    </form>
                                </div>
                            @endauth
                        </div>
                        <hr class="invis">
                        <div class="custombox clearfix p-3">
                            <h4 class="small-title">
                                <a data-toggle="collapse" data-target="#collapsedReply{{ $comment->id }}"
                                    role="button">
                                    {{ $comment->replies->count() }}
                                    {{ trans_choice('ответ|ответа|ответов', $comment->replies->count(), [], 'ru') }}
                                </a>
                            </h4>
                            <div id="collapsedReply{{ $comment->id }}" class="collapse">
                                @foreach ($comment->replies as $reply)
                                    <div class="media">
                                        <a class="media-left" href="#">
                                            <img src="{{ $reply->user->avatar !== null ? asset('storage/' . $reply->user->avatar) : asset('assets/front/images/avatar.png') }}"
                                                alt="" class="rounded-circle" width="64px" height="64px">
                                        </a>
                                        <div class="media-body">
                                            <div class="media-heading">
                                                <div class="blog-meta big-meta">
                                                    <small class="media-heading user_name">
                                                        {{ $reply->user->name }}</small>
                                                    <small>{{ $reply->dateAsCarbon->diffForHumans() }}</small>
                                                    @can('delete', $reply)
                                                        <form
                                                            action="{{ route('comments.replies.destroy', ['id' => $reply->id]) }}"
                                                            method="POST" class="d-inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button role="button" type="submit"
                                                                class="btn-link btn-sm text-reset"
                                                                onclick="return confirm('Подтвердите удаление')">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>{!! $reply->text !!}</div>
                                    <div class="blog-meta big-meta">
                                        @include('public.partials.likes', [
                                            'instance' => $reply,
                                        ])
                                    </div>
                                    <hr>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<hr class="invis1">
@auth('web')
    <div id="commentFormWrapper" class="custombox clearfix">
        <h4 class="small-title">Оставить комментарий</h4>
        <div class="row">
            <div class="col-lg-12">
                <form class="form-wrapper" method="POST" enctype="multipart/form-data"
                    action="{{ route('comments.store', [
                        'type' => strtolower(class_basename($instance)),
                        'id' => $instance->id,
                    ]) }}">
                    @csrf
                    <div class="form-group">
                        <textarea class="form-control summernote" name="text" data-upload-url="{{ route('api.summernote.upload') }}"></textarea>
                    </div>
                    <button role="button" type="submit" class="btn btn-dark">Отправить</button>
                </form>
            </div>
        </div>
    </div>
@endauth
@guest
    <div class="blog-content">Чтобы оставить комментарий, необходимо <a class="text-primary"
            href="{{ route('register') }}">зарегистрироваться</a> или <a class="text-primary"
            href="{{ route('login') }}">войти</a></div>
@endguest

@push('scripts')
    <script src="{{ asset('assets/front/js/custom/summernote/basic.js') }}"></script>
    <script src="{{ asset('assets/front/js/custom/likes.js') }}"></script>
@endpush
