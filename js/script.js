// Função para seguir/deixar de seguir
function seguirUsuario(usuarioId, botao) {
  const estaSeguindo = botao.classList.contains("following");
  const textoOriginal = botao.textContent;

  // Feedback visual imediato
  botao.disabled = true;
  botao.textContent = "Processando...";

  fetch("seguir.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `seguido_id=${usuarioId}`,
  })
    .then((response) => {
      if (!response.ok) throw new Error("Erro na rede");
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        // Atualiza o botão
        botao.classList.toggle("following");
        botao.textContent =
          data.action === "follow" ? "Deixar de seguir" : "Seguir";

        // Atualiza a contagem de seguidores/seguindo
        if (typeof atualizarContadores === "function") {
          atualizarContadores();
        }
      } else {
        alert(data.message || "Erro ao processar");
        botao.textContent = textoOriginal;
      }
    })
    .catch((error) => {
      console.error("Erro:", error);
      alert("Falha na conexão");
      botao.textContent = textoOriginal;
    })
    .finally(() => {
      botao.disabled = false;
    });
}

// Função para curtir/descurtir
function curtir(postId, botao) {
  const icon = botao.querySelector("i");
  const count = botao.querySelector(".like-count");
  const isLiked = icon.classList.contains("fas"); // Verifica se já está curtido

  // Feedback visual imediato
  if (isLiked) {
    icon.classList.replace("fas", "far"); // Muda para não curtido visualmente
    botao.classList.remove("liked");
    if (count) count.textContent = parseInt(count.textContent) - 1;
  } else {
    icon.classList.replace("far", "fas"); // Muda para curtido visualmente
    botao.classList.add("liked");
    if (count) count.textContent = parseInt(count.textContent) + 1;
  }

  // Envia a requisição AJAX
  fetch("curtir.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `post_id=${postId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        // Reverte visualmente se falhar
        if (isLiked) {
          icon.classList.replace("far", "fas");
          botao.classList.add("liked");
          if (count) count.textContent = parseInt(count.textContent) + 1;
        } else {
          icon.classList.replace("fas", "far");
          botao.classList.remove("liked");
          if (count) count.textContent = parseInt(count.textContent) - 1;
        }
      } else if (count) {
        count.textContent = data.total_likes; // Atualiza com o valor correto do servidor
      }
    })
    .catch((error) => {
      console.error("Erro:", error);
      // Reverte visualmente em caso de erro
      if (isLiked) {
        icon.classList.replace("far", "fas");
        botao.classList.add("liked");
        if (count) count.textContent = parseInt(count.textContent) + 1;
      } else {
        icon.classList.replace("fas", "far");
        botao.classList.remove("liked");
        if (count) count.textContent = parseInt(count.textContent) - 1;
      }
    });
}

// Função para curtir comentários
function curtirComentario(comentarioId, elemento) {
  const icon = elemento.querySelector("i");
  const count = elemento.querySelector(".like-comment-count");
  const isLiked = icon.classList.contains("fas"); // Verifica classe 'fas' (curtido)
  console.log("Icon classes:", icon.classList);
  // Feedback visual imediato
  if (isLiked) {
    icon.classList.replace("fas", "far"); // Muda para vazio
    elemento.classList.remove("likedComment");
    if (count) count.textContent = parseInt(count.textContent) - 1;
  } else {
    icon.classList.replace("far", "fas"); // Muda para preenchido
    elemento.classList.add("likedComment");
    if (count) count.textContent = parseInt(count.textContent) + 1;
  }

  // Envia a requisição AJAX
  fetch("curtir_comentario.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `comentario_id=${comentarioId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (!data.success) {
        // Reverte visualmente se falhar
        if (isLiked) {
          icon.classList.replace("far", "fas");
          elemento.classList.add("likedComment");
          if (count) count.textContent = parseInt(count.textContent) + 1;
        } else {
          icon.classList.replace("fas", "far");
          elemento.classList.remove("likedComment");
          if (count) count.textContent = parseInt(count.textContent) - 1;
        }
      }
    })
    .catch((error) => {
      console.error("Erro:", error);
      // Reverte visualmente
      if (isLiked) {
        icon.classList.replace("far", "fas");
        elemento.classList.add("likedComment");
        if (count) count.textContent = parseInt(count.textContent) + 1;
      } else {
        icon.classList.replace("fas", "far");
        elemento.classList.remove("likedComment");
        if (count) count.textContent = parseInt(count.textContent) - 1;
      }
    });
}

