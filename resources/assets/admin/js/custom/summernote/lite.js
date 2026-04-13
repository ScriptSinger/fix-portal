$(document).ready(function () {
    $("#description, .lite").summernote({
        height: 150,
        toolbar: [
            ["style", ["bold", "italic", "underline", "clear"]],
            ["font", ["strikethrough", "superscript", "subscript"]],
            ["para", ["ul", "ol"]],
            ["insert", ["link"]],
            ["view", ["fullscreen"]],
            ["misc", ["codeview"]],
        ],
    });
});
