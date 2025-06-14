# 💻: VERSAMI WEB

Esta aplicação é um dos componentes do Trabalho de Conclusão de Curso (TCC) técnico da **ETEC de São Paulo**, desenvolvida pelos alunos **Julia Belchior**, **Matheus Canesso**, **Thamiris Fernandes** e **Ygor Silva**.

## :warning: Atenção
Antes de iniciar a configuração da aplicação web, é **obrigatório executar todos os scripts de banco de dados** que estão disponíveis no repositório [`versami-documentacao`](https://github.com/Versami-ETESP/versami-documentacao)

## ☑️ Pré-requisitos

* Um editor de código de sua preferencia (VS Code, Notepad++, etc.).
* Xampp instalado na máquina (com suporte a PHP).
* Git instalado (opcional, mas recomendado).
* Banco de dados SQL Server com os scripts do projeto executados.

## :gear: Como configurar o projeto

### 1. Clonar o repositório

Se você **possui o Git instalado**, abra o terminal na pasta `C:\xampp\htdocs` e execute:

```bash
git clone https://github.com/Versami-ETESP/versami-web
```

Caso **não possua o Git**, acesse a página do repositório, clique no botão `Code` e depois em `Download ZIP`.

![image](https://github.com/user-attachments/assets/16fea5ed-b497-4368-bb49-43070a74cf43)

Depois de baixado, **extraia o arquivo ZIP** para a pasta `C:\xampp\htdocs`

---

### 2. Configurar a conexão com o banco de dados

* Acesse a pasta extraída
* Abra o arquivo `config.php` no seu editor de código
* Na linha 2, altere o valor de `$serverName` para o nome do seu servidor do SQL Server
* Nas linhas 5 e 6, atualize as credenciais (Uid e PWD) para as do seu SQL Server

![image](https://github.com/user-attachments/assets/5a17ccbb-37ca-4e04-a62e-1685304780ec)

```php
$serverName = "<SUA_MAQUINA>\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "versami",
    "Uid" => "<SEU_USUARIO>",
    "PWD" => "<SUA_SENHA>",
    "CharacterSet" => "UTF-8"
);
```

---

### :white_check_mark: Pronto!

Com a conexão configurada, siga os passos abaixo para executar a aplicação:

* Vá até a pasta: `C:\xampp`
* Abra o arquivo xampp-control (dê dois cliques)
![image](https://github.com/user-attachments/assets/a9b3c4e4-71ef-4005-9db6-3437b5d56583)

* Clique em Start no módulo Apache
![image](https://github.com/user-attachments/assets/234ce4b6-2b36-4e98-a3f8-e18755fd6c49)

* Abra o navegador e acesse: `http://localhost/versami-web/Index/index.php`
![image](https://github.com/user-attachments/assets/b149da8b-55e1-46bc-a5a4-00b87d972ced)
