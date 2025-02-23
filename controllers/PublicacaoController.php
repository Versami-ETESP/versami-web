<?php
require_once '../core/Conexao.php';
class PublicacaoController
{
    private $pdo = null;
    private $table = "tblPublicacao";

    public function __construct()
    {
        $connect = new Conexao();
        $this->pdo = $connect->connectDB();
    }

    /**
     * @param int $id
     * @param string $content
     * O metodo 'sendPost' recebe $id e $content e faz um insert no banco de dados na tabela Publicacao.
     * Caso haja algum erro de conexão ele encerra o script
     */

    public function sendPost($content, $id)
    {

        date_default_timezone_set('America/Sao_Paulo');


        if (empty($content) || empty($id)) {
            $result = false;
            die(json_encode($result));
        }

        $datapub = new DateTime();

        try {
            $sql = $this->pdo->prepare("INSERT INTO $this->table (conteudo, dataPublic, usuario) VALUES (:cont, :datapub, :user);");

            $sql->bindValue(":cont", $content);
            $sql->bindValue(":datapub", $datapub);
            $sql->bindValue(":user", $id);

            $sql->execute();

            $result = true;
            die(json_encode($result));

        } catch (PDOException $e) {
            error_log("Erro de conexão " . $e->getMessage());
        }

    }

    /**
     * 'restorePosts' faz um select no banco de dados para resgatar as postagens de um usuario especifico.
     * Ele recebe os valores de inicio e o limite da solicitação e retorna um vetor com as publicações. Caso nao haja retorna um vetor em branco.
     * 
     * @param int $limite
     * @param int $inicio
     * @param string id
     */

    public function restorePosts($id, $inicio, $limite)
    {

        try {
            $sql = $this->pdo->prepare("SELECT * FROM $this->table WHERE usuario = :user ORDER BY idPublicacao DESC OFFSET :qtd ROWS FETCH NEXT :limite ROWS ONLY;"); // seleciona os posts do usuario e limita a quantidade de post por requisição, para carregar aos poucos na pagina

            $sql->bindValue(":user", $id);
            $sql->bindValue(":limite", $limite, PDO::PARAM_INT);  // PDO::PARAM_INT define que o parametro :limite seja lido no sql com inteiro
            $sql->bindValue(":qtd", $inicio, PDO::PARAM_INT);
            $sql->execute();

            $consulta = $sql->fetchAll(PDO::FETCH_ASSOC); 

            if(count($consulta) > 0){
                $result = $consulta; 
                die(json_encode($result));
            } else {
                $result = []; 
                die(json_encode($result));
            }

        } catch (PDOException $e) {
            error_log("Erro de conexão " . $e->getMessage());
        }

    }
}