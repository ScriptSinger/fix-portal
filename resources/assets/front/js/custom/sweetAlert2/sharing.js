document.querySelectorAll(".copyLink").forEach((link) => {
    link.addEventListener("click", function (event) {
        const url = this.getAttribute("data-url");
        copyToClipboard(url);
        event.preventDefault();
    });
});

function copyToClipboard(text) {
    navigator.clipboard
        .writeText(text)
        .then(() => {
            showNotification("success", "Ссылка скопирована в буфер обмена");
        })
        .catch((err) => {
            console.error("Ошибка копирования: ", err);
            showNotification("error", "Ошибка копирования ссылки");
        });
}

function showNotification(type, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        },
    });
    Toast.fire({
        icon: type,
        title: type === "success" ? "Успешно!" : "Ошибка!",
        text: message,
    });
}
