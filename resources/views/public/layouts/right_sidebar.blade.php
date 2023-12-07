<!DOCTYPE html>
<html lang="en">

@include('public.layouts.includes.head')

<body>
    <div id="wrapper">
        @include('public.layouts.includes.header')

        @yield('banner')


        <section class="section lb @if (Request::is('articles/*') || Request::is('questions/*')) ) m3rem @endif">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                        @yield('content')
                    </div><!-- end col -->

                    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        @include('public.layouts.includes.sidebar')
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- end container -->
        </section>

        @include('public.layouts.includes.footer')

        <div class="dmtop">Scroll to Top</div>

    </div><!-- end wrapper -->

    @include('public.layouts.includes.script')
</body>

</html>
