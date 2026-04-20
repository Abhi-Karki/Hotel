'use strict';

/**
 * navbar toggle
 */

const overlay = document.querySelector("[data-overlay]");
const navOpenBtn = document.querySelector("[data-nav-open-btn]");
const navbar = document.querySelector("[data-navbar]");
const navCloseBtn = document.querySelector("[data-nav-close-btn]");
const navLinks = document.querySelectorAll("[data-nav-link]");

const navElemArr = [navOpenBtn, navCloseBtn, overlay].filter(Boolean);

const navToggleEvent = function (elem) {
  if (!navbar || !overlay || !elem.length) return;

  for (let i = 0; i < elem.length; i++) {
    elem[i].addEventListener("click", function () {
      navbar.classList.toggle("active");
      overlay.classList.toggle("active");
    });
  }
}

navToggleEvent(navElemArr);
if (navLinks.length) navToggleEvent(navLinks);



/**
 * header sticky & go to top
 */

const header = document.querySelector("[data-header]");
const goTopBtn = document.querySelector("[data-go-top]");

window.addEventListener("scroll", function () {
  if (!header) return;

  if (window.scrollY >= 200) {
    header.classList.add("active");
    if (goTopBtn) goTopBtn.classList.add("active");
  } else {
    header.classList.remove("active");
    if (goTopBtn) goTopBtn.classList.remove("active");
  }

});
