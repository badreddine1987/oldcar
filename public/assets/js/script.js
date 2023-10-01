"use strict"

const menu = document.querySelector('.menu');
const nav = document.querySelector('.nav_content')

menu.addEventListener('click', () => {
    menu.classList.toggle('active');
    nav.classList.toggle('active');
});

const lienSuivant = document.querySelector('a.page-link[rel="next"]');

// Ajoutez une classe à la balise <a>
lienSuivant.classList.add('page-active');