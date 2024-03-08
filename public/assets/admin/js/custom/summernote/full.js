$(function () {
    $("#content").summernote({
        height: 150,
        callbacks: {
            onImageUpload: function (files) {
                upload(files[0], $(this));
            },
            onMediaDelete: function ($target) {
                destroy($target.attr("src"), $(this));
            },
        },
    });

    function upload(file, instance) {
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
                instance.summernote("insertImage", response, function ($image) {
                    $image.addClass("img-fluid");
                });
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

    function destroy(src, instance) {
        let url = instance.data("routes").destroy;
        let token = $('meta[name="csrf-token"]').attr("content");
        $.ajax({
            method: "DELETE",
            url: url,
            headers: {
                "X-CSRF-TOKEN": token,
            },
            data: {
                src: src,
            },
            success: function (response) {
                console.log(response.message);
            },
            error: function () {
                console.log("Error uploading image");
            },
        });
    }
});
