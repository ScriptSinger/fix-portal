$(document).ready(function () {
    var routes = $("#dataTable").data("routes");
    function formatDate(data, type) {
        var formattedDate = moment(data).format("DD.MM.YYYY HH:mm:ss");
        return type === "display" ? formattedDate : data;
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
                data: "ip_address",
                title: "IP-address",
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
        columnDefs: [
            {
                targets: 1,
                render: function (data, type, row, meta) {
                    var route = routes.edit.replace(":id", row.id);
                    return `<a href="${route}">${row.ip_address}</a>`;
                },
            },
        ],
        rowCallback: function (row, data, index) {
            if (data.deleted_at) {
                $("td:eq(1)", row).wrapInner("<s>");
            }
        },
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
                            ? "Error restoring category:"
                            : "Error deleting category:",
                        error
                    );
                },
            });
        }
    );
});
