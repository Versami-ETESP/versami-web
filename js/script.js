// Add this event listener to the existing script.js file
document.addEventListener("DOMContentLoaded", function () {
  // Sidebar toggle logic
  const sidebar = document.getElementById("sidebar");
  const menuBtn = document.getElementById("menuBtn"); // This targets the new button
  const overlay = document.getElementById("overlay"); // This targets the new overlay
  const content = document.querySelector(".content"); // Select the main content area

  // Check if all elements exist before adding listeners
  if (menuBtn && sidebar && overlay && content) {
    menuBtn.addEventListener("click", function () {
      sidebar.classList.toggle("active");
      overlay.classList.toggle("active");
      // Optional: Adjust main content margin, if needed, when sidebar opens
      // content.style.marginLeft = sidebar.classList.contains("active") ? "250px" : "0";
    });

    overlay.addEventListener("click", function () {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
      // content.style.marginLeft = "0";
    });
  }


  const postForm = document.getElementById("postForm");
  if (postForm) {
    postForm.addEventListener("submit", function (event) {
      event.preventDefault(); // Prevent default form submission
      submitPost(); // Call the AJAX submission function
    });
  }

  // Existing DOMContentLoaded listeners should be here too
  // Função para adicionar e remover a classe 'changed' para o label do input file
  const fileInputs = document.querySelectorAll('input[type="file"]');
  fileInputs.forEach((input) => {
    const label = input.previousElementSibling; // O label "Selecionar Imagem"

    input.addEventListener("change", function () {
      if (this.files && this.files[0]) {
        label.innerHTML = `<i class="fa-solid fa-check"></i> ${this.files[0].name}`;
        label.classList.add("changed"); // Adiciona uma classe para estilização de "selecionado"
      } else {
        label.innerHTML =
          '<i class="fa-solid fa-upload"></i> Selecionar Imagem';
        label.classList.remove("changed");
      }
    });
  });

  // Add event listeners for the confirmation modal buttons on DOMContentLoaded (from Profile.php logic)
  const confirmBtn = document.getElementById("confirmDenounceBtn");
  const cancelBtn = document.getElementById("cancelDenounceBtn");
  const confirmationModalOverlay = document.getElementById(
    "confirmationModalOverlay"
  );

  if (confirmBtn) {
    confirmBtn.addEventListener("click", confirmarDenuncia);
  }
  if (cancelBtn) {
    cancelBtn.addEventListener("click", cancelarDenuncia);
  }
  if (confirmationModalOverlay) {
    confirmationModalOverlay.addEventListener("click", function (event) {
      if (event.target === confirmationModalOverlay) {
        cancelarDenuncia();
      }
    });
  }

  // Listener para fechar o popup de seleção de livros ao clicar no overlay
  const bookSelectionPopup = document.getElementById("bookSelectionPopup");
  if (bookSelectionPopup) {
    bookSelectionPopup.addEventListener("click", function (event) {
      if (event.target === this) {
        closeBookSelection();
      }
    });
  }

  // NEW: Blog Post Modal related event listeners
  const blogPostModalOverlay = document.getElementById("blogPostModalOverlay");
  const blogModalCloseBtn = document.getElementById("blogModalCloseBtn");

  if (blogPostModalOverlay) {
      // Close when clicking the 'X' button
      if (blogModalCloseBtn) {
          blogModalCloseBtn.addEventListener('click', closeBlogPostModal);
      }
      // Close when clicking outside the modal content
      blogPostModalOverlay.addEventListener('click', function(event) {
          if (event.target === blogPostModalOverlay) {
              closeBlogPostModal();
          }
      });
  }
});

// NEW: Function to open the blog post modal
function openBlogPostModal() {
    const blogPostModalOverlay = document.getElementById("blogPostModalOverlay");
    if (blogPostModalOverlay) {
        blogPostModalOverlay.classList.add('active'); // Add 'active' class to show
        // Disable body scrolling when modal is open
        document.body.style.overflow = 'hidden';
    }
}

// NEW: Function to close the blog post modal
function closeBlogPostModal() {
    const blogPostModalOverlay = document.getElementById("blogPostModalOverlay");
    const blogModalBody = document.getElementById("blogModalBody");
    if (blogPostModalOverlay) {
        blogPostModalOverlay.classList.remove('active'); // Remove 'active' class to hide
        blogModalBody.innerHTML = ''; // Clear content when closing
        // Enable body scrolling when modal is closed
        document.body.style.overflow = '';
    }
}


