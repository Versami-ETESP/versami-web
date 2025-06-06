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
    <link rel="shortcut icon" href="../Assets/favicon.png" type="favicon/png" />
    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"
      rel="stylesheet"
    />
    <script
      src="https://kit.fontawesome.com/17dd42404d.js"
      crossorigin="anonymous"
    ></script>
    <link rel="stylesheet" href="CSS/Style.css" />
    <title>Versami | Blog</title>
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
            <a href="../Sobre/sobre.php" id="sobre-link"
              >Sobre nós</a
            >
          </li>
          <li><a href="blog.php" id="blog-link" class="active">Blog</a></li>
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

    <div class="blogversami">
      <div class="containerMessageLeft">
        <h2>Blog - <span>Versami</span></h2>
        <p>
          No Blog Versami, você encontrará atualizações sobre o nosso site e
          aplicativo, além de funcionalidades e novidades. O espaço também é
          dedicado a eventos literários, autores renomados, clássicos da
          literatura e críticas, criando uma comunidade vibrante para os
          apaixonados por leitura. Junte-se a nós e compartilhe suas
          experiências!
        </p>
        <button onclick="ContaPage()" class="buttonP" type="button">Criar Conta <i class="fa-solid fa-chevron-right"></i></button>
      </div>

    <div class="overlayPopUp" id="overlay" onclick="closePopup(event)">
        <div class="containerPopUp">
          <div class="popup" onclick="event.stopPropagation()">
              <button class="btnClose" onclick="closePopup()"><i class="fa-solid fa-xmark"></i></button>
              <img src="../Assets/imgCardBlog.jpg" alt="Imagem">
              <h2>Atualização!</h2>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ab quaerat dolore suscipit explicabo nihil, dolorum officia. Reprehenderit asperiores voluptas non alias quis omnis id autem. Sit minus beatae aspernatur tenetur?</p>
              <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nisi illum nemo natus illo ullam rem esse vel quasi alias at, quos dolores exercitationem iste nam, quod debitis? Minima, consequatur rerum!</p>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti sapiente porro non recusandae iusto officia fugit minima distinctio error quaerat sequi, labore, soluta perspiciatis magnam harum dolor, magni fugiat maxime?</p>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatibus earum totam pariatur ipsam, inventore fugit sint reiciendis necessitatibus, eius suscipit praesentium ducimus, amet consequatur vero consequuntur qui repellat neque possimus.</p>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quas eius architecto excepturi voluptatem vel alias commodi quidem. Assumenda quo vitae ut, voluptates ipsam culpa illum fugiat tempora rem, quae commodi.</p>
              <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Iste aspernatur vero iusto suscipit quae sed sapiente voluptate vitae consequuntur a molestias porro quo eveniet repudiandae, totam praesentium. Placeat, sint dolorem.</p>
              <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. At ratione temporibus non. Fuga quod sit aliquid quaerat iusto distinctio. Ad alias veniam quaerat accusantium porro eum cum sed ea laudantium?</p>
          </div>
        </div>
    </div>
    </div>

      <div class="containerCards">
        <div class="card" onclick="openPopup()">
          <img src="../Assets/imgCardBlog.jpg" alt="Imagem" />
          <div class="cardContent">
            <div class="cardText">
              <h2>Atualização 2.0!</h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                Commodi necessitatibus officia nihil voluptatum totam voluptate
                eligendi voluptates nulla doloribus, voluptas facere? Animi eius
                culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio.
              </p>
            </div>
          </div>
          <div class="btnContainer">
            <button class="btnSeta">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
        <div class="card" onclick="openPopup()">
          <img src="../Assets/imgCardBlog.jpg" alt="Imagem" />
          <div class="cardContent">
            <div class="cardText">
              <h2>Novas funcionalidades!</h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                Commodi necessitatibus officia nihil voluptatum totam voluptate
                eligendi voluptates nulla doloribus, voluptas facere? Animi eius
                culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio.
              </p>
            </div>
          </div>
          <div class="btnContainer">
            <button class="btnSeta">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
        <div class="card" onclick="openPopup()">
          <img src="../Assets/imgCardBlog.jpg" alt="Imagem" />
          <div class="cardContent">
            <div class="cardText">
              <h2>Bienal do livro 2024</h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                Commodi necessitatibus officia nihil voluptatum totam voluptate
                eligendi voluptates nulla doloribus, voluptas facere? Animi eius
                culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio.
              </p>
            </div>
          </div>
          <div class="btnContainer">
            <button class="btnSeta">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
        <div class="card" onclick="openPopup()">
          <img src="../Assets/imgCardBlog.jpg" alt="Imagem" />
          <div class="cardContent">
            <div class="cardText">
              <h2>Livros da Collen Hover!</h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                Commodi necessitatibus officia nihil voluptatum totam voluptate
                eligendi voluptates nulla doloribus, voluptas facere? Animi eius
                culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio.
              </p>
            </div>
          </div>
          <div class="btnContainer">
            <button class="btnSeta">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
        <div class="card" onclick="openPopup()">
          <img src="../Assets/imgCardBlog.jpg" alt="Imagem" />
          <div class="cardContent">
            <div class="cardText">
              <h2>Sistema de Avaliações!</h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                Commodi necessitatibus officia nihil voluptatum totam voluptate
                eligendi voluptates nulla doloribus, voluptas facere? Animi eius
                culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio. Lorem ipsum dolor sit amet, consectetur adipisicing
                elit. Commodi necessitatibus officia nihil voluptatum totam
                voluptate eligendi voluptates nulla doloribus, voluptas facere?
                Animi eius culpa impedit ratione repellat? Eligendi, praesentium
                distinctio.
              </p>
            </div>
          </div>
          <div class="btnContainer">
            <button class="btnSeta">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>
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
