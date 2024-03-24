document.addEventListener("DOMContentLoaded", function () {
    var registerButton = document.getElementById("registerButton");
    var agreeTermsCheckbox = document.getElementById("agreeTerms");

    function checkCheckbox() {
        if (agreeTermsCheckbox.checked) {
            registerButton.disabled = false;
        } else {
            registerButton.disabled = true;
        }
    }
    checkCheckbox();
    agreeTermsCheckbox.addEventListener("change", checkCheckbox);
});
