$(function () {
    var routes = $("#dataTable").data("routes");
    var dataTable = $("#dataTable").DataTable({
        responsive: true,

        language: {
            url: $("#dataTable").data("locale"),
            search: "Поиск:",
            searchPlaceholder: "Название / модель / платформа / расширение / ID",
        },
        processing: true,
        serverSide: true,
        pagingType: "numbers",

        ajax: {
            url: routes.index,
            data: function (d) {
                var form = $("#firmwareFilters");
                if (form.length) {
                    d.platform = form.find('[name="platform"]').val();
                    d.extension = form.find('[name="extension"]').val();
                    d.crc32 = form.find('[name="crc32"]').val();
                }
            },
            dataSrc: "data",
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

    $("#firmwareFilters").on("submit", function (event) {
        event.preventDefault();
        dataTable.ajax.reload();
    });

    $("#resetFilters").on("click", function () {
        $("#firmwareFilters")[0].reset();
        if ($.fn.select2) {
            $("#filterPlatform").val("").trigger("change");
            $("#filterExtension").val("").trigger("change");
        }
        dataTable.ajax.reload();
    });

});
