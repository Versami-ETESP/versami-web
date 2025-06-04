<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bio = htmlspecialchars($_POST['bio'], ENT_QUOTES, 'UTF-8');
    $profile_pic = $_FILES['profile_pic'];
    $cover_pic = $_FILES['cover_pic'];

    $uploadDir = "uploads/";

    if (!empty($profile_pic['fullname']) && !empty($cover_pic['fullname'])) {
        $profilePicName = uniqid() . "_" . basename($profile_pic['fullname']);
        $coverPicName = uniqid() . "_" . basename($cover_pic['fullname']);

        $profilePicPath = $uploadDir . $profilePicName;
        $coverPicPath = $uploadDir . $coverPicName;

        if (move_uploaded_file($profile_pic['tmp_name'], $profilePicPath) &&
            move_uploaded_file($cover_pic['tmp_name'], $coverPicPath)) {
            
            $query = "UPDATE users SET profile_pic = ?, cover_pic = ?, bio = ? WHERE username = ?";
            $params = array($profilePicName, $coverPicName, $bio, $_SESSION['user']['username']);

            if (!sqlsrv_query($conn, $query, $params)) {
                die(print_r(sqlsrv_errors(), true));
            }

            $_SESSION['user']['profile_pic'] = $profilePicName;
            $_SESSION['user']['cover_pic'] = $coverPicName;
            $_SESSION['user']['bio'] = $bio;
        } else {
            echo "Erro ao enviar arquivos.";
            exit();
        }
    }

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="post" enctype="multipart/form-data">
        <h2>Configurar Perfil</h2>

        <label for="cover_pic">Foto de Capa</label>
        <input type="file" name="cover_pic" id="cover_pic" accept="image/*" required>
        <img id="cover_preview" class="cover-preview" src="uploads/<?php echo $_SESSION['user']['cover_pic'] ?? 'default-cover.jpg'; ?>" alt="Prévia da capa">

        <label for="profile_pic">Foto de Perfil</label>
        <input type="file" name="profile_pic" id="profile_pic" accept="image/*" required>
        <img id="profile_preview" class="profile-preview" src="uploads/<?php echo $_SESSION['user']['profile_pic'] ?? 'default-profile.jpg'; ?>" alt="Prévia da foto de perfil">

        <textarea name="bio" placeholder="Biografia"><?php echo $_SESSION['user']['bio'] ?? ''; ?></textarea>
        <button type="submit">Salvar</button>
    </form>

    <script>
        document.getElementById("cover_pic").addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                document.getElementById("cover_preview").src = URL.createObjectURL(file);
            }
        });

        document.getElementById("profile_pic").addEventListener("change", function(event) {
            const file = event.target.files[0];
            if (file) {
                document.getElementById("profile_preview").src = URL.createObjectURL(file);
            }
        });
    </script>
</body>
</html>
