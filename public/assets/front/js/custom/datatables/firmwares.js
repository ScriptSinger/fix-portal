$(function () {
    var routes = $("#dataTable").data("routes");
    var dataTable = $("#dataTable").DataTable({
        responsive: true,

        language: {
            url: $("#dataTable").data("locale"),
        },
        info: false,
        processing: true,
        pagingType: "numbers",

        ajax: {
            url: routes.index,
            dataSrc: "",
        },

        stateSave: true,
        select: true,

        columns: [
            {
                data: "id",
                title: "ID",
            },
            {
                data: "title",
                title: "Название",
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
                data: "crc32",
                title: "CRC32",
            },
            {
                data: "data",
                title: "Параметры",
            },
        ],

        columnDefs: [
            {
                targets: 1,
                render: function (data, type, row, meta) {
                    // Если тип события - отрисовка (render) и тип элемента - отображение (display)
                    if (type === "display") {
                        // Возвращаем HTML-код ссылки
                        return `<a href="${routes.show.replace(
                            ":id",
                            row.id
                        )}" class="btn-link">${row.title}</a>`;
                    } else {
                        // Возвращаем только текст (не ссылку) для других типов событий
                        return row.title;
                    }
                },
            },
            {
                targets: 2,
                render: function (data, type, row, meta) {
                    return data + " КБ";
                },
            },
            {
                targets: 7,
                render: function (data, type, row, meta) {
                    var truncatedText =
                        type === "display" && data && data.length > 100
                            ? data.substr(0, 100) + "..."
                            : data;
                    return truncatedText;
                },
            },
        ],

        rowCallback: function (row, data, index) {
            if (data.deleted_at) {
                $("td:eq(1)", row).wrapInner("<s>");
            }
        },
    });

    $("#dataTable").on("click", ".btn-link", function (event) {
        // Предотвращаем срабатывание события select при клике на ссылку
        event.stopPropagation();
    });
});
