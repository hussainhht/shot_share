
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('image-input');
    const preview = document.getElementById('image-preview');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            preview.style.display = 'none';
            preview.src = '#';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
});
