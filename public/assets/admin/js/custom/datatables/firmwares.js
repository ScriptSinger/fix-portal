$(document).ready(function () {
    var routes = $("#dataTable").data("routes");

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
                data: "title",
                title: "Title",
            },
            {
                data: "size",
                title: "Размер",
            },
            {
                data: "date",
                title: "Дата",
                className: "text-nowrap",
            },
            {
                data: "extension",
                title: "Расширение",
            },
            {
                data: "platform",
                title: "Платформа",
            },
            {
                data: "data",
                title: "Платформа",
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
                    return `<a href="${routes.edit.replace(":id", row.id)}">${
                        row.title
                    }</a>`;
                },
            },
            {
                targets: 6,
                visible: false,
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
                            ? "Error restoring firmware"
                            : "Error deleting firmware:",
                        error
                    );
                },
            });
        }
    );
});
