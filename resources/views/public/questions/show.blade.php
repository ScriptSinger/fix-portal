@extends('public.layouts.banner')
@section('title', "$question->title | " . config('app.name', 'Ufamasters'))

@section('sidebar')
    <div class="sidebar">
        @include('public.layouts.widgets.sidebar.prime_posts')
        @include('public.layouts.widgets.sidebar.advertising')
        @include('public.layouts.widgets.sidebar.prime_categories')
    </div>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="blog-title-area">
            {{ Breadcrumbs::render('question', $question->appliance, $question) }}

            <span class="color-yellow"><a
                    href="{{ route('public.appliances.show', ['appliance' => $question->appliance->slug]) }}"
                    title="">{{ $question->appliance->title }}</a></span>

            <h3>{{ $question->title }}</h3>
            <div class="blog-meta big-meta">
                <small>{{ $question->dateAsCarbon->diffForHumans() }}</small>
                <small><a href="blog-author.html" title="">{{ optional($question->user)->name }}</a></small>
                <small><i class="fa fa-eye"></i> {{ $question->views }}</small>

                @can('update', $question)
                    <small>
                        <a href="{{ route('questions.edit', ['question' => $question->slug]) }}">
                            <i class="fa fa-edit"></i>
                            <b>Редактировать</b>
                        </a>
                    </small>
                @endcan

                @can('delete', $question)
                    <small>
                        <a href="#"
                            onclick="event.preventDefault(); 
              document.getElementById('removeQuestion').submit();">
                            <i class="fa fa-trash"></i>
                            <b>Удалить</b>
                        </a>
                        <form id="removeQuestion" action="{{ route('questions.destroy', ['question' => $question->id]) }}"
                            method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </small>
                @endcan
            </div><!-- end meta -->
            @include('public.layouts.widgets.sharing', ['reference' => $question->slug])
            <!-- end post-sharing -->
        </div><!-- end title -->

        @if ($question->photos)
            <div class="single-post-media">
                @foreach (json_decode($question->photos) as $photo)
                    <img src="{{ asset('storage/' . $photo) }}" alt="Photo" class="img-fluid">
                    <hr class="invis">
                @endforeach
            </div><!-- end media -->
        @endif

        <div class="blog-content mb-5">
            {!! $question->description !!}
        </div><!-- end content -->

        <hr class="invis1">
        @include('public.layouts.widgets.page.related_posts')
        <hr class="invis1">

        @include('public.layouts.widgets.page.comments', [
            'instance' => $question,
        ])
        @include('public.layouts.modal.show')
    </div><!-- end page-wrapper -->
@endsection
@push('scripts')
    <script src="{{ asset('assets/front/js/custom/modal/blogModal.js') }}"></script>
@endpush
