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
    <link rel="shortcut icon" href="../Assents/favicon.png" type="favicon" />
    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"
      rel="stylesheet"
    />
    <script
      src="https://kit.fontawesome.com/17dd42404d.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="CSS/Contato.css" />
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
            <a href="../Sobre/sobre.php" id="sobre-link">Sobre nós</a>
          </li>
          <li>
            <a href="../Blog/blog.php" id="blog-link">Blog</a>
          </li>
          <li>
            <a
              href="../../Contato/HTML/Contato.html"
              id="contato-link"
              class="active"
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
        <h2>Entre em contato<br />
          conosco</h2>
        <p>
          Envie uma mensagem pelo formulário ao lado e<br>
          entraremos em contato o mais rápido possivel!
        </p>
      </div>
    </section>

    <section class="telaMeio">
      <div class="cardsContainer">
        <div class="cardContato">
          <i class="fa-solid fa-phone"></i>
          <div class="cardContent">
            <h2>Número de 
              <br>telefone
            </h2>
            <p>(11) 11223-3445</p>
          </div>
        </div>
        <div class="cardContato">
          <i class="fa-solid fa-location-dot"></i>
          <div class="cardContent">
            <h2>Escritório
              <br>presencial
            </h2>
            <p>Av. Tiradentes, 615 - Bom Retiro, 
              São Paulo - SP, 01101-010</p>
          </div>
        </div>
        <div class="cardContato">
          <div class="cardFormulario">
            <h2> Formulário de contato</h2>
            <form>
              <input class="inptEntrada" type="text" id="nome"
              name="nome" placeholder="Seu nome" required>
              <input class="inptEntrada" type="email"
              id="email" name="email" placeholder="Seu melhor e-mail" required>
              <textarea class="inptEntrada" id="mensagem" name="mensagem" rows="8" placeholder="Sua mensagem" required></textarea>
              <button onclick="ContaPage()" class="buttonPT">Enviar <i class="fa-solid fa-chevron-right"></i></button>
            </form>
          </div>
        </div>
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
        <img src="../Assets/icongoogle.png" alt="" />
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
            <button class="btnFooter" type="submit">Inscrever-se</button>
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
          <p>2024 | Versami Corporation &copy; </p>
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
