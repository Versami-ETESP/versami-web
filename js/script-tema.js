// script.js
function trocarTema() {
  const sidebar = document.getElementById("sidebar");
  const principal = document.getElementById("principal-content");
  const comment = document.getElementById("comment");
  sidebar.classList.toggle("dark");
  sidebar.classList.toggle("light");
  principal.classList.toggle("dark");
  principal.classList.toggle("light");
  comment.classList.toggle("dark");
  comment.classList.toggle("light");

  // Armazenar a escolha no localStorage
  if (sidebar.classList.contains("dark")) {
    localStorage.setItem("tema", "dark");
  } else {
    localStorage.setItem("tema", "light");
  }
  if (principal.classList.contains("dark")) {
    localStorage.setItem("tema", "dark");
  } else {
    localStorage.setItem("tema", "light");
  }
  if (comment.classList.contains("dark")) {
    localStorage.setItem("tema", "dark");
  } else {
    localStorage.setItem("tema", "light");
  }
}

// Aplicar tema salvo ao carregar a página
window.onload = function () {
  const temaSalvo = localStorage.getItem("tema") || "light";
  document.getElementById("sidebar").className = temaSalvo;
  document.getElementById("principal-content").className = temaSalvo;
  document.getElementById("comment").className = temaSalvo;
};