// Sistema de Busca (mantido igual)
$(document).ready(function () {
  $("#buscar_usuario").on("input", function () {
    // ... (código original mantido)
  });
});

// Sistema de Exclusão de Post (ajustado para idPublicacao)
$(document).on("click", ".delete-post", function () {
  if (confirm("Tem certeza que deseja excluir esta publicação?")) {
    const idPublicacao = $(this).data("id");

    $.ajax({
      url: "excluir_post.php",
      method: "POST",
      data: { idPublicacao: idPublicacao },
      success: function () {
        location.reload();
      },
    });
  }
});

// Função para abrir e fechar popup

const btnOpen = document.querySelector(".button");
const popupOverlay = document.querySelector(".popup-overlay");
const btnClose = document.querySelector(".btn-close");

function abrirModalPopUp() {
  btnOpen.addEventListener("click", () => {
    popupOverlay.style.display = "flex";
  });

  btnClose.addEventListener("click", () => {
    popupOverlay.style.display = "none";
  });

  popupOverlay.addEventListener("click", (event) => {
    if (event.target === popupOverlay) {
      popupOverlay.style.display = "none";
    }
  });
}

// Função para tabelas (Feed, Seguindo, Reviews e Livros Favoritos)

function changeTab(index) {
  let tabs = document.querySelectorAll(".tab");
  let contents = document.querySelectorAll(".contentPosts");

  tabs.forEach((tab) => tab.classList.remove("active"));
  contents.forEach((content) => content.classList.remove("active"));

  tabs[index].classList.add("active");
  contents[index].classList.add("active");
}

// Função para enviar posts sem duplicar
function submitPost() {
  const formData = new FormData(document.getElementById("postForm"));

  fetch("feed.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((data) => {
      // Atualiza a lista de posts sem recarregar a página
      location.reload();
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

// Função para buscar usuarios no Explorar

$(document).ready(function () {
  $("#buscar_usuario").on("input", function () {
    $.ajax({
      url: "buscar.php",
      type: "GET",
      data: { buscar_usuario: $(this).val() },
      success: function (response) {
        $("#resultados").html(response);
      },
    });
  });
});

// Função para clicar no post e exibir o detalhamento
document.querySelectorAll(".back-arrow").forEach((arrow) => {
  arrow.addEventListener("click", function (e) {
    e.preventDefault();
    const href = this.getAttribute("href");

    sessionStorage.setItem("feedScrollPosition", window.scrollY);

    window.location.href = href;
  });
});

window.addEventListener("DOMContentLoaded", function () {
  if (window.location.pathname.includes("feed.php")) {
    const scrollPosition = sessionStorage.getItem("feedScrollPosition");
    if (scrollPosition) {
      window.scrollTo(0, parseInt(scrollPosition));
      sessionStorage.removeItem("feedScrollPosition");
    }

    if (performance.navigation.type === 2) {
      setTimeout(() => {
        window.location.reload();
      }, 100);
    }
  }
});

// Função do Blog carregar a primeira noticia

function loadNews(postId) {
  $.ajax({
    url: "get_blog_post.php",
    type: "GET",
    data: { id: postId },
    success: function (response) {
      $("#newsFull").html(response);
      $(".news-card").removeClass("active");
      $(`.news-card[onclick="loadNews(${postId})"]`).addClass("active");
    },
    error: function () {
      $("#newsFull").html(
        '<div class="error-message">Erro ao carregar a notícia</div>'
      );
    },
  });
}

// Função para expandir a imagem
function expandImage() {
  document.getElementById("overlay").style.visibility = "visible";
  document.getElementById("overlay").style.opacity = "1";
}

// Função para fechar a imagem expandida
function closeImage(event) {
  if (
    event.target.id === "overlay" ||
    event.target.classList.contains("close-btn")
  ) {
    document.getElementById("overlay").style.opacity = "0";
    setTimeout(() => {
      document.getElementById("overlay").style.visibility = "hidden";
    }, 300);
  }
}

// Função para exibir a imagem de pré-visualização

function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  const file = input.files[0];
  
  if (file) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
          preview.src = e.target.result;
          preview.style.display = 'block';
      }
      
      reader.readAsDataURL(file);
  }
}