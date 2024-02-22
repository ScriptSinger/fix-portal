$(document).ready(function () {
    function upload(path, size, name) {
        var dropzone = this;
        var btn = $("#submit");
        btn.on("click", function (e) {
            e.preventDefault();
            // Запускаем процесс загрузки файлов
            dropzone.processQueue();
        });

        // var mockFile = { name: name, size: size };

        // myDropzone.displayExistingFile(
        //     mockFile,
        //     path // Используем переданный путь к файлу
        // );

        dropzone.on("success", function (file, response) {
            console.log("Файл успешно загружен!", response);
        });
        dropzone.on("error", function (file, errorMessage) {
            console.error("Ошибка при загрузке файла:", errorMessage);
        });

        $.ajax({
            url: $("#upload-form").data("routes").show, // Замените на ваш маршрут
            method: "GET",
            success: function (response) {
                // Передаем значения size и name в функцию upload()
                upload(response.path, response.size, response.name);
            },
            error: function (error) {
                console.error("Ошибка при получении данных:", error);
            },
        });
    }

    Dropzone.options.uploadForm = {
        method: "post",
        url: $("#upload-form").data("routes").upload,
        paramName: "file", // The name that will be used to transfer the file
        maxFilesize: 2,
        maxFiles: 1,
        dictDefaultMessage: "Перетащите файл сюда или нажмите для выбора",
        uploadMultiple: false,
        autoProcessQueue: false,
        addRemoveLinks: true,
        init: upload,
    };
});
