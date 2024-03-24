$(function () {
    $("#content").summernote({
        height: 150,
        callbacks: {
            onImageUpload: function (files) {
                upload($(this), files[0]);
            },
            onMediaDelete: function ($target) {
                destroy($(this), $target.attr("data-id"));
            },
        },
    });

    function upload(instance, file) {
        let url = instance.data("routes").upload;
        let token = $('meta[name="csrf-token"]').attr("content");
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: url,
            headers: {
                "X-CSRF-TOKEN": token,
            },
            method: "POST",
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {
                instance.summernote(
                    "insertImage",
                    response.url,
                    function ($image) {
                        $image.addClass("img-fluid");
                        $image.attr("data-id", response.id);
                    }
                );
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function (key) {
                        alert(errors[key]);
                    });
                }
            },
        });
    }

    function destroy(instance, id) {
        let url = instance.data("routes").destroy.replace(":id", id);
        let token = $('meta[name="csrf-token"]').attr("content");
        $.ajax({
            method: "DELETE",
            url: url,
            headers: {
                "X-CSRF-TOKEN": token,
            },
            success: function (response) {
                alert(response.message);
            },
            error: function () {
                alert(response.message);
            },
        });
    }
});
