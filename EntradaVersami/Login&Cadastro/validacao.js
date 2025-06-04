$(document).ready(function(){

    $("#form").submit(function (event){

        event.preventDefault();
    
        usrNome = $("#nome").val().trim();
        usrLogin =  $("#login").val().trim();
        usrEmail = $("#email").val().trim();
        usrNasc = $("#nasc").val().trim();
        usrSenha = $("#senha").val().trim();
        usrConfirma = $("#confirma").val().trim();

    
            $.ajax({
                method:"POST",
                url:"Cadastro/cadastro.php",
                data:{
                    usrNome:usrNome, usrLogin: usrLogin, usrEmail: usrEmail, usrSenha: usrSenha, usrNasc: usrNasc, usrConfirma: usrConfirma
                },
                datatype:'json',
                beforeSend: function (){
                    $("#carregando").css({display:"block"});
                },
                success: function (retorno){
                    console.log(retorno);
                    retorno = JSON.parse(retorno);
                    alert(retorno);
                    //window.location.href = "/Login/HTML/login.html";
                },
                error: function (cod,textStatus,msg){
                    $("#alerta").text("Erro de conexão com o servidor").css({color:"red", fontSize:"12px", visibility:"visible"});
                },
                complete: function (){
                    limpaCampos();
                    $("#carregando").css({display:"none"});
                }
            }); // final da função ajax de enviar dados para o PHP

        
    }); //fim do evento submit

    
    $("#nome").on("input", function(){
        validaCampos("#nome",$(this).val().trim(),"O nome deve ter no mínimo 3 caracteres");
    });

    $("#email").on("input", function(){
        validaCampos("#email",$(this).val().trim(),"Digite um e-mail válido");
    });

    $("#senha").on("input", function(){
        validaCampos("#senha",$(this).val().trim(),"A senha deve ter 8 ou mais caracteres");
    });

    $("#nasc").on("input", function(){
        validaCampos("#nasc", $(this).val().trim(),"O usuário deve ser maior de 13 anos");
    });

    $("#confirma").on("input", function(){

        usrSenha = $("#senha").val().trim();
        usrConfirma = $("#confirma").val().trim();


        $.ajax({
            type:"POST",
            url:"valida-dados.php",
            data:{senha: usrSenha, confirma: usrConfirma},
            datatype:'json',
            success: function(resposta){
                resposta = JSON.parse(resposta);
                console.log(resposta);
                if (!resposta){
                    erro("#confirma","As senhas não coincidem, por favor, verifique");
                } else {
                    semErro("#confirma");
                }
            },
            error: function(cod,textStatus,msg){
                erro("","Erro de comunicação com o servidor");
            }
        });
    });

    $("#login").on("input", function(){
        usrLogin = $(this).val().trim();

        $.ajax({
            type:"POST",
            url:"valida-dados.php",
            data:{login: usrLogin},
            datatype:'json',
            success: function(resposta){
                resposta = JSON.parse(resposta);
                console.log(resposta);
                if (!resposta){
                    erro("#login","Nome de usuário já cadastrado!");
                } else {
                    semErro("#login");
                }
            },
            error: function(cod,textStatus,msg){
                erro("","Erro de comunicação com o servidor");
            }
        })
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

function validaCampos(usrInput, conteudo, msg){
    $.ajax({
        type:"POST",
        url:"valida-dados.php",
        data:{usrInput: usrInput, conteudo: conteudo},
        datatype:'json',
        success: function(resposta){
            resposta = JSON.parse(resposta);
            console.log(resposta);
            if (!resposta){
                erro(usrInput,msg);
            } else {
                semErro(usrInput);
            }
        },
        error: function(cod,textStatus,msg){
            erro("","Erro de comunicação com o servidor");
        }
    });
}

function limpaCampos (){

    $("#nome").val("");
    $("#login").val("");
    $("#email").val("");
    $("#nasc").val("");
    $("#senha").val("");
    $("#confirma").val("");

}
