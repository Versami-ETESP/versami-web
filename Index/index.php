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
    <link rel="shortcut icon" href="../../Assets/favicon.png" type="favicon" />
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
          <li><a href="../Index/index.php" id="inicio-link"  class="active">Início</a></li>
          <li><a href="../Sobre/sobre.php" id="sobre-link">Sobre nós</a></li>
          <li><a href="../Blog/blog.php" id="blog-link">Blog</a></li>
          <li><a href="../Contato/contato.php" id="contato-link">Contato</a></li>
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
        <h2>Conecte-se com 
          <br>leitores apaixonados</h2>
          <p>Interaja, compartilhe resenhas e faça novos amigos 
            <br>através dos livros que você ama.</p>
            <button onclick="ContaPage()" class="buttonP" type="button">Criar Conta <i class="fa-solid fa-chevron-right"></i></button>
      </div>

      
    </section>

    <div class="iconCenter">
      <i class="fa-solid fa-chevron-down"></i>
    </div>
    
    <section class="telaPesquisa">
      <div class="containerCenter">
        <h2 id="telaPesquisa">Explore o <span>mundo literário</span>
          <br>e avalie <span>suas leituras</span></h2>
        <p>Compartilhe sua avaliação a rede de outros leitores a <br>explorar novas histórias.</p>
        <div class="boxPesquisa">
          <input class="intP" type="text" name="busca" placeholder="Buscar por título, autor ou gênero...">
          <button class="buttonL" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
        <div class="iconCenterBlue">
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>
    </section>

    <section class="telaCategorias">
      <div class="containerCenterCategorias">
        <h2>Encontre diversas <span>categorias</span></h2>
        <p>Explore diversas categorias de livros e converse 
          <br>com amantes da literatura.</p>
        <p class="buttonS"><a href="../../Contrução/Desenvolvimento.html">Saiba mais <i class="fa-solid fa-chevron-right"></i></a></p>
      </div>
      <div class="containerCenterCards">
        <div class="card">
          <img src="../Assets/imgCard01.png" alt="">
          <div class="cardContent">
            <p>Romance</p>
          </div>
        </div>
        <div class="card">
          <img src="../Assets/imgCard02.png" alt="">
          <div class="cardContent">
            <p>Suspense</p>
          </div>
        </div>
        <div class="card">
          <img src="../Assets/imgCard03.png" alt="">
          <div class="cardContent">
            <p>Aventura</p>
          </div>
        </div>
      </div>
    </section>
    
    <section class="blogVersami">
        <div class="iconCenterRotate">
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      <h2>Blog da <span>Versami</span></h2>
      <p>Fique por dentro de todas as novidades da plataforma Versami!
        <br>Lançamentos, funcionalidades e atualizações!</p>
        <div class="containerCards">
          <div class="cardBlog">
              <img src="../Assets/imgCardBlog.jpg" alt="Imagem">
              <div class="cardText">
                <h2>Atualização 2.0!</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                </p>
              </div>
            <div class="btnContainer">
              <button onclick="BlogPage()" class="btnSeta"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>
          <div class="cardBlog">
            <img src="../Assets/imgCardBlog.jpg" alt="Imagem">
              <div class="cardText">
                <h2>Livros Collen Hoover!</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                </p>
              </div>
            <div class="btnContainer">
              <button onclick="BlogPage()" class="btnSeta"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>
          <div class="cardBlog">
            <img src="../Assets/imgCardBlog.jpg" alt="Imagem">
              <div class="cardText">
                <h2>Sistema de Avaliações!</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Commodi necessitatibus officia nihil voluptatum totam voluptate eligendi voluptates nulla doloribus, voluptas facere? Animi eius culpa impedit ratione repellat? Eligendi, praesentium distinctio.
                </p>
              </div>
            <div class="btnContainer">
              <button onclick="BlogPage()" class="btnSeta"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>
      </div>
    </section>

    <div class="iconCenterApp">
      <i class="fa-solid fa-chevron-down"></i>
    </div>

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
