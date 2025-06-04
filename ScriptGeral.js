document.addEventListener("DOMContentLoaded", function() {
    let profileInput = document.getElementById("profilePic");
    let coverInput = document.getElementById("coverPic");
    
    if (profileInput) {
        profileInput.addEventListener("change", function(event) {
            let file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profilePreview").src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (coverInput) {
        coverInput.addEventListener("change", function(event) {
            let file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("coverPreview").src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});

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

function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
}

function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
}


function mostrarImagemSelecionada() {
    var input = document.getElementById('inputImagem');
    var file = input.files[0];
    var previewDiv = document.getElementById('previewDiv');
    var previewImg = document.getElementById('previewImg');

    if (file) {
        previewDiv.style.display = 'flex';

        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

document.getElementById("postForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);
    
    fetch("create_post.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("postMessage").innerHTML = "<p style='color: green;'>Post criado com sucesso!</p>";
            document.getElementById("postForm").reset();
            location.reload();
        } else {
            document.getElementById("postMessage").innerHTML = "<p style='color: red;'>" + data.error + "</p>";
        }
    })
    .catch(error => console.error("Erro ao postar:", error));
});