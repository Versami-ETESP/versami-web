$(document).ready(function(){

    /*
     Evento durante o submit formulario de cadastro.
     Ele ira enviar os dados do formulario para o servidor, que fará as validações e retorna se concluiu ou não.
     Se não ocorrer cadastro será exibido um alert com a mensagem explicando o erro.
    */
    $("#form").submit(function (event){

        event.preventDefault();
    
        let usrNome = $("#nome").val().trim();
        let usrLogin =  $("#login").val().trim();
        let usrEmail = $("#email").val().trim();
        let usrNasc = $("#nasc").val().trim();
        let usrSenha = $("#senha").val().trim();
        let usrConfirma = $("#confirma").val().trim();
        let tipo = "#submit";

    
            $.ajax({
                method:"POST",
                url:"../../Index.php",
                data:{
                    usrNome:usrNome, usrLogin: usrLogin, usrEmail: usrEmail, usrSenha: usrSenha, usrNasc: usrNasc, usrConfirma: usrConfirma, tipo: tipo
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

    /* 
     os eventos a seguir fazem uma verificação no input dos usuarios nos campos
     com os IDs informados e chamam a função 'validaCampos()'
    */

    $("#nome").on("input", function(){
        console.log("acionei o campo texto");
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

    /**
     * O evento a seguir verifica o campo com id confirma e faz uma requisição ajax
     * Essa requisição solicita a verificação se os campos senha e confirma senha são iguais
     * se o retorno do PHP for false irá exibir mensagem para o usuario
     */ 

    $("#confirma").on("input", function(){

        let usrSenha = $("#senha").val().trim();
        let usrConfirma = $("#confirma").val().trim();
        let tipo = "#confirma";


        $.ajax({
            type:"POST",
            url:"../../Index.php",
            data:{senha: usrSenha, confirma: usrConfirma, tipo: tipo},
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

    /*
    * O proximo evento faz uma solicitação ajax para o php verificar se o usuario está disponivel.
    * Se houver o retorno false, ira aparecer a mensagem personalizada para o usuario escolher outro usuario
    */

    $("#login").on("input", function(){
        let usrLogin = $(this).val().trim();
        let tipo = "#user";

        $.ajax({
            type:"POST",
            url:"../../Index.php",
            data:{login: usrLogin, tipo: tipo},
            datatype:'json',
            success: function(resposta){
                resposta = JSON.parse(resposta);
                console.log(resposta);
                if (!resposta){
                    erro("#login","Nome de usuário já cadastrado!");
                } else {
                    $("#login").css({border: ""});
                    $("#alerta").text("Usuario disponível").css({color:"green", fontSize:"12px", visibility:"visible"});
                }
            },
            error: function(cod,textStatus,msg){
                erro("","Erro de comunicação com o servidor");
            }
        })
    });

}); // fim da função ready

/**
 * função 'valida campos' que faz um requisição via ajax para o php, que verifica os dados do cliente. Apos o retorno, se a resposta for false, 
 * a função mostra uma mensagem personalizada para verificar o campo em especifico
 */
function validaCampos(usrInput, conteudo, msg){
    let tipo = "#valida";
    $.ajax({
        type:"POST",
        url:"../../Index.php",
        data:{usrInput: usrInput, conteudo: conteudo, tipo: tipo}, 
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
