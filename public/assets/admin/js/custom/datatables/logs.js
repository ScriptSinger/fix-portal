$(document).ready(function () {
    var routes = $("#dataTable").data("routes");

    function formatDate(data, type) {
        moment.locale("ru");
        var formattedDate = moment(data).fromNow();
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
                data: "created_at",
                title: "Time",
                render: function (data, type, row) {
                    return formatDate(data, type);
                },
            },
            {
                data: "user",
                title: "Пользователь",
                render: function (data, type, row) {
                    if (data && data.name) {
                        return `<a href="${routes.userEdit.replace(
                            ":id",
                            data.id
                        )}">${data.name}</a>`;
                    } else {
                        return "";
                    }
                },
            },

            {
                data: "methodType",
                title: "Метод",
            },
            {
                data: "referer",
                title: "Referer",
            },
            {
                data: "route",
                title: "Маршрут",
            },
            {
                data: "ipAddress",
                title: "IpAddress",
            },
            {
                data: "userAgent",
                title: "UserAgent",
            },
            {
                data: "locale",
                title: "Locale",
            },
        ],
    });
});
