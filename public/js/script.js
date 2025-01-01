document.getElementById('imageForm').addEventListener('submit', function (e) {
    e.preventDefault();

    document.getElementById('spinner').style.display = 'inline-block';

    const url = document.getElementById('url').value;
    const text = document.getElementById('text').value;
    const minWidth = document.getElementById('minWidth').value;
    const minHeight = document.getElementById('minHeight').value;

    if (minWidth < 200 || minHeight < 200) {
        alert('Минимальная ширина и высота должны быть не менее 200px');
        return;
    }

    fetch('/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ url, text, minWidth, minHeight })
    })
        .then(response => response.json())
        .then(data => {
            document.getElementById('spinner').style.display = 'none';
            if (data.success) {
                loadImages();
            } else {
                alert('Ошибка при обработке запроса');
            }
        });
});

function loadImages() {
    fetch('/images')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('imagesContainer');
            container.innerHTML = '';
            data.images.forEach(image => {
                const imgElement = document.createElement('img');
                imgElement.src = image;
                container.appendChild(imgElement);
            });
        });
}

loadImages();
