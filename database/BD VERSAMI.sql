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
fotoUsuario varchar(250),
fotoCapa varchar(250))
go
create table tblAdmin(
idAdmin int not null primary key identity(1,1),
nome char(80),
data_nasc date,
email varchar(80),
senha varchar(250),
arroba_usuario varchar(30),
permissao_admin bit 
)
go
create table tblAutor(
idAutor int not null primary key identity(1,1),
nomeAutor char(80),
descAutor varchar(250)
)
go
create table tblGenero(
idGenero int not null primary key identity(1,1),
nomeGenero char(80),
descGenero varchar(250)
)
go
create table tblLivro(
idLivro int not null primary key identity(1,1),
nomeLivro char(80),
descLivro varchar(250),
imgCapa varchar(250),
genero int foreign key references tblGenero(idGenero)
)
go
create table tblLivro_Autor(
Livro int foreign key references tblLivro(idLivro),
Autor int foreign key references tblAutor(idAutor)
)
go
create table tblPublicacao(
idPublicacao int not null primary key identity(1,1),
conteudo varchar(1000),
titulo varchar(80),
dataPublic datetime,
nota int,
usuario int foreign key references tblUsuario(idUsuario),
livro int foreign key references tblLivro(idLivro)
)
go
create table tblInteracao(
idInteracao int not null primary key identity(1,1),
comentario varchar(248),
data_coment datetime,
curtida bit,
publicacao int foreign key references tblPublicacao(idPublicacao),
usuario int foreign key references tblUsuario(idUsuario)
)
go
create table tblUsuario_Seguindo(
usuario int foreign key references tblUsuario (idUsuario),
usuario_seguindo int foreign key references tblUsuario(idUsuario),
primary key (usuario, usuario_seguindo)
)
go
create table tblSeguindo(
idSeguindo int not null primary key identity(1,1),
usuario int foreign key references tblUsuario(idUsuario)
)
go
create table tblSeguidor(
idSeguidor int not null primary key identity(1,1),
usuario int foreign key references tblUsuario(idUsuario)
)


--use master
--drop database versami
