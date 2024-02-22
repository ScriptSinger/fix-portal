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
        info: false,
        processing: true,

        language: {
            url: $("#dataTable").data("locale"),
        },

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
                data: "name",
                title: "Имя",
            },
            {
                data: "location",
                title: "Город",
            },
        ],

        columnDefs: [
            {
                targets: 1,
                render: function (data, type, row, meta) {
                    return `<a href="${routes.show.replace(":id", row.id)}">${
                        row.name
                    }</a>`;
                },
            },
        ],

        rowCallback: function (row, data, index) {
            if (data.deleted_at) {
                $("td:eq(1)", row).wrapInner("<s>");
            }
        },
    });
});
