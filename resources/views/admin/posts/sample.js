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
