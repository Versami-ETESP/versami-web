$(document).ready(function(){

    $("#form2").submit(function(event){

        event.preventDefault();

        let usrLogin =  $("#login2").val().trim();
        let usrSenha = $("#senha2").val().trim();

                $.ajax({
                    method:"POST",
                    url:"../PHP/login-user.php",
                    data:{
                        login: usrLogin, senha: usrSenha
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
                            window.location.href = "../../Imagem-User/HTML/ImageProfile.php";
                            console.log("logado!");
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

    const usuarioNome = sessionStorage.getItem("usuarioNome");
    if (usuarioNome) {
        $("#msgTitulo").text(`${usuarioNome}, o nosso site está em construção!`);
    }

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
