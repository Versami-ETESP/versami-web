<?php
session_start();
include '../config.php';

// Limpa o erro da sessão se não for uma submissão POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    unset($_SESSION["login_error"]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Altera de 'email' para 'arroba_usuario' para corresponder ao formulário
    $arroba_usuario = $_POST["arroba_usuario"] ?? ''; // Usa 'arroba_usuario' do formulário
    $senha = hash("sha256", $_POST["senha"]); // Hash da senha para comparação com o BD

    // Verifica se o campo de arroba_usuario não está vazio
    if (empty($arroba_usuario)) {
        $_SESSION["login_error"] = "Por favor, digite seu nome de usuário.";
    } else {
        // Chamar a stored procedure usp_loginUsuario
        // A stored procedure já espera arroba_usuario e a senha hasheada
        $sql_call_sp = "{CALL usp_loginUsuario(?, ?)}";
        $params_call_sp = array($arroba_usuario, $senha);
        $stmt_call_sp = sqlsrv_query($conn, $sql_call_sp, $params_call_sp);

        if ($stmt_call_sp === false) {
            error_log("Erro ao chamar stored procedure usp_loginUsuario: " . print_r(sqlsrv_errors(), true));
            $_SESSION["login_error"] = "Erro interno no servidor de autenticação.";
        } else {
            $user_info = sqlsrv_fetch_array($stmt_call_sp, SQLSRV_FETCH_ASSOC);

            if ($user_info) {
                // Login bem-sucedido
                $_SESSION["usuario_id"] = $user_info["idUsuario"];
                $_SESSION["nome"] = $user_info["nome"];
                $_SESSION["user_info"] = $user_info; // Armazenar todas as informações retornadas pela SP

                header("Location: ../Feed/Feed.php");
                exit();
            } else {
                // Nenhuma linha retornada pela SP = credenciais inválidas
                $_SESSION["login_error"] = "Nome de usuário ou senha incorretos.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="Livros, Rede Social, Avaliações" />
    <link rel="icon" type="image/x-icon" href="../Assets/favicon.png" />
    <meta name="description"
        content="Versami, sua rede social para conectar leitores de todos os gêneros literários. Aqui, você pode avaliar, descobrir e compartilhar livros, criando uma comunidade engajada de apaixonados por leitura." />
    <meta name="author" content="Julia Maria, Matheus Canesso, Thamiris Fernandes, Ygor Silva" />
    <link rel="shortcut icon" href="../Assets/favicon.png" type="favicon" />
    <link rel="icon" href="../Assets/iconVersami.png" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/17dd42404d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="CSS/LoginStyle.css" />
    <title>Versami | Acesse sua conta</title>
</head>

<body>
    <header class="glass">
      <nav>
        <div class="logo">
          <img src="../Assets/logoVersamiBlue.png" alt="Logo Versami" />
        </div>
        <ul class="nav-links">
          <li>
            <a href="../Index/Index.php" id="inicio-link"><i class="fa-solid fa-house"></i></a>
          </li>
          <li>
            <a href="../Sobre/Sobre.php" id="sobre-link"><i class="fa-solid fa-book-open"></i></a>
          </li>
          <li>
            <a href="../Login/Login.php" id="login-link" class="active"><i class="fa-solid fa-user"></i></a>
          </li>
        </ul>
      </nav>
      <div class="glass-gradient-line"></div>
    </header>
    <h1 class="tituloPrincipal">
        Acesse agora a <span>Versami!</span>
    </h1>
    <main class="loginMain">
        <div class="loginPrincipal">
            <div class="login-panel">
                <h2 >Acessar Conta</h2>
                <?php if (isset($_SESSION['login_error'])): ?>
                    <p class="error-message">
                        <i class="material-icons-outlined">error_outline</i>
                        <?php echo $_SESSION['login_error']; ?>
                    </p>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>
                <form autocomplete="off" class="login-form" method="POST">
                    <div class="input-group">
                        <i class="material-icons-outlined">alternate_email</i>
                        <input type="text" name="arroba_usuario" id="login2" class="form-input" placeholder="Digite seu nome de usuário" required
                            value="<?php echo isset($_POST['arroba_usuario']) ? htmlspecialchars($_POST['arroba_usuario']) : ''; ?>" />
                    </div>
                    <div class="input-group">
                        <i class="material-icons-outlined">lock</i>
                        <input type="password" name="senha" id="senha2" class="form-input" placeholder="Digite sua senha"
                            required />
                    </div>
                    <a href="#" id="forgotPasswordLink" class="forgot-password-link">Esqueceu sua senha?</a>

                    <button type="submit" class="button submit-button" id="enviar">Entrar</button>
                </form>
            </div>
        </div>
        <div class="cadastroPrincipal msgindex">
            <h1>Sua <span>primeira</span> vez <br/>aqui?</h1>
            <p>
                Crie agora sua conta e <br />encontre diversos livros!
            </p>
            <a class="button loginButton" href="../Cadastro/cadastro.php">Criar Conta <i
                    class="fa-solid fa-chevron-right"></i></a>
        </div>
        <div class="carregando" id="carregando"></div>
    </main>
    <footer>
        <div class="footer-content">
            <div class="newsletter">
                <h4>Acompanhe nossa</h4>
                <h1>Newsletter</h1>
                <form>
                    <input type="email" placeholder="Seu email" required />
                    <button type="submit">Inscrever-se</button>
                </form>
            </div>
            <div class="middle">
                <div class="social-image">
                    <img src="../Assets/logoVersami.png" alt="Logo Versami" />
                </div>
                <p class="pRedes">Siga nossas redes sociais</p>
                <div class="social-links">
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    <a href="#"><i class="fa-brands fa-tiktok"></i></a>
                </div>
                <p>2024 | Versami Corporation &copy;</p>
            </div>
            <div class="about">
                <h4>Sobre nós</h4>
                <p>
                    Somos uma rede social voltada para conectar leitores de todos os
                    gêneros literários. Aqui, você pode avaliar, descobrir e
                    compartilhar livros, criando uma comunidade engajada de apaixonados
                    por leitura. Acesse pelo site ou aplicativo Android!
                </p>
            </div>
        </div>
    </footer>
    <div class="popup-overlay" id="forgotPasswordPopupOverlay">
        <div class="popup">
            <div class="btn-top-content">
                <div class="btn-close-content">
                    <button class="btn-close" id="closeForgotPasswordPopup"><i class="fa-solid fa-x"></i></button>
                </div>
                <h2>Redefinir Senha</h2>
            </div>
            <form id="resetPasswordForm">
                <div class="form-group">
                    <label for="reset_arroba_usuario">Nome de Usuário (@)</label>
                    <input type="text" id="reset_arroba_usuario" name="arroba_usuario"
                        placeholder="Digite seu nome de usuário" required>
                </div>

                <div class="form-group" id="secretQuestionGroup" style="display: none;">
                    <label for="reset_pergunta_secreta">Pergunta Secreta</label>
                    <input type="text" id="reset_pergunta_secreta" readonly disabled>
                </div>

                <div class="form-group" id="secretAnswerGroup" style="display: none;">
                    <label for="reset_resposta_secreta">Sua Resposta</label>
                    <input type="text" id="reset_resposta_secreta" name="resposta_secreta"
                        placeholder="Digite a resposta à sua pergunta secreta">
                </div>

                <div class="form-group" id="newPasswordGroup" style="display: none;">
                    <label for="reset_nova_senha">Nova Senha</label>
                    <input type="password" id="reset_nova_senha" name="nova_senha" placeholder="Digite sua nova senha">
                </div>

                <div class="form-group" id="confirmNewPasswordGroup" style="display: none;">
                    <label for="reset_confirmar_nova_senha">Confirmar Nova Senha</label>
                    <input type="password" id="reset_confirmar_nova_senha" name="confirmar_nova_senha"
                        placeholder="Confirme sua nova senha">
                </div>

                <button type="submit" class="button" id="redefinirSenhaBtn" style="display: none;">Redefinir
                    Senha</button>
            </form>
        </div>
    </div>
    <div id="toastNotification" class="toast-notification"></div>
    <script src="../JS/Script.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../JS/user-login.js"></script>
    <script>
        // Função para mostrar uma notificação toast (copiada do profile.php)
        function showToast(message, type = 'success') {
            console.log(`Showing toast: ${message} (${type})`);
            const toast = document.getElementById("toastNotification");
            if (!toast) {
                console.error("Toast notification element not found!");
                return;
            }
            toast.className = "toast-notification show " + type; // Adiciona 'show' e a classe do tipo
            toast.textContent = message;

            setTimeout(function () {
                toast.className = toast.className.replace("show", "");
            }, 3000);
        }

        // --- FUNÇÕES PARA O POPUP DE ESQUECI MINHA SENHA ---

        function openForgotPasswordModal() {
            console.log("Abrindo popup de redefinição de senha.");
            document.getElementById('forgotPasswordPopupOverlay').style.display = 'flex';
            // Resetar formulário ao abrir
            document.getElementById('resetPasswordForm').reset();
            $('#secretQuestionGroup').hide();
            $('#secretAnswerGroup').hide();
            $('#newPasswordGroup').hide();
            $('#confirmNewPasswordGroup').hide();
            $('#redefinirSenhaBtn').hide();
        }

        function closeForgotPasswordModal() {
            console.log("Fechando popup de redefinição de senha.");
            document.getElementById('forgotPasswordPopupOverlay').style.display = 'none';
        }

        $(document).ready(function () {
            // Listener para o link "Esqueceu sua senha?"
            $('#forgotPasswordLink').on('click', function (e) {
                e.preventDefault();
                openForgotPasswordModal();
            });

            // Listener para o botão de fechar (X) do popup
            $('#closeForgotPasswordPopup').on('click', function () {
                closeForgotPasswordModal();
            });

            // Listener para fechar o popup ao clicar fora
            $('#forgotPasswordPopupOverlay').on('click', function (event) {
                if ($(event.target).is('#forgotPasswordPopupOverlay')) {
                    closeForgotPasswordModal();
                }
            });

        // Listener para o campo de nome de usuário para buscar a pergunta secreta
        $('#reset_arroba_usuario').on('blur', function () {
            const arroba_usuario = $(this).val().trim();
            if (arroba_usuario.length > 0) {
                $.ajax({
                    url: 'get_pergunta_secreta.php', // Novo arquivo PHP para buscar a pergunta
                    method: 'POST',
                    data: { arroba_usuario: arroba_usuario },
                    dataType: 'json',
                    success: function (response) {
                        console.log("Resposta da pergunta secreta:", response);
                        if (response.success) {
                            $('#reset_pergunta_secreta').val(response.pergunta);
                            $('#secretQuestionGroup').show();
                            $('#secretAnswerGroup').show(); // Exibe o campo de resposta
                            $('#newPasswordGroup').show();   // Exibe os campos de nova senha
                            $('#confirmNewPasswordGroup').show();
                            $('#redefinirSenhaBtn').show();  // Exibe o botão de redefinir
                        } else {
                            showToast(response.error || "Nome de usuário não encontrado ou sem pergunta secreta.", 'error');
                            $('#secretQuestionGroup').hide();
                            $('#secretAnswerGroup').hide();
                            $('#newPasswordGroup').hide();
                            $('#confirmNewPasswordGroup').hide();
                            $('#redefinirSenhaBtn').hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Erro ao buscar pergunta secreta:", status, error, xhr.responseText);
                        showToast("Erro ao buscar pergunta secreta. Tente novamente.", 'error');
                        $('#secretQuestionGroup').hide();
                        $('#secretAnswerGroup').hide();
                        $('#newPasswordGroup').hide();
                        $('#confirmNewPasswordGroup').hide();
                        $('#redefinirSenhaBtn').hide();
                    }
                });
            } else {
                $('#secretQuestionGroup').hide();
                $('#secretAnswerGroup').hide();
                $('#newPasswordGroup').hide();
                $('#confirmNewPasswordGroup').hide();
                $('#redefinirSenhaBtn').hide();
            }
        });

        // Listener para o formulário de redefinição de senha
        $('#resetPasswordForm').on('submit', function (e) {
            e.preventDefault();
            console.log("Formulário de redefinição submetido.");

            const arroba_usuario = $('#reset_arroba_usuario').val().trim();
            const resposta_secreta = $('#reset_resposta_secreta').val().trim();
            const nova_senha = $('#reset_nova_senha').val();
            const confirmar_nova_senha = $('#reset_confirmar_nova_senha').val();

            if (!arroba_usuario || !resposta_secreta || !nova_senha || !confirmar_nova_senha) {
                showToast("Por favor, preencha todos os campos.", 'error');
                return;
            }

            if (nova_senha !== confirmar_nova_senha) {
                showToast("As novas senhas não coincidem.", 'error');
                return;
            }

            if (nova_senha.length < 8) {
                showToast("A nova senha deve ter pelo menos 8 caracteres.", 'error');
                return;
            }

            $.ajax({
                url: 'redefinir_senha.php', // Novo arquivo PHP para redefinir a senha
                method: 'POST',
                data: {
                    arroba_usuario: arroba_usuario,
                    resposta_secreta: resposta_secreta,
                    nova_senha: nova_senha
                },
                dataType: 'json',
                success: function (response) {
                    console.log("Resposta de redefinição de senha:", response);
                    if (response.success) {
                        showToast(response.message, 'success');
                        closeForgotPasswordModal();
                    } else {
                        showToast(response.error || "Erro ao redefinir a senha.", 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erro ao redefinir senha:", status, error, xhr.responseText);
                    showToast("Ocorreu um erro ao redefinir a senha. Tente novamente.", 'error');
                }
            });
        });
        });
    </script>
</body>

</html>