$(document).ready(function () {
    // Добавление атрибута role="button" и обработчика события клика для изображений в блоке .blog-content
    $(".page-wrapper img")
        .attr("role", "button")
        .click(function () {
            var imgSrc = $(this).attr("src");
            $("#modalImage").attr("src", imgSrc);
            $("#imageModal").modal("show");
        });
});
