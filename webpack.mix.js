const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.styles(
    [
        "resources/assets/admin/plugins/fontawesome-free/css/all.css",
        "resources/assets/admin/plugins/select2/css/select2.css",
        "resources/assets/admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.css",
        "resources/assets/admin/css/adminlte.css",
        "resources/assets/admin/plugins/datatables-bs4/css/dataTables.bootstrap4.css",
        "resources/assets/admin/plugins/datatables-responsive/css/responsive.bootstrap4.css",
        "resources/assets/admin/plugins/datatables-select/css/select.bootstrap4.css",
        "resources/assets/admin/plugins/summernote/summernote-bs4.css",
        "resources/assets/admin/plugins/ekko-lightbox/ekko-lightbox.css",
    ],
    "public/assets/admin/css/adminlte.css"
);

mix.scripts(
    [
        "resources/assets/admin/plugins/jquery/jquery.min.js",
        "resources/assets/admin/plugins/bootstrap/js/bootstrap.bundle.js",
        "resources/assets/admin/plugins/select2/js/select2.full.js",
        "resources/assets/admin/js/adminlte.js",
        "resources/assets/admin/js/demo.js",
        "resources/assets/admin/plugins/sweetalert2/sweetalert2.all.min.js",
        "resources/assets/admin/plugins/bs-custom-file-input/bs-custom-file-input.min.js",
        "resources/assets/admin/plugins/datatables/jquery.dataTables.js",
        "resources/assets/admin/plugins/datatables-bs4/js/dataTables.bootstrap4.js",
        "resources/assets/admin/plugins/datatables-responsive/js/dataTables.responsive.js",
        "resources/assets/admin/plugins/datatables-responsive/js/responsive.bootstrap4.js",
        "resources/assets/admin/plugins/datatables-select/js/dataTables.select.js",
        "resources/assets/admin/plugins/datatables-select/js/select.bootstrap4.js",
        "resources/assets/admin/plugins/moment/moment-with-locales.js",
        "resources/assets/admin/plugins/summernote/summernote-bs4.js",
        "resources/assets/admin/plugins/ekko-lightbox/ekko-lightbox.js",
        "resources/assets/admin/plugins/filterizr/jquery.filterizr.min.js",
    ],
    "public/assets/admin/js/adminlte.js"
);

mix.copyDirectory(
    "resources/assets/admin/js/custom",
    "public/assets/admin/js/custom"
);

mix.copyDirectory(
    "resources/assets/admin/plugins/fontawesome-free/webfonts",
    "public/assets/admin/webfonts"
);

mix.copyDirectory(
    "resources/assets/admin/plugins/summernote/font",
    "public/assets/admin/css/font"
);

mix.copyDirectory("resources/assets/admin/img", "public/assets/admin/img");

mix.copy(
    "resources/assets/admin/css/adminlte.css.map",
    "public/assets/admin/css/adminlte.css.map"
);

mix.copy(
    "resources/assets/admin/js/adminlte.js.map",
    "public/assets/admin/js/adminlte.js.map"
);

mix.styles(
    [
        "resources/assets/front/css/bootstrap.css",
        "resources/assets/front/css/font-awesome.min.css",
        "resources/assets/front/css/animate.css",
        "resources/assets/front/css/responsive.css",
        "resources/assets/front/css/colors.css",
        "resources/assets/front/css/style.css",
        "resources/assets/front/css/version/marketing.css",
        "resources/assets/front/css/version/custom.css",
        "resources/assets/admin/plugins/datatables-bs4/css/dataTables.bootstrap4.css",
        "resources/assets/admin/plugins/datatables-responsive/css/responsive.bootstrap4.css",
        "resources/assets/admin/plugins/datatables-select/css/select.bootstrap4.css",
        "resources/assets/admin/plugins/datatables-buttons/css/buttons.bootstrap4.css",
        "resources/assets/admin/plugins/summernote/summernote-bs4.css",
        "resources/assets/admin/plugins/dropzone/dropzone.css",
    ],
    "public/assets/front/css/main.css"
);

mix.scripts(
    [
        "resources/assets/front/js/jquery.min.js",
        "resources/assets/front/js/tether.min.js",
        "resources/assets/admin/plugins/bootstrap/js/bootstrap.bundle.js",
        "resources/assets/front/js/animate.js",
        "resources/assets/front/js/custom.js",
        "resources/assets/admin/plugins/sweetalert2/sweetalert2.all.min.js",
        "resources/assets/admin/plugins/datatables/jquery.dataTables.js",
        "resources/assets/admin/plugins/datatables-bs4/js/dataTables.bootstrap4.js",
        "resources/assets/admin/plugins/datatables-responsive/js/dataTables.responsive.js",
        "resources/assets/admin/plugins/datatables-responsive/js/responsive.bootstrap4.js",
        "resources/assets/admin/plugins/datatables-select/js/dataTables.select.js",
        "resources/assets/admin/plugins/summernote/summernote-bs4.js",
        "resources/assets/admin/plugins/dropzone/dropzone.js",
        "resources/assets/admin/plugins/jquery.inputmask.js",
    ],
    "public/assets/front/js/main.js"
);

mix.copyDirectory(
    "resources/assets/front/js/custom",
    "public/assets/front/js/custom"
);

mix.copyDirectory("resources/assets/front/fonts", "public/assets/front/fonts");

mix.copyDirectory(
    "resources/assets/front/images",
    "public/assets/front/images"
);
mix.copyDirectory(
    "resources/assets/front/upload",
    "public/assets/front/upload"
);

mix.copyDirectory(
    "resources/assets/admin/plugins/datatables-locale",
    "public/assets/locale/datatable"
);

mix.copyDirectory(
    "resources/assets/admin/plugins/summernote/font",
    "public/assets/front/css/font"
);

// mix.js('resources/js/app.js', 'public/assets/auth/js')
//     .sass('resources/sass/app.scss', 'public/assets/auth/css')
//     .sourceMaps();

// npm install
// npm run dev
