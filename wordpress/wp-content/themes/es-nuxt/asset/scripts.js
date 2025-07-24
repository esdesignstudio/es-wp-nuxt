
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll("#categorychecklist input[type=checkbox]");
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener("change", function () {
            if (this.checked) {
                checkboxes.forEach(function(box) {
                    if (box !== checkbox) {
                        box.checked = false;
                    }
                });
            }
        });
    });
});