<!DOCTYPE html>
<html lang="en">

@include('public.layouts.includes.head')

<body>
    <div id="wrapper">
        @include('public.layouts.includes.header')
        @yield('page-title')
        <section class="section lb">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                        @yield('sidebar')
                    </div>

                    <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                        @yield('content')
                    </div>

                </div>
            </div>
        </section>

        @include('public.layouts.includes.footer')

        <div class="dmtop">Scroll to Top</div>

    </div><!-- end wrapper -->
    @include('public.layouts.includes.script')

</body>

</html>
