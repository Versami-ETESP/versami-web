document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", function () {
    document.querySelector(".nav-links a.active").classList.remove("active");
    this.classList.add("active");
  });
});

function openPopup() {
  document.getElementById('overlay').style.display = 'flex';
}

function closePopup(event) {
  if (!event || event.target === document.getElementById('overlay') || event.target.classList.contains('btnClose')) {
      document.getElementById('overlay').style.display = 'none';
  }
}

function ContaPage() {
  window.location.href = '../../../Login/HTML/login.html';
}