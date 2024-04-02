$(document).ready(function () {
    let token = $('meta[name="csrf-token"]').attr("content");
    let routes = $("#upload-form").data("routes");

    Dropzone.options.uploadForm = {
        autoProcessQueue: false,
        method: "POST",
        url: routes.upload,
        paramName: "avatar",
        maxFilesize: 2,
        maxFiles: 1,
        dictDefaultMessage: "Перетащите файл сюда или нажмите для выбора",
        uploadMultiple: false,
        autoProcessQueue: false,
        addRemoveLinks: true,
        acceptedFiles: "image/jpeg,image/png,image/jpg",

        init: function () {
            var dz = this;
            var btn = $("#submit");
            btn.on("click", function (e) {
                e.preventDefault();
                dz.processQueue();
            });

            $.ajax({
                url: routes.show,
                method: "GET",
                success: function (response) {
                    if (response) {
                        let mockFile = {
                            name: response.name,
                            size: response.size,
                        };
                        dz.files.push(mockFile);
                        dz.displayExistingFile(mockFile, response.uri);
                    }
                },
                error: function (error) {
                    console.error("Ошибка при получении данных:", error);
                },
            });

            this.on("addedfile", function (file) {
                if (dz.files.length > 1 && file) {
                    dz.removeFile(dz.files[0]);
                }
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
