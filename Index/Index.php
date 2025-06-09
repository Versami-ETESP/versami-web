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
    <title>Versami | Início</title>
  </head>

  <body>
    <header class="glass">
      <nav>
        <div class="logo">
          <img src="../Assets/logoVersamiBlue.png" alt="Logo Versami" />
        </div>
        <ul class="nav-links">
          <li>
            <a href="Index.php" id="inicio-link" class="active"><i class="fa-solid fa-house"></i></a>
          </li>
          <li>
            <a href="../Sobre/Sobre.php" id="sobre-link"><i class="fa-solid fa-book-open"></i></a>
          </li>
          <li>
            <a href="../Login/Login.php"><i class="fa-solid fa-user"></i></a>
          </li>
        </ul>
      </nav>
      <div class="glass-gradient-line"></div>
    </header>

    <main>
      <section class="hero-section">
        <div class="hero-content">
          <h2>Conecte-se com <br /><span>leitores apaixonados</span></h2>
          <p>
            Interaja, compartilhe resenhas e faça novos amigos <br />através dos
            livros que você ama.
          </p>
          <button onclick="ContaPage()" class="button" type="button">
            Criar Conta <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
        <div class="hero-image">
          <img src="../Assets/imgIndex02.png" alt="Books illustration" />
        </div>
      </section>

      <section class="search-section">
        <div class="features-section">
          <div class="feature-card">
            <div class="icon-circle">
              <i class="fas fa-search"></i>
            </div>
            <h3>Explore o mundo literário</h3>
            <p>
              Descubra novos títulos, autores e gêneros que você vai amar.
            </p>
          </div>
          <div class="feature-card">
            <div class="icon-circle">
              <i class="fas fa-star"></i>
            </div>
            <h3>Avalie suas leituras</h3>
            <p>
              Compartilhe suas opiniões e ajude outros leitores a escolher.
            </p>
          </div>
          <div class="feature-card">
            <div class="icon-circle">
              <i class="fas fa-users"></i>
            </div>
            <h3>Conecte-se e socialize</h3>
            <p>Encontre sua comunidade de leitores e troque ideias.</p>
          </div>
        </div>
      </section>

      <section class="categories-section">
        <div class="categories-header">
          <h2>Encontre diversas <span>categorias</span></h2>
          <p>
            Explore diversas categorias de livros e converse <br />com amantes
            da literatura.
          </p>
          <a href="../Login/Login.php" class="button"
            >Saiba mais <i class="fa-solid fa-chevron-right"></i
          ></a>
        </div>
        <div class="categories-cards">
          <div class="category-card">
            <img src="../Assets/imgCard01.png" alt="Romance" />
            <div class="card-overlay">
              <p>Romance</p>
            </div>
          </div>
          <div class="category-card">
            <img src="../Assets/imgCard02.png" alt="Suspense" />
            <div class="card-overlay">
              <p>Suspense</p>
            </div>
          </div>
          <div class="category-card">
            <img src="../Assets/imgCard03.png" alt="Aventura" />
            <div class="card-overlay">
              <p>Aventura</p>
            </div>
          </div>
        </div>
      </section>

      <section class="search-section">
        <div class="search-content">
          <h2>
            Explore o <span>mundo literário</span> e avalie
            <span>suas leituras</span>
          </h2>
          <p>
            Compartilhe sua avaliação e ajude a rede de outros leitores a
            explorar novas histórias.
          </p>
          <div class="exploration-buttons">
            <a href="../Explorar.php" class="button button-explore"
              >Explorar Livros <i class="fas fa-book-open"></i
            ></a>
          </div>
        </div>
      </section>

      <section class="blog-preview-section">
        <div class="blog-header">
          <h2>Blog da <span>Versami</span></h2>
          <p>
            Fique por dentro de todas as novidades da plataforma Versami!
            <br />Lançamentos, funcionalidades e atualizações!
          </p>
        </div>
        <div class="blog-cards-container">
          <div class="blog-card">
            <img src="../Assets/imgBlog01.jpg" alt="Atualização" />
            <div class="blog-card-content">
              <h3>Atualização 2.0!</h3>
              <p>
                A equipe Versami está vibrando de alegria ao anunciar a chegada
                da nossa aguardada Atualização 2.0! Preparamos uma série de
                melhorias e novas funcionalidades cuidadosamente pensadas para
                transformar sua jornada literária em algo ainda mais intuitivo,
                conectado e prazeroso.
              </p>
              <button onclick="BlogPage()" class="button">
                Leia mais <i class="fa-solid fa-chevron-right"></i>
              </button>
            </div>
          </div>
          <div class="blog-card">
            <img src="../Assets/imgBlog02.jpg" alt="Livros Collen Hoover" />
            <div class="blog-card-content">
              <h3>Livros Collen Hoover!</h3>
              <p>
                Temos uma excelente notícia para todos os apaixonados por dramas
                e romances intensos na comunidade Versami! Com grande
                entusiasmo, anunciamos a adição de uma vasta coleção de obras da
                aclamada autora Colleen Hoover à nossa plataforma.
              </p>
              <button onclick="BlogPage()" class="button">
                Leia mais <i class="fa-solid fa-chevron-right"></i>
              </button>
            </div>
          </div>
          <div class="blog-card">
            <img src="../Assets/imgBlog03.jpg" alt="Sistema de Avaliações" />
            <div class="blog-card-content">
              <h3>Sistema de Avaliações!</h3>
              <p>
                Na Versami, acreditamos que cada leitor tem uma perspectiva
                única e valiosa para compartilhar. É por isso que estamos
                entusiasmados em destacar nosso robusto sistema de avaliações de
                livros, projetado para empoderar sua experiência literária.
              </p>
              <button onclick="BlogPage()" class="button">
                Leia mais <i class="fa-solid fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="app-section">
        <div class="app-content-left">
          <h2>Versami já disponível para <span>Android!</span></h2>
          <p>
            Baixe agora o nosso App da Versami para<br />se conectar com
            leitores e avaliar seus<br />livros favoritos.
          </p>
          <img src="../Assets/icongoogle.png" alt="Google Play Store" />
        </div>
        <div class="app-content-right">
          <img
            src="../Assets/imgCelulares.png"
            alt="Celulares com o app Versami"
          />
        </div>
      </section>
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

    <script src="JS/Script.js"></script>
  </body>
</html>
