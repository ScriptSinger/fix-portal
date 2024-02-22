$(document).ready(function () {
    let token = $('meta[name="csrf-token"]').attr("content");
    let routes = $("#upload-form").data("routes");

    Dropzone.options.uploadForm = {
        method: "POST",
        url: routes.upload,
        paramName: "file",
        maxFilesize: 2,
        maxFiles: 1,
        dictDefaultMessage: "Перетащите файл сюда или нажмите для выбора",
        uploadMultiple: false,
        autoProcessQueue: false,
        addRemoveLinks: true,
        acceptedFiles: "image/jpeg,image/png,image/jpg,image/gif",

        init: function () {
            var dz = this;
            var btn = $("#submit");
            btn.on("click", function (e) {
                e.preventDefault();
                dz.processQueue();
            });

            this.on("success", function (file, response) {
                // console.log("Файл успешно загружен!", response);
            });

            this.on("error", function (file, errorMessage) {
                console.error("Ошибка при загрузке файла:", errorMessage);
            });

            this.on("addedfile", function (file) {
                if (dz.files.length > 1 && file) {
                    // Если добавлено более одного файла, удалите предыдущее превью
                    dz.removeFile(dz.files[0]);
                }
            });

            $.ajax({
                url: routes.show,
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": token,
                },
                success: function (response) {
                    if (response) {
                        let mockFile = {
                            name: response.name,
                            size: response.size,
                        };
                        dz.displayExistingFile(mockFile, response.path);
                    }
                },
                error: function (error) {
                    console.error("Ошибка при получении данных:", error);
                },
            });

            this.on("removedfile", (file) => {
                $.ajax({
                    url: routes.destroy,
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": token,
                    },
                    success: function (response) {
                        console.log("Файл успешно удален!", response);
                    },
                    error: function (error) {
                        console.error("Ошибка при удалении файла:", error);
                    },
                });
            });
        },
    };
});
