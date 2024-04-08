$(document).ready(function () {
    $(".title__link").on("click", function (e) {
        e.preventDefault(); // Предотвращаем стандартное действие ссылки

        var target = $(this).data("scroll"); // Получаем значение атрибута data-scroll
        var $target = $(target);

        // Прокручиваем страницу к цели (якорю) с плавной анимацией за время 800 миллисекунд
        $("html, body").animate(
            {
                scrollTop: $target.offset().top,
            },
            800
        );
    });
});
