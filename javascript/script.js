const prevButton = document.querySelector('.prev');
const nextButton = document.querySelector('.next');
const carouselImages = document.querySelector('.carousel-images');
const images = document.querySelectorAll('.carousel-images img');

let index = 0;

function showImage(newIndex) {
    if (newIndex >= images.length) index = 0;
    else if (newIndex < 0) index = images.length - 1;
    else index = newIndex;

    const offset = -index * 100;
    carouselImages.style.transform = `translateX(${offset}%)`;
}

prevButton.addEventListener('click', () => showImage(index - 1));
nextButton.addEventListener('click', () => showImage(index + 1));

setInterval(() => showImage(index + 1), 5000);




