document.getElementById('image-upload').addEventListener('change', function(e) {
    const fileInput = e.target;
    const fileName = document.getElementById('file-name');
    const preview = document.getElementById('image-preview');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileName.textContent = file.name;
        fileName.classList.add('has-file');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
    } else {
        fileName.textContent = 'Aucun fichier sélectionné';
        fileName.classList.remove('has-file');
        preview.src = '';
        preview.classList.remove('has-image');
    }
});