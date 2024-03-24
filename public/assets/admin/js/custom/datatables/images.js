$(document).ready(function () {
    var routes = $("#dataTable").data("routes");
    function formatDate(data, type) {
        var formattedDate = moment(data).format("DD.MM.YYYY HH:mm:ss");
        return type === "display" ? formattedDate : data;
    }

    function getSizeInMb(data) {
        if (typeof data === "number") {
            var sizeInMB = data / (1024 * 1024);
            return sizeInMB.toFixed(2) + " MB";
        } else {
            return data;
        }
    }

    var dataTable = $("#dataTable").DataTable({
        responsive: true,
        stateSave: true,
        select: true,
        language: {
            url: $("#dataTable").data("locale"),
        },
        processing: true,
        ajax: {
            url: routes.index,
            dataSrc: "",
        },
        columns: [
            {
                data: "id",
                title: "ID",
            },
            {
                data: null,
                title: "Account Images",
                render: function (data, type, row, meta) {
                    if (row.user && row.user.email) {
                        return `<a href="${routes.show.replace(
                            ":id",
                            row.user_id
                        )}">${row.user.email}</a>`;
                    } else {
                        return "admin";
                    }
                },
            },
            {
                data: "url",
                title: "Thumbnail",
                render: function (data, type, row, meta) {
                    return `<a href="${data}" data-toggle="lightbox" data-title="${
                        row.url
                    }" data-footer="${row.mime} ${getSizeInMb(
                        row.size
                    )}"><img class="direct-chat-img" src="${data}" class="img-fluid"></a>`;
                },
            },
            {
                data: "mime",
                title: "Mime",
            },
            {
                data: "size",
                title: "Size",
                render: function (data, type, row, meta) {
                    return getSizeInMb(data);
                },
            },
            {
                data: "created_at",
                title: "Created At",
                render: function (data, type, row) {
                    return formatDate(data, type);
                },
            },
            {
                data: "updated_at",
                title: "Updated At",
                render: function (data, type, row) {
                    return formatDate(data, type);
                },
            },
            {
                data: "deleted_at",
                title: "Deleted At",
                render: function (data, type, row) {
                    return formatDate(data, type);
                },
            },
            {
                data: null,
                title: "Action",
                render: function (data, type, row) {
                    if (data.deleted_at) {
                        return `<button class="btn btn-primary btn-restore" data-row-id="${row.id}"><i class="fas fa-trash-restore" aria-hidden="true"></i></button>`;
                    } else {
                        return `<button class="btn btn-danger btn-delete" data-row-id="${row.id}"><i class="fas fa-trash"></i></button>`;
                    }
                },
            },
        ],

        // rowCallback: function (row, data, index) {
        //     if (data.deleted_at) {
        //         $("td:eq(1)", row).wrapInner("<s>");
        //     }
        // },
    });

    $("#dataTable").on(
        "click",
        "button.btn-restore, button.btn-delete",
        function (event) {
            event.stopPropagation();
            var token = $('meta[name="csrf-token"]').attr("content");
            var id = $(this).data("row-id");
            var actionUrl = $(this).hasClass("btn-restore")
                ? routes.restore.replace(":id", id)
                : routes.destroy.replace(":id", id);

            $.ajax({
                url: actionUrl,
                method: $(this).hasClass("btn-restore") ? "PUT" : "DELETE",
                headers: {
                    "X-CSRF-TOKEN": token,
                },
                success: function (response) {
                    dataTable.ajax.reload(null, false);
                },
                error: function (error) {
                    console.error(
                        $(this).hasClass("btn-restore")
                            ? "Error restoring user:"
                            : "Error deleting user:",
                        error
                    );
                },
            });
        }
    );
});
