$(document).ready(function () {
    $("[data-route]").each(function () {
        let $widget = $(this); // Текущий виджет
        let $overlay = $widget.closest(".small-box").find(".overlay"); // Находим оверлей
        let route = $widget.data("route");
        let statisticKey = $widget.data("statistic-key");

        // Показываем оверлей перед отправкой запроса
        $overlay.show();

        $.ajax({
            url: route,
            method: "GET",
            dataType: "json",
            success: function (data) {
                $widget.text(data[statisticKey]);
                // Скрываем оверлей после успешного получения данных
                $overlay.hide();
            },
            error: function (xhr, status, error) {
                console.error("Error fetching statistics:", error);
                // Скрываем оверлей и в случае ошибки
                $overlay.hide();
            },
        });
    });
});
