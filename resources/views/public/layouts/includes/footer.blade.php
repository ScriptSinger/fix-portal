<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                @include('public.layouts.includes.footer_widgets.recent_posts')
            </div>

            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                @include('public.layouts.includes.footer_widgets.popular_posts')
            </div>

            <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                @include('public.layouts.includes.footer_widgets.popular_categories')
            </div><!-- end col -->
        </div><!-- end row -->

        <div class="row">
            <div class="col-md-12 text-center">
                <br>
                <br>
                <div class="copyright">&copy;
                    {{ optional($customization)->copyright ?? 'Copyright 2014-2024 Ufamasters.ru. Все права защищены.' }}
                </div>
            </div>
        </div>
    </div><!-- end container -->
</footer><!-- end footer -->
