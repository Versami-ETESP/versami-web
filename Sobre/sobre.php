<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="Livros, Rede Social, Avaliações" />
    <meta
      name="description"
      content="Versami, sua rede social para conectar leitores de todos os gêneros literários. Aqui, você pode avaliar, descobrir e compartilhar livros, criando uma comunidade engajada de apaixonados por leitura."
    />
    <meta
      name="author"
      content="Julia Maria, Matheus Canesso, Thamiris Fernandes, Ygor Silva"
    />
    <link rel="shortcut icon" href="../Assets/favicon.png" type="favicon" />
    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"
      rel="stylesheet"
    />
    <script
      src="https://kit.fontawesome.com/17dd42404d.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="CSS/Style.css" />
    <title>Versami</title>
  </head>
  <body>
    <header class="glass">
      <nav>
        <div class="logo">
          <img src="../Assets/logoVersamiBlue.png" alt="Logo Versami" />
        </div>
        <ul class="nav-links">
          <li>
            <a href="../Index/index.php" id="inicio-link">Início</a>
          </li>
          <li>
            <a href="sobre.php" id="sobre-link" class="active">Sobre nós</a>
          </li>
          <li>
            <a href="../Blog/blog.php" id="blog-link">Blog</a>
          </li>
          <li>
            <a href="../Contato/contato.php" id="contato-link"
              >Contato</a
            >
          </li>
        </ul>
        <div class="user-icon">
          <span class="material-icons-outlined"
            ><a href="../Login/login.php"> account_circle </a></span
          >
        </div>
      </nav>
    </header>

    <section class="telaPrincipal">
      <div class="containerLeft">
        <h2>O que é a Versami?</h2>
        <p>
          Lorem ipsum, dolor sit amet consectetur adipisicing elit.<br />
          Cumque, odit doloremque ipsa ducimus culpa tenetur fugit natus
          accusantium, et modi, aliquid fugiat nulla vitae odio tempora saepe
          magnam ipsum eligendi. Lorem ipsum, dolor sit amet consectetur
          adipisicing elit. Cumque, odit doloremque ipsa ducimus culpa tenetur
          fugit natus accusantium, et modi, aliquid fugiat nulla vitae odio
          tempora saepe magnam ipsum eligendi.
        </p>
        <button class="buttonP" type="button" onclick="ContaPage()">
          Criar Conta <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </section>

    <div class="iconCenter">
      <i class="fa-solid fa-chevron-down"></i>
    </div>

    <section class="telaPesquisa">
      <div class="containerCenter">
        <h2><span>Qual a nossa ideia?</span></h2>
        <p>
          Lorem ipsum, dolor sit amet consectetur adipisicing elit. Cumque, odit
          doloremque ipsa ducimus culpa tenetur fugit natus accusantium, et
          modi, aliquid fugiat nulla vitae odio tempora saepe magnam ipsum
          eligendi. Lorem ipsum, dolor sit amet consectetur adipisicing elit.
          Cumque, odit doloremque ipsa ducimus culpa tenetur fugit natus
          accusantium, et modi, aliquid fugiat nulla vitae odio tempora saepe
          magnam ipsum eligendi. Lorem ipsum, dolor sit amet consectetur
          adipisicing elit. Cumque, odit doloremque ipsa ducimus culpa tenetur
          fugit natus accusantium, et modi, aliquid fugiat nulla vitae odio
          tempora saepe magnam ipsum eligendi.Lorem ipsum, dolor sit amet
          consectetur adipisicing elit. Cumque, odit doloremque ipsa ducimus
          culpa tenetur fugit natus accusantium, et modi, aliquid fugiat nulla
          vitae odio tempora saepe magnam ipsum eligendi.Lorem ipsum, dolor sit
          amet consectetur adipisicing elit. Cumque, odit doloremque ipsa
          ducimus culpa tenetur fugit natus accusantium, et modi, aliquid fugiat
          nulla vitae odio tempora saepe magnam ipsum eligendi.
        </p>
        <button class="buttonP" type="button" onclick="ContaPage()">
          Criar Conta <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </section>

    <section class="telaApp">
      <div class="containerEsquerdoApp">
        <h2>Versami já disponível para <span>Android!</span></h2>
        <p>
          Baixe agora o nosso App da Versami para<br />
          se conectar com leitores e avaliar seus<br />
          livros favoritos.
        </p>
        <img src="../Assets/iconGoogle.png" alt="" />
      </div>
      <div class="containerDireitoApp">
        <img src="../Assets/imgCelulares.png" alt="" />
      </div>
    </section>

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

    <script src="JS/Script.js"></script>
  </body>
</html>
