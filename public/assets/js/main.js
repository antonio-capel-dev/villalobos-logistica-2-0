// Main JS for interactions
document.addEventListener("DOMContentLoaded", () => {
  console.log("Villalobos Logística 2.0 Frontend is Ready");

  // Mobile menu toggle functionality
  const mobileToggle = document.querySelector(".mobile-menu-toggle");
  const mainNav = document.querySelector(".main-nav");

  if (mobileToggle && mainNav) {
    mobileToggle.addEventListener("click", () => {
      mainNav.classList.toggle("active");
    });
  }
});
