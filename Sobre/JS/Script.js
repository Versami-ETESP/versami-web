// Navegação pela navbar
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", function () {
    document.querySelector(".nav-links a.active").classList.remove("active");
    this.classList.add("active");
  });
});

function ContaPage() {
  window.location.href = "../../Login/Login.php";
}
