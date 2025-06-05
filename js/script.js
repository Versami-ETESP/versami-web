// Função para seguir/deixar de seguir
function seguirUsuario(usuarioId, botao) {
  const estaSeguindo = botao.classList.contains("following");
  const textoOriginal = botao.textContent;

  // Feedback visual imediato
  botao.disabled = true;
  botao.innerHTML = `<i class="fas fa-spinner fa-spin"></i> <span class="button-text">Processando...</span>`;

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
        botao.innerHTML = `<i class="fas fa-<span class="math-inline">\{data\.action \=\=\= "follow" ? 'user\-minus' \: 'user\-plus'\}"\></i\> <span class\="button\-text"\></span>{data.action === "follow" ? 'Deixar de seguir' : 'Seguir'}</span>`;

        // Atualiza a contagem de seguidores/seguindo (se houver uma função)
        if (typeof atualizarContadores === "function") {
          atualizarContadores();
        }
      } else {
        alert(data.message || "Erro ao processar");
        botao.innerHTML = `<i class="fas fa-<span class="math-inline">\{estaSeguindo ? 'user\-plus' \: 'user\-minus'\}"\></i\> <span class\="button\-text"\></span>{textoOriginal}</span>`; // Reverte o texto e ícone
      }
    })
    .catch((error) => {
      console.error("Erro:", error);
      alert("Falha na conexão");
      botao.innerHTML = `<i class="fas fa-<span class="math-inline">\{estaSeguindo ? 'user\-plus' \: 'user\-minus'\}"\></i\> <span class\="button\-text"\></span>{textoOriginal}</span>`; // Reverte o texto e ícone
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

    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = "block";
    };

    reader.readAsDataURL(file);
  }
}

// Variável para armazenar todos os livros
let allBooks = [];

// Função para abrir o popup de seleção de livros
async function openBookSelection() {
  try {
    document.getElementById("bookSelectionPopup").style.display = "flex";
    document.getElementById("bookSearch").value = "";

    // Mostra loading enquanto carrega
    showBookLoading(true);

    const response = await fetch("get_books.php");
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.error || "Erro ao carregar livros");
    }

    // Armazena todos os livros para filtragem
    allBooks = result.data || [];
    renderBooks(allBooks);
  } catch (error) {
    console.error("Erro:", error);
    showBookError("Erro ao carregar livros: " + error.message);
  } finally {
    showBookLoading(false);
  }
}

// Função para renderizar os livros
function renderBooks(books) {
  const booksList = document.getElementById("booksList");

  if (books.length === 0) {
    booksList.innerHTML = `
            <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 20px;">
                <i class="fa-solid fa-book-open" style="font-size: 2em; color: #ccc;"></i>
                <p>Nenhum livro encontrado</p>
            </div>
        `;
    return;
  }

  booksList.innerHTML = "";

  books.forEach((book) => {
    const bookElement = document.createElement("div");
    bookElement.className = "book-item";
    bookElement.innerHTML = `
          <div class="book">
            <div class="attach-btn" data-book-id="<span class="math-inline">\{book\.idLivro\}"
data\-book\-title\="</span>{book.nomeLivro}"
                    data-book-author="<span class="math-inline">\{book\.nomeAutor \|\| ""\}"
data\-book\-cover\="</span>{book.imagem_base64 || ""}">
                <div class="book-cover">
                    ${
                      book.imagem_base64
                        ? `<img src="data:image/jpeg;base64,${book.imagem_base64}" alt="${book.nomeLivro}">`
                        : `<div class="no-cover"><i class="fa-solid fa-book"></i></div>`
                    }
                </div>
                <div class="book-details">
                    <h3 class="book-name"><span class="math-inline">\{book\.nomeLivro\}</h3\>
<p class\="author"\></span>{
                      book.nomeAutor || "Autor desconhecido"
                    }</p>
                </div>
            </div>
          </div>
        `;
    booksList.appendChild(bookElement);
  });

  // Adiciona eventos aos botões de anexar
  document.querySelectorAll(".attach-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const bookId = this.getAttribute("data-book-id");
      const bookTitle = this.getAttribute("data-book-title");
      const bookAuthor = this.getAttribute("data-book-author");
      const bookCover = this.getAttribute("data-book-cover");

      selectBook(bookId, bookTitle, bookAuthor, bookCover);
    });
  });
}

