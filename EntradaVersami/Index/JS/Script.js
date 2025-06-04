// Navegação pela navbar
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", function () {
    document.querySelector(".nav-links a.active").classList.remove("active");
    this.classList.add("active");
  });
});

function BlogPage() {
  window.location.href = '../../Blogs/Blog/HTML/Blog.html';
}

function ContaPage() {
  window.location.href = '../../Login/HTML/login.html';
}
