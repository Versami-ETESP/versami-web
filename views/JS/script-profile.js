let res = "";  // variavel que vai ser usada para formar a string que forma as postagens
let qtd = 0; // variavel que guarda qual a quantide de posts ja carregada
const limite = 2; // constante que define sempre 5 posts para cada chamada da função

let nomeUser = sessionStorage.getItem("usuarioNome");
let arrobaUser = sessionStorage.getItem("usuarioArroba");
let fotoPerfil = sessionStorage.getItem("usuarioPerfil");
let fotoCapa = sessionStorage.getItem("usuarioCapa");

$(document).ready(function(){
   
    $("#userNome").text(nomeUser);
    $("#userArroba").text(arrobaUser);

    
    buscarPosts(true); // aciona a função 'buscarPosts' assim que a pagina carrega.

    /*
        Realiza o envio de uma publicação para o servidor, para ser salvo no banco de dados.
        Não permite o envio de posts vazios.
    */


    $("#publicarPost").click( function(){
        let conteudo = $("#publicacao").val();
        let tipo = "#public";
    
        $.ajax({
            url: "../../Index.php",
            type:"POST",
            data:{conteudo: conteudo, tipo: tipo},
            success: function(resultado){
                resultado = JSON.parse(resultado);
                console.log(resultado);
                if(resultado){
                    location.reload();
                }else{
                    alert("Escreva uma publicação antes de enviar!");
                } 
            },
            error: function(){
                alert('Erro ao publicar. Tente novamente mais tarde!')
            }
        })
    });
    
}); 

/*
    Aciona a função 'buscarPosts' quando o usuario chega no limite do scroll da pagina
*/
$(window).on("scroll", function() {
    if ($(window).scrollTop() + $(window).height() + 5 > $(document).height()) {
        buscarPosts();
    }
}); 

/*
 * 'buscarPosts' passa para o php os limites qtd de postagens e recebe as 
 * postagens em forma de vetor, cria uma div e insere o conteudo desse post na div
 */

function buscarPosts(inicial = false){ 

    let tipo = "#busca";

    $.ajax({
        url: "../../Index.php",
        type:"POST",
        data: {limite: limite, inicio: qtd, tipo: tipo},
        success: function(resultado){
            console.log(resultado);
            let posts = JSON.parse(resultado);
            console.log(posts);
            
            if(inicial && (!posts || posts === "false")){
                res = "<div class='post'>Nenhum post localizado</div>"; // cria uma div informando que o user nao tem postagens vinculadas ao perfil
                $("#posts").append(res);
            } else if (Array.isArray(posts) && posts.length > 0) {
                posts.forEach(post => { res += 
                    `<div class='post'>
                        <div class='containerpost'>
                        <div class='icon-perf'><img src='../../Uploads/Perfil/padrao.png' id='fotoPerfil'/></div>
                        <span class='nomePost'>${nomeUser}</span>
                        <span class='datapost'>Publicado em: ${post.dataPublic}</span>
                        </div>
                        <div class='conteudo'>${post.conteudo}</div>
                    </div>`; 
                }); // crias as postagens dentro da div
                $("#posts").append(res);
                res = "";
                qtd += limite;
            } else {
                console.log("A variável 'posts' não é um array ou está vazia."); 
	            if (inicial) { 
		            let res = "<div class='post'>Nenhum post localizado</div>";
  		            $("#posts").append(res); 
	            }
            }
        },
        error: function(){
            alert("Erro ao carregar os posts.");
        }
    });

} // 

function abrirModal(){
    const modal = $("#janela-modal");
    modal.addClass('abrir');

    $("#janela-modal").on('click', function(e){
        if ($(e.target).is('#fechar') || $(e.target).is('#janela-modal') || $(e.target).closest('#fechar').length > 0) { 
            modal.removeClass('abrir');
        }
    });
}

function menuShow() {
    
    if ($('.cabecalho').hasClass('open')) {
        $('.cabecalho').removeClass('open');
        $(".icon").html("menu");
        $("body").css("overflow", "");
    } else {
        $('.cabecalho').addClass('open');
        $(".icon").text("close");
        $("body").css("overflow", "hidden");
    }
}
