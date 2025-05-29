<?php
// cadastrar_livro.php
include 'config.php';

// Gêneros literários pré-definidos
$generosPredefinidos = [
    "Ficção Científica",
    "Fantasia",
    "Romance",
    "Mistério",
    "Terror",
    "Aventura",
    "Biografia",
    "História",
    "Autoajuda",
    "Negócios",
    "Poesia",
    "Drama",
    "Comédia",
    "Infantil",
    "Juvenil",
    "Distopia",
    "Suspense",
    "Policial",
    "Ficção Histórica",
    "Realismo Mágico"
];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeLivro = trim($_POST['nomeLivro'] ?? '');
    $descLivro = trim($_POST['descLivro'] ?? '');
    $nomeAutor = trim($_POST['nomeAutor'] ?? '');
    $generoSelecionado = trim($_POST['genero'] ?? '');

    // Validar dados
    $erros = [];

    if (empty($nomeLivro)) {
        $erros[] = "O título do livro é obrigatório.";
    } elseif (strlen($nomeLivro) > 80) {
        $erros[] = "O título do livro deve ter no máximo 80 caracteres.";
    }

    if (empty($nomeAutor)) {
        $erros[] = "O autor é obrigatório.";
    } elseif (strlen($nomeAutor) > 80) {
        $erros[] = "O nome do autor deve ter no máximo 80 caracteres.";
    }

    if (empty($generoSelecionado) || !in_array($generoSelecionado, $generosPredefinidos)) {
        $erros[] = "Selecione um gênero válido.";
    }

    if (strlen($descLivro) > 250) {
        $erros[] = "A descrição deve ter no máximo 250 caracteres.";
    }

    // Validar imagem
    $imgCapa = null;
    if (isset($_FILES['imgCapa'])) {
        if ($_FILES['imgCapa']['error'] == UPLOAD_ERR_OK) {
            $tamanhoMaximo = 2 * 1024 * 1024; // 2MB
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

            if ($_FILES['imgCapa']['size'] > $tamanhoMaximo) {
                $erros[] = "A imagem da capa deve ter no máximo 2MB.";
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipoMime = $finfo->file($_FILES['imgCapa']['tmp_name']);

            if (!in_array($tipoMime, $tiposPermitidos)) {
                $erros[] = "Apenas imagens JPEG, PNG ou GIF são permitidas.";
            }
        } elseif ($_FILES['imgCapa']['error'] != UPLOAD_ERR_NO_FILE) {
            $erros[] = "Erro no upload da imagem: " . $_FILES['imgCapa']['error'];
        }
    }

    if (empty($erros)) {
        try {
            // Iniciar transação
            sqlsrv_begin_transaction($conn);

            // 1. Verificar ou cadastrar o autor - VERSÃO CORRIGIDA
            $sqlAutor = "SELECT idAutor FROM tblAutor WHERE nomeAutor = ?";
            $paramsAutor = array($nomeAutor);
            $stmtAutor = sqlsrv_query($conn, $sqlAutor, $paramsAutor);

            if ($stmtAutor === false) {
                throw new Exception("Erro ao buscar autor: " . print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmtAutor)) {
                $row = sqlsrv_fetch_array($stmtAutor, SQLSRV_FETCH_ASSOC);
                $idAutor = $row['idAutor'];
            } else {
                $sqlNovoAutor = "INSERT INTO tblAutor (nomeAutor, descAutor) VALUES (?, ?)";
                $paramsNovoAutor = array($nomeAutor, "Autor cadastrado automaticamente");
                $stmtNovoAutor = sqlsrv_query($conn, $sqlNovoAutor, $paramsNovoAutor);

                if ($stmtNovoAutor === false) {
                    throw new Exception("Erro ao cadastrar autor: " . print_r(sqlsrv_errors(), true));
                }

                // Obter o ID do autor recém-inserido
                $sqlId = "SELECT SCOPE_IDENTITY() AS id";
                $stmtId = sqlsrv_query($conn, $sqlId);
                if ($stmtId === false || !sqlsrv_fetch($stmtId)) {
                    throw new Exception("Erro ao obter ID do autor: " . print_r(sqlsrv_errors(), true));
                }
                $idAutor = sqlsrv_get_field($stmtId, 0);
                sqlsrv_free_stmt($stmtId);
            }

            // 2. Verificar ou cadastrar o gênero - VERSÃO CORRIGIDA
            $sqlGenero = "SELECT idGenero FROM tblGenero WHERE nomeGenero = ?";
            $paramsGenero = array($generoSelecionado);
            $stmtGenero = sqlsrv_query($conn, $sqlGenero, $paramsGenero);

            if ($stmtGenero === false) {
                throw new Exception("Erro ao buscar gênero: " . print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmtGenero)) {
                $row = sqlsrv_fetch_array($stmtGenero, SQLSRV_FETCH_ASSOC);
                $idGenero = $row['idGenero'];
            } else {
                $sqlNovoGenero = "INSERT INTO tblGenero (nomeGenero, descGenero) VALUES (?, ?)";
                $paramsNovoGenero = array($generoSelecionado, "Gênero cadastrado automaticamente");
                $stmtNovoGenero = sqlsrv_query($conn, $sqlNovoGenero, $paramsNovoGenero);

                if ($stmtNovoGenero === false) {
                    throw new Exception("Erro ao cadastrar gênero: " . print_r(sqlsrv_errors(), true));
                }

                // Obter o ID do gênero recém-inserido
                $sqlId = "SELECT SCOPE_IDENTITY() AS id";
                $stmtId = sqlsrv_query($conn, $sqlId);
                if ($stmtId === false || !sqlsrv_fetch($stmtId)) {
                    throw new Exception("Erro ao obter ID do gênero: " . print_r(sqlsrv_errors(), true));
                }
                $idGenero = sqlsrv_get_field($stmtId, 0);
                sqlsrv_free_stmt($stmtId);
            }

            // Verificação adicional dos IDs obtidos
            if (empty($idAutor) || !is_numeric($idAutor)) {
                throw new Exception("ID do autor inválido após tentativa de obtenção/cadastro");
            }

            if (empty($idGenero) || !is_numeric($idGenero)) {
                throw new Exception("ID do gênero inválido após tentativa de obtenção/cadastro");
            }

            // 3. Processar a imagem da capa - VERSÃO CORRIGIDA
            $sqlLivro = "";
            $paramsLivro = array();

            if (isset($_FILES['imgCapa']) && $_FILES['imgCapa']['error'] == UPLOAD_ERR_OK) {
                $imgCapa = file_get_contents($_FILES['imgCapa']['tmp_name']);
                
                $sqlLivro = "INSERT INTO tblLivro (nomeLivro, descLivro, imgCapa, idGenero, idAutor) 
                             VALUES (?, ?, CONVERT(varbinary(max), ?), ?, ?)";
                
                $paramsLivro = array(
                    array($nomeLivro, SQLSRV_PARAM_IN),
                    array($descLivro, SQLSRV_PARAM_IN),
                    array($imgCapa, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY)),
                    array($idGenero, SQLSRV_PARAM_IN),
                    array($idAutor, SQLSRV_PARAM_IN)
                );
            } else {
                $sqlLivro = "INSERT INTO tblLivro (nomeLivro, descLivro, idGenero, idAutor) 
                             VALUES (?, ?, ?, ?)";
                
                $paramsLivro = array(
                    array($nomeLivro, SQLSRV_PARAM_IN),
                    array($descLivro, SQLSRV_PARAM_IN),
                    array($idGenero, SQLSRV_PARAM_IN),
                    array($idAutor, SQLSRV_PARAM_IN)
                );
            }

            // 4. Cadastrar o livro
            $stmtLivro = sqlsrv_query($conn, $sqlLivro, $paramsLivro);

            if ($stmtLivro === false) {
                throw new Exception("Erro ao cadastrar livro: " . print_r(sqlsrv_errors(), true));
            }

            // Commit da transação
            sqlsrv_commit($conn);
            $sucesso = "Livro cadastrado com sucesso!";

            // Limpar os campos do formulário após o sucesso
            $_POST = array();

        } catch (Exception $e) {
            // Rollback em caso de erro
            sqlsrv_rollback($conn);
            $erro = "Erro no cadastro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Livro - Versami</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            width: 100%;
            margin-top: 20px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .img-preview {
            max-width: 200px;
            max-height: 300px;
            margin-top: 10px;
            display: none;
        }

        .required-field::after {
            content: " *";
            color: red;
        }

        small {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Cadastrar Novo Livro</h1>

        <?php if (isset($sucesso)): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form action="cadastrar_livro.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nomeLivro" class="required-field">Título do Livro</label>
                <input type="text" id="nomeLivro" name="nomeLivro" required
                    value="<?php echo htmlspecialchars($_POST['nomeLivro'] ?? ''); ?>">
                <small>Máximo de 80 caracteres</small>
            </div>

            <div class="form-group">
                <label for="nomeAutor" class="required-field">Autor</label>
                <input type="text" id="nomeAutor" name="nomeAutor" required
                    value="<?php echo htmlspecialchars($_POST['nomeAutor'] ?? ''); ?>">
                <small>Digite o nome do autor. Se não existir, será cadastrado automaticamente (máx. 80
                    caracteres)</small>
            </div>

            <div class="form-group">
                <label for="genero" class="required-field">Gênero</label>
                <select id="genero" name="genero" required>
                    <option value="">Selecione um gênero...</option>
                    <?php foreach ($generosPredefinidos as $genero): ?>
                        <option value="<?php echo htmlspecialchars($genero); ?>" <?php if (isset($_POST['genero']) && $_POST['genero'] === $genero)
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($genero); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descLivro">Descrição</label>
                <textarea id="descLivro"
                    name="descLivro"><?php echo htmlspecialchars($_POST['descLivro'] ?? ''); ?></textarea>
                <small>Máximo de 250 caracteres</small>
            </div>

            <div class="form-group">
                <label for="imgCapa">Capa do Livro</label>
                <input type="file" id="imgCapa" name="imgCapa" accept="image/jpeg, image/png, image/gif">
                <small>Apenas imagens JPEG, PNG ou GIF (máx. 2MB)</small>
                <img id="imgPreview" class="img-preview" alt="Pré-visualização da capa">
            </div>

            <button type="submit">Cadastrar Livro</button>
        </form>
    </div>

    <script>
        // Mostrar pré-visualização da imagem
        document.getElementById('imgCapa').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imgPreview = document.getElementById('imgPreview');
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>