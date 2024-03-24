Dropzone.autoDiscover = false;
$(document).ready(function () {
    let token = $('meta[name="csrf-token"]').attr("content");
    let routes = $("#dz").data("routes");

    var dropzone = new Dropzone("#dz", {
        headers: {
            "X-CSRF-TOKEN": token,
        },
        method: "POST",
        url: routes.upload, // URL-адрес, куда будут загружаться файлы
        paramName: "file", // Имя параметра для передачи файла на сервер
        maxFilesize: 5, // Максимальный размер файла в мегабайтах
        maxFiles: 5, // Максимальное количество файлов
        acceptedFiles: ".jpg,.png,.gif", // Разрешенные типы файлов
        autoProcessQueue: false, // Отключение автоматической загрузки файлов

        init: function () {
            if (routes.show) {
                var thisDz = this;
                $.ajax({
                    url: routes.show,
                    method: "GET",
                    dataType: "json", // Ожидаемый тип данных в ответе (может быть 'json', 'xml', 'html' и т. д.)
                    success: function (response) {
                        let mockFile = {
                            name: response.name,
                            size: response.size,
                        };
                        thisDz.displayExistingFile(mockFile, response.url);
                    },
                    error: function (xhr, status, error) {
                        if (xhr.status === 500) {
                            // Проверяем статус ответа
                            console.error("Error fetching file data:", error);
                        } else {
                            console.log(
                                "Thumbnail not found, skipping display."
                            );
                        }
                    },
                });
            }
        },
    });

    $("#send").click(function (e) {
        e.preventDefault();
        dropzone.processQueue(); // Запустить загрузку файлов, если она не была запущена автоматически
    });
});
