$(document).ready(function(){
    /*
    * O evento faz um requisção via ajax, para verificar se o user e senha existem no BD.
    * Se ele retornar true, salva algumas informações do usuario no sessionStorage e depois redireciona para a pagina do perfil
    */
    $("#form2").submit(function(event){

        event.preventDefault();

        let usrLogin =  $("#login2").val().trim();
        let usrSenha = $("#senha2").val().trim();
        let tipo = "#login";

                $.ajax({
                    method:"POST",
                    url:"../../Index.php",
                    data:{
                        login: usrLogin, senha: usrSenha, tipo: tipo
                    },
                    datatype:'json',
                    beforeSend: function (){
                        $("#carregando").css({display:"block"});
                    },
                    success: function (retorno){
                        retorno = JSON.parse(retorno);
                        console.log(retorno);
                        if (retorno[0] == true) {
                            sessionStorage.setItem("usuarioNome",retorno[1]);
                            sessionStorage.setItem("usuarioArroba",retorno[2]);
                            sessionStorage.setItem("usuarioPerfil",retorno[3]);
                            sessionStorage.setItem("usuarioCapa",retorno[4]);
                            window.location.href = "../HTML/profile.html";
                        } else {
                            erro("",retorno[1]);
                        }                       
                    },
                    error: function (cod,textStatus,msg){
                        erro("","Erro de comunicação com o servidor");
                    },
                    complete: function (){
                        $("#carregando").css({display:"none"});
                    }
                });
        });    

}); // fim da função ready


function erro(campo, msg) {
    $(campo).css({border: "2px solid red"});
    $("#alerta").text(msg).css({color:"red", fontSize:"12px", visibility:"visible"});
}

function semErro(campo) {
    $(campo).css({border: ""});
    $("#alerta").text("").css({visibility: "hidden"});
}

function limpaCampos (){

    $("#nome").val("");
    $("#login").val("");
    $("#email").val("");
    $("#nasc").val("");
    $("#senha").val("");
    $("#confirma").val("");

}