// Evento de pesquisa em tempo real
document.getElementById("bookSearch").addEventListener("input", function () {
  filterBooks(this.value);
});

function filterBooks(searchTerm) {
  if (!searchTerm) {
    renderBooks(allBooks);
    return;
  }

  const term = searchTerm.toLowerCase();
  const filtered = allBooks.filter((book) => {
    return (
      book.nomeLivro.toLowerCase().includes(term) ||
      (book.nomeAutor && book.nomeAutor.toLowerCase().includes(term)) ||
      (book.genero && book.genero.toLowerCase().includes(term))
    );
  });

  renderBooks(filtered);
}

// Função para selecionar um livro
function selectBook(bookId, title, author, coverImage) {
  const container = document.getElementById("selectedBookContainer");

  // Atualiza os dados do livro
  document.getElementById("selectedBookId").value = bookId;

  // Atualiza a capa do livro
  const bookCover = document.getElementById("selectedBookCover");
  if (coverImage) {
    bookCover.innerHTML = `<img src="data:image/jpeg;base64,${coverImage}" style="width:100%;height:100%;object-fit:cover;">`;
  } else {
    bookCover.innerHTML =
      '<i class="fa-solid fa-book" style="color:#ccc;"></i>';
  }

  // Atualiza as informações do livro
  document.getElementById("selectedBookInfo").innerHTML = `
        <strong><span class="math-inline">\{title\}</strong\>
<p\></span>{author || "Autor desconhecido"}</p>
    `;

  // Mostra o botão de remoção
  document.getElementById("removeBookBtn").style.display = "block";

  // Fecha o popup de seleção
  closeBookSelection();
}

// Função para remover o livro selecionado
function removeSelectedBook() {
  const container = document.getElementById("selectedBookContainer");
  const bookCover = document.getElementById("selectedBookCover");
  const bookInfo = document.getElementById("selectedBookInfo");

  // Limpa os dados
  document.getElementById("selectedBookId").value = "";
  bookInfo.innerHTML = "";
  bookCover.innerHTML = '<i class="fa-solid fa-book"></i>';

  // Esconde o botão de remoção
  document.getElementById("removeBookBtn").style.display = "none";

  // Mantém o container visível e clicável
  container.style.display = "flex";
  container.onclick = function () {
    openBookSelection();
  };
}

// Evento de clique no botão de remoção
document
  .getElementById("removeBookBtn")
  .addEventListener("click", function (e) {
    e.stopPropagation(); // Impede que o evento se propague para o container
    removeSelectedBook();
  });

// Evento de clique no container do livro (para selecionar novo)
document
  .getElementById("selectedBookContainer")
  .addEventListener("click", function () {
    // Só abre a seleção se não tiver livro selecionado
    if (!document.getElementById("selectedBookId").value) {
      openBookSelection();
    }
  });

// Função para atualizar o estado visual do container
function updateBookContainerState() {
  const container = document.getElementById("selectedBookContainer");
  const hasBook = document.getElementById("selectedBookId").value !== "";

  if (hasBook) {
    container.classList.add("has-book");
    document.getElementById("removeBookBtn").style.display = "block";
  } else {
    container.classList.remove("has-book");
    document.getElementById("removeBookBtn").style.display = "none";
    document.getElementById("selectedBookCover").innerHTML =
      '<i class="fa-solid fa-book"></i>';
    document.getElementById("selectedBookInfo").innerHTML = "";
  }
}

// Chamar esta função sempre que o estado mudar
updateBookContainerState();

// Funções auxiliares
function showBookLoading(show) {
  const loadingElement = document.getElementById("booksLoading");
  if (show) {
    if (!loadingElement) {
      const loader = document.createElement("div");
      loader.id = "booksLoading";
      loader.className = "loading-indicator";
      loader.innerHTML =
        '<div class="spinner"></div><p>Carregando livros...</p>';
      document.getElementById("booksList").appendChild(loader);
    }
  } else if (loadingElement) {
    loadingElement.remove();
  }
}