// Função para seguir/deixar de seguir
function seguirUsuario(usuarioId, botao) {
  const estaSeguindo = botao.classList.contains("following");
  const textoOriginal = botao.textContent;

  // Feedback visual imediato
  botao.disabled = true;
  botao.innerHTML = `<i class="fas fa-spinner fa-spin"></i> <span class="button-text">Processando...</span>`;

  fetch("../Functions/seguir.php", {
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
        botao.innerHTML = `<i class="fas fa-${
          data.action === "follow" ? "user-minus" : "user-plus"
        }"></i> <span class="button-text">${
          data.action === "follow" ? "Deixar de seguir" : "Seguir"
        }</span>`;

        // Atualiza a contagem de seguidores/seguindo (se houver uma função)
        if (typeof atualizarContadores === "function") {
          atualizarContadores();
        }
      } else {
        alert(data.message || "Erro ao processar");
        botao.innerHTML = `<i class="fas fa-${
          estaSeguindo ? "user-plus" : "user-minus"
        }"></i> <span class="button-text">${textoOriginal}</span>`; // Reverte o texto e ícone
      }
    })
    .catch((error) => {
      console.error("Erro:", error);
      alert("Falha na conexão");
      botao.innerHTML = `<i class="fas fa-${
        estaSeguindo ? "user-plus" : "user-minus"
      }"></i> <span class="button-text">${textoOriginal}</span>`; // Reverte o texto e ícone
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
  fetch("../Functions/curtir.php", {
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
  fetch("../Functions/curtir_comentario.php", { // Corrected path
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

// Função para abrir e fechar popup (general review/post creation popup)
const btnOpen = document.querySelector(".button");
const popupOverlay = document.querySelector(".popup-overlay");
const btnClose = document.querySelector(".btn-close");

function abrirModalPopUp() {
  // Ensure elements exist
  if (btnOpen && popupOverlay && btnClose) {
      popupOverlay.style.display = "flex";
  }
}

if (btnClose) {
    btnClose.addEventListener("click", () => {
        if (popupOverlay) popupOverlay.style.display = "none";
    });
}

if (popupOverlay) {
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

// Função para enviar posts (AGORA VIA AJAX)
function submitPost() {
  const formData = new FormData(document.getElementById("postForm"));
  const reviewPopupOverlay = document.getElementById("reviewPopupOverlay"); // Obtenha a referência para o popup

  fetch("../Feed/Feed.php", {
    // Caminho para o script que processa o post
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest", // Identifica a requisição como AJAX
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json(); // Espera uma resposta JSON
    })
    .then((data) => {
      if (data.success) {
        // 1. Fecha o popup de review
        if (reviewPopupOverlay) {
          reviewPopupOverlay.style.display = "none";
        }
        // 2. Exibe o toast de confirmação
        showToast(data.message || "Postagem criada com sucesso!", "success");

        // 3. Recarrega a página CONDICIONALMENTE (apenas se for feed.php)
        // Usa includes para ser flexível com o caminho base (ex: /versami-web/Feed/Feed.php)
        if (
          window.location.pathname.includes("/Feed/Feed.php") ||
          window.location.pathname.includes("/feed.php")
        ) {
          // Recarrega após um pequeno atraso para que o toast seja visível
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          // Para outras páginas, limpa o formulário e remove o livro anexado (boa prática)
          document.getElementById("postForm").reset();
          removeSelectedBook(); // Garante que o livro anexado também seja limpo
        }
      } else {
        showToast(data.message || "Erro ao criar postagem.", "error");
      }
    })
    .catch((error) => {
      console.error("Erro na requisição AJAX:", error);
      showToast("Falha na conexão ou erro do servidor.", "error");
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

// Função do Blog carregar a noticia (now loads into a modal on mobile)
function loadBlogPost(postId) { // Renamed from loadNews to loadBlogPost for consistency with usage in BlogUsuarios.php
    const blogModalBody = document.getElementById("blogModalBody");
    const blogPostModalOverlay = document.getElementById("blogPostModalOverlay");

    if (!blogModalBody || !blogPostModalOverlay) {
        console.error("Blog modal elements not found!");
        // Fallback or more robust error handling
        showToast("Erro ao carregar a notícia: Componentes do modal não encontrados.", "error");
        return;
    }

    // Show a loading indicator in the modal body immediately
    blogModalBody.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><h2>Carregando Notícia...</h2><p>Por favor, aguarde.</p></div>';
    openBlogPostModal(); // Open the modal immediately with loading indicator

    $.ajax({
        url: 'get_blog_post_content.php', // Corrected path
        method: 'GET',
        data: { id: postId },
        success: function(response) {
            blogModalBody.innerHTML = response; // Load content into modal body
            // Highlight the selected post in the list (on the main page behind the modal)
            $('.post-preview').removeClass('active-post');
            $(`.post-preview[data-post-id="${postId}"]`).addClass('active-post');
        },
        error: function(xhr, status, error) {
            console.error("Erro ao carregar post:", status, error, xhr.responseText);
            blogModalBody.innerHTML = '<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><h2>Erro ao carregar notícia</h2><p>Não foi possível carregar o conteúdo da notícia. Tente novamente.</p></div>';
        }
    });
}


// Função para expandir a imagem (from general image overlay)
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

    const response = await fetch("../Functions/get_books.php");
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
            <div class="attach-btn" data-book-id="${book.idLivro}"
                    data-book-title="${book.nomeLivro}"
                    data-book-author="${book.nomeAutor || ""}"
                    data-book-cover="${book.imagem_base64 || ""}">
                <div class="book-cover">
                    ${
                      book.imagem_base64
                        ? `<img src="data:image/jpeg;base64,${book.imagem_base64}" alt="${book.nomeLivro}">`
                        : `<div class="no-cover"><i class="fa-solid fa-book"></i></div>`
                    }
                </div>
                <div class="book-details">
                    <h3 class="book-name">${book.nomeLivro}</h3>
                    <p class="author">${
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
        <strong>${title}</strong>
        <p>${author || "Autor desconhecido"}</p>
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

// Function to show a toast notification
function showToast(message, type = "success") {
  const toast = document.getElementById("toastNotification");
  toast.className = "toast-notification show " + type; // Add 'show' and type class
  toast.textContent = message;

  // After 3 seconds, remove the show class
  setTimeout(function () {
    toast.className = toast.className.replace("show", "");
  }, 3000);
}

// Function to show/hide the post menu dropdown
function togglePostMenu(event, button) {
  event.stopPropagation(); // Prevent the click from propagating to the document body immediately

  const dropdown = button.nextElementSibling; // Get the next sibling, which is the dropdown

  // Close all other open dropdowns
  document
    .querySelectorAll(".post-menu-dropdown.show")
    .forEach((openDropdown) => {
      if (openDropdown !== dropdown) {
        openDropdown.classList.remove("show");
      }
    });

  // Toggle the 'show' class on the clicked dropdown
  dropdown.classList.toggle("show");
}

// Close the dropdown menu when clicking anywhere outside of it
document.addEventListener("click", function (event) {
  if (!event.target.closest(".post-menu")) {
    document
      .querySelectorAll(".post-menu-dropdown.show")
      .forEach((dropdown) => {
        dropdown.classList.remove("show");
      });
  }
});

// Function to handle reporting a post (now triggers a confirmation modal)
function denunciarPost(postId) {
  // Hide all currently open post menu dropdowns
  document.querySelectorAll(".post-menu-dropdown.show").forEach((dropdown) => {
    dropdown.classList.remove("show");
  });

  // Display the confirmation modal
  const confirmationModalOverlay = document.getElementById(
    "confirmationModalOverlay"
  );
  confirmationModalOverlay.classList.add("active"); // Use 'active' class for display control

  // Store the postId on the modal for later use when confirming
  confirmationModalOverlay.setAttribute("data-post-id", postId);
}

// Function to confirm and process the denunciation after modal confirmation
function confirmarDenuncia() {
  const confirmationModalOverlay = document.getElementById(
    "confirmationModalOverlay"
  );
  const postIdToReport = confirmationModalOverlay.getAttribute("data-post-id");

  // Hide the confirmation modal
  confirmationModalOverlay.classList.remove("active");
  confirmationModalOverlay.removeAttribute("data-post-id"); // Clean up the stored ID

  // If a valid postId is available, proceed with the AJAX request
  if (postIdToReport) {
    $.ajax({
      url: "denunciar_post.php",
      method: "POST",
      data: { post_id: postIdToReport },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showToast(response.message, "success"); // Show success toast
        } else {
          showToast(response.error || "Erro ao denunciar o post.", "error"); // Show error toast
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        showToast(
          "Ocorreu um erro ao tentar denunciar a publicação. Tente novamente.",
          "error"
        ); // Show error toast
      },
    });
  }
}

// Function to cancel the denunciation (just closes the modal)
function cancelarDenuncia() {
  const confirmationModalOverlay = document.getElementById(
    "confirmationModalOverlay"
  );
  confirmationModalOverlay.classList.remove("active");
  confirmationModalOverlay.removeAttribute("data-post-id"); // Clean up the stored ID
}

// Function to delete a post
function excluirPost(postId) {
  // Esconde o dropdown
  document.querySelectorAll(".post-menu-dropdown").forEach((dropdown) => {
    dropdown.classList.remove("show");
  });

  if (confirm("Tem certeza que deseja excluir esta publicação?")) {
    $.ajax({
      url: "excluir_post.php", // Seu script PHP para excluir
      method: "POST",
      data: { idPublicacao: postId },
      dataType: "json", // Espera uma resposta JSON
      success: function (response) {
        if (response.success) {
          alert("Publicação excluída com sucesso!"); // You might want to change this to a toast as well
          window.location.href = "../Feed/Feed.php"; // Redireciona para o feed após a exclusão
        } else {
          alert(response.error || "Erro ao excluir a publicação."); // You might want to change this to a toast as well
        }
      },
      error: function (xhr, status, error) {
        console.error("Erro na requisição AJAX:", status, error);
        alert(
          "Ocorreu um erro ao tentar excluir a publicação. Tente novamente."
        ); // You might want to change this to a toast as well
      },
    });
  }
}

// Add event listeners for the confirmation modal buttons on DOMContentLoaded
document.addEventListener("DOMContentLoaded", function () {
  const confirmBtn = document.getElementById("confirmDenounceBtn");
  const cancelBtn = document.getElementById("cancelDenounceBtn");
  const confirmationModalOverlay = document.getElementById(
    "confirmationModalOverlay"
  );

  if (confirmBtn) {
    confirmBtn.addEventListener("click", confirmarDenuncia);
  }
  if (cancelBtn) {
    // Directly call cancelarDenuncia on click
    cancelBtn.addEventListener("click", cancelarDenuncia);
  }
  // Close modal if clicking on the overlay itself
  if (confirmationModalOverlay) {
    confirmationModalOverlay.addEventListener("click", function (event) {
      // Ensure the click target is the overlay itself, not its children
      if (event.target === confirmationModalOverlay) {
        cancelarDenuncia();
      }
    });
  }
});

function showProfileTab(tabName) {
  // Remove 'active' de todas as abas e seções de conteúdo
  document
    .querySelectorAll(".profile-tab")
    .forEach((tab) => tab.classList.remove("active"));
  document
    .querySelectorAll(".profile-posts-section, .favorite-books-section")
    .forEach((section) => section.classList.remove("active"));

  // Adiciona 'active' à aba clicada e à seção de conteúdo correspondente
  if (tabName === "posts") {
    document
      .querySelector(".profile-tabs .profile-tab:nth-child(1)")
      .classList.add("active");
    document.getElementById("profile-posts-section").classList.add("active");
  } else if (tabName === "favorites") {
    document
      .querySelector(".profile-tabs .profile-tab:nth-child(2)")
      .classList.add("active");
    document
      .getElementById("profile-favorites-section")
      .classList.add("active");
  }
}

function toggleFavorite(button, bookId) { // Renomeado de toggleBookFavorite para toggleFavorite
    const isFavorited = button.classList.contains('favorited');
    const icon = button.querySelector('i');

    // Atualiza visualmente imediatamente
    button.classList.toggle('favorited');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');

    if (isFavorited) {
        button.style.backgroundColor = '';
        icon.style.color = '';
    } else {
        button.style.backgroundColor = '#ffebee';
        icon.style.color = '#e0245e';

        button.style.transform = 'scale(1.1)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 300);
    }

    // Chamada AJAX para o backend
    fetch('../Functions/toggle_favorite.php', {
        method: 'POST',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `book_id=${bookId}&action=${isFavorited ? 'remove' : 'add'}`,
    })
    .then((response) => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.statusText}`);
        }
        return response.json();
    })
    .then((data) => {
        if (!data.success) {
            console.error('Erro ao favoritar/desfavoritar livro:', data.error || 'Erro desconhecido');
            // Reverte as alterações visuais se a operação no servidor falhou
            button.classList.toggle('favorited');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            if (isFavorited) {
                 button.style.backgroundColor = '#ffebee';
                 icon.style.color = '#e0245e';
            } else {
                 button.style.backgroundColor = '';
                 icon.style.color = '';
            }
        }
    })
    .catch((error) => {
        console.error('Falha na requisição AJAX:', error);
        // Reverte as alterações visuais em caso de erro de rede
        button.classList.toggle('favorited');
        icon.classList.toggle('far');
        icon.classList.toggle('fas');
        if (isFavorited) {
             button.style.backgroundColor = '#ffebee';
             icon.style.color = '#e0245e';
        } else {
             button.style.backgroundColor = '';
             icon.style.color = '';
        }
    });
} 