create database versami
go
use versami
go
create table tblUsuario(
idUsuario int not null primary key identity(1,1),
nome varchar(50),
data_nasc date,
email varchar(80),
senha varchar(250),
arroba_usuario varchar(30) unique,
fotoUsuario varbinary(max),
fotoCapa varbinary(max))
go
create table tblAdmin(
idAdmin int not null primary key identity(1,1),
nome varchar(80),
data_nasc date,
email varchar(80),
senha varchar(250),
arroba_usuario varchar(30) unique,
permissao_admin bit 
)
go
create table tblAutor(
idAutor int not null primary key identity(1,1),
nomeAutor varchar(80),
descAutor varchar(250)
)
go
create table tblGenero(
idGenero int not null primary key identity(1,1),
nomeGenero varchar(80),
descGenero varchar(250)
)
go
create table tblLivro(
idLivro int not null primary key identity(1,1),
nomeLivro varchar(80),
descLivro varchar(250),
imgCapa varbinary(max)
)
go
create table tblPublicacao(
idPublicacao int not null primary key identity(1,1),
conteudo varchar(1000),
titulo varchar(80),
dataPublic DATETIME2,
nota int,
idUsuario int foreign key references tblUsuario(idUsuario),
idLivro int null foreign key references tblLivro(idLivro)
)
go
create table tblComentario(
idComentario int not null primary key identity(1,1),
comentario varchar(248),
data_coment DATETIME2,
idPublicacao int foreign key references tblPublicacao(idPublicacao),
idUsuario int foreign key references tblUsuario(idUsuario)
)
go
create table tblBlogPost(
idBlogPost int primary key identity(1,1),
titulo varchar(80),
conteudo varchar (1000),
dataPost DATETIME2,
administrador int foreign key references tblAdmin(idAdmin)
)
go
create table tblOcorrenciaBugs(
idOcorrenciaBugs int primary key identity(1,1),
conteudo varchar(250),
corrigido bit,
usuario int foreign key references tblUsuario(idUsuario),
administrador int foreign key references tblAdmin(idAdmin)
)
go
create table tblLivrosFavoritos(
idUsuario int foreign key references tblUsuario(idUsuario),
idLivro int foreign key references tblLivro(idLivro),
primary key(idUsuario, idLivro)
)
go
create table tblLikesPorPost(
idUsuario int foreign key references tblUsuario(idUsuario),
idPublicacao int foreign key references tblPublicacao(idPublicacao),
primary key(idUsuario, idPublicacao)
)
go
create table tblLikesPorComentario(
idUsuario int foreign key references tblUsuario(idUsuario),
idComentario int foreign key references tblComentario(idComentario),
primary key(idUsuario, idComentario)
)
go
create table tblGenero_Livro(
idGenero int foreign key references tblGenero(idGenero),
idLivro int foreign key references tblLivro(idLivro),
primary key(idGenero, idLivro)
)
go
create table tblSeguidores(
idSeguidor int foreign key references tblUsuario(idUsuario),
idSeguido int foreign key references tblUsuario(idUsuario),
primary key(idSeguidor, idSeguido)
)
go
create table tblLivro_Autor(
idLivro int foreign key references tblLivro(idLivro),
idAutor int foreign key references tblAutor(idAutor),
primary key(idLivro, idAutor)
)
go
ALTER TABLE tblUsuario ADD COLUMN bio_usuario VARCHAR(255) null

--use master
--drop database versami