function showBookError(message) {
  const booksList = document.getElementById("booksList");
  booksList.innerHTML = `
        <div class="error-message">
            <i class="fa-solid fa-exclamation-triangle"></i>
            <p>${message || "Erro ao carregar livros"}</p>
            <button onclick="openBookSelection()">Tentar novamente</button>
        </div>
    `;
}

function closeBookSelection() {
  document.getElementById("bookSelectionPopup").style.display = "none";
}

// Evento de clique no ícone do livro
document
  .getElementById("selectBookBtn")
  .addEventListener("click", openBookSelection);

// Evento para remover livro selecionado
document.getElementById("removeBookBtn").addEventListener("click", function () {
  document.getElementById("selectedBookContainer").style.display = "none";
  document.getElementById("selectedBookId").value = "";
});

// Adicione este código junto com as outras funções de popup

// Função para fechar o popup de seleção de livros ao clicar no overlay
document
  .getElementById("bookSelectionPopup")
  .addEventListener("click", function (event) {
    if (event.target === this) {
      closeBookSelection();
    }
  });

// Mantenha a função closeBookSelection existente
function closeBookSelection() {
  document.getElementById("bookSelectionPopup").style.display = "none";
}

// Variável global para armazenar o ID do post a ser denunciado/excluído
let currentPostId = null;

// Função para controlar o menu de 3 pontinhos (tooltip)
function togglePostMenu(event, buttonElement) {
    // Para parar a propagação do evento de clique, evitando que o clique no botão
    // também acione o clique na div do post (que geralmente redireciona)
    event.stopPropagation();

    const dropdown = buttonElement.nextElementSibling; // Pega o dropdown que é o próximo irmão do botão
    // Removemos a linha const postId = buttonElement.closest('.post').dataset.postId;
    // pois o postId já é passado como segundo argumento em feed.php
    
    // Fecha qualquer outro dropdown aberto
    document.querySelectorAll('.post-menu-dropdown.active').forEach(openDropdown => {
        if (openDropdown !== dropdown) { // Se não for o dropdown que estamos clicando agora
            openDropdown.classList.remove('active');
        }
    });

    // Alterna a classe 'active' para mostrar/esconder o dropdown clicado
    dropdown.classList.toggle('active');
}

// Fecha o dropdown quando se clica em qualquer lugar do documento, fora do menu
document.addEventListener('click', function(event) {
    // Verifica se o clique não foi dentro de um '.post-menu'
    if (!event.target.closest('.post-menu')) {
        document.querySelectorAll('.post-menu-dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }
});


// Funções para os botões do tooltip
function denunciarPost(postId) {
    currentPostId = postId; // Garante que o postId correto está setado
    document.getElementById('confirmationModalOverlay').classList.add('active'); // Abre o modal de confirmação
    // Fecha o dropdown após a ação
    document.querySelectorAll('.post-menu-dropdown.active').forEach(dropdown => {
        dropdown.classList.remove('active');
    });
}

function excluirPost(postId) {
    alert('Excluir post ' + postId + ' clicado!');
    // Fecha o dropdown após a ação
    document.querySelectorAll('.post-menu-dropdown.active').forEach(dropdown => {
        dropdown.classList.remove('active');
    });
}

// Lógica de confirmação de denúncia no modal
document.getElementById('confirmDenounceBtn').addEventListener('click', function() {
    if (currentPostId) {
        // Envia a denúncia via AJAX
        fetch('denunciar_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `post_id=${currentPostId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Ou uma mensagem mais amigável/modal de sucesso
            } else {
                alert(data.error || data.message); // Exibe erro do servidor
            }
        })
        .catch(error => {
            console.error('Erro ao denunciar post:', error);
            alert('Erro ao conectar com o servidor para denunciar.');
        })
        .finally(() => {
            document.getElementById('confirmationModalOverlay').classList.remove('active'); // Fecha o modal
            currentPostId = null; // Limpa o ID do post
        });
    }
});

document.getElementById('cancelDenounceBtn').addEventListener('click', function() {
    document.getElementById('confirmationModalOverlay').classList.remove('active'); // Fecha o modal
    currentPostId = null; // Limpa o ID do post
});

// Fechar o modal de confirmação clicando no overlay
document.getElementById('confirmationModalOverlay').addEventListener('click', function(event) {
    if (event.target === this) {
        this.classList.remove('active');
        currentPostId = null;
    }
});