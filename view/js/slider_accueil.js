/* -------------------- CAROUSEL INDEX.PHP -------------------- */

/* ---------- VARIABLES ---------- */
const slides = document.querySelectorAll(".slides img");
let slideIndex = 0;
let intervalId = null;


/* ---------- DOM > AFFICHAGE ---------- */
document.addEventListener("DOMContentLoaded", initializeSlider);


/* -------------------- LES FONCTIONS -------------------- */
function initializeSlider(){
    if(slides.length > 0){
        slides[slideIndex].classList.add("displaySlide");
        intervalId = setInterval(nextSlide, 6500);
    }
}

/* ---------- AFFICHER ---------- */
function showSlide(index){

    if(index >= slides.length){
        slideIndex = 0;
    }
    else if(index < 0) {
        slideIndex = slides.length - 1;
    }

    slides.forEach(slide => {
        slide.classList.remove("displaySlide");
    });
    slides[slideIndex].classList.add("displaySlide");
}

/* ---------- BOUTON < ---------- */
function prevSlide(){
    clearInterval(intervalId);
    slideIndex--;
    showSlide(slideIndex);
}

/* ---------- BOUTON > ---------- */
function nextSlide(){
    slideIndex++;
    showSlide(slideIndex);
}