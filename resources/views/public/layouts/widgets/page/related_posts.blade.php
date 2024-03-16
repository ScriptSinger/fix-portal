  @isset($relatedPosts)
      <hr class="invis1">
      <div class="custombox clearfix">
          <h4 class="small-title">Вам также может понравиться</h4>
          <div class="row">
              @foreach ($relatedPosts as $post)
                  <div class="col-lg-6">
                      <div class="blog-box">
                          <div class="post-media">
                              <a href="marketing-single.html" title="">
                                  <img src="{{ $post->thumbnail }}" alt="" class="img-fluid">
                                  <div class="hovereffect">
                                      <span class=""></span>
                                  </div><!-- end hover -->
                              </a>
                          </div><!-- end media -->
                          <div class="blog-meta">
                              <h4><a href="{{ route('articles.show', ['article' => $post->slug]) }}"
                                      title="">{{ $post->title }}</a>
                              </h4>

                              {{-- <small><a href="{{ route('categories.show', ['category' => $post->category->slug]) }}"
                                      title=""> {{ $post->category->title }}
                                  </a></small> --}}

                              {{-- <small>{{ $post->dateAsCarbon->diffForHumans() }}</small> --}}
                          </div>

                      </div><!-- end blog-box -->
                  </div><!-- end col -->
              @endforeach
          </div><!-- end row -->
      </div><!-- end custom-box -->
      <hr class="invis1">
  @endisset
