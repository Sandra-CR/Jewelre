let slides = document.querySelectorAll(".slide");
let slideIndex = 0;
let intervalId = null;

document.addEventListener("DOMContentLoaded", initializeSlider);

function initializeSlider() {
    if (slides.length > 0) {
        slides[slideIndex].classList.add("displaySlide");
        intervalId = setInterval(nextSlide, 6500);
        updateDots();
        updateTransform();
    }
}

function plusSlides(n) {
    clearInterval(intervalId);
    slideIndex += n;
    if (slideIndex >= slides.length) slideIndex = 0;
    if (slideIndex < 0) slideIndex = slides.length - 1;
    updateSlides();
}

function currentSlide(n) {
    clearInterval(intervalId);
    slideIndex = n - 1;
    updateSlides();
}

function nextSlide() {
    slideIndex = (slideIndex + 1) % slides.length;
    updateSlides();
}

function updateSlides() {
    updateDots();
    updateTransform();
}

function updateDots() {
    let dots = document.querySelectorAll(".dot");
    dots.forEach(dot => dot.classList.remove("active"));
    if (dots[slideIndex]) dots[slideIndex].classList.add("active");
}

function updateTransform() {
    let slideWidth = document.querySelector(".slider").clientWidth;
    document.querySelector(".slides").style.transform = `translateX(${-slideIndex * slideWidth}px)`;
}
