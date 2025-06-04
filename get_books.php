<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                l.idLivro, 
                l.nomeLivro, 
                l.imgCapa,
                a.nomeAutor
            FROM tblLivro l
            LEFT JOIN tblAutor a ON l.idAutor = a.idAutor
            ORDER BY l.nomeLivro";

    $stmt = sqlsrv_query($conn, $sql);
    
    if ($stmt === false) {
        throw new Exception("Erro na consulta: " . print_r(sqlsrv_errors(), true));
    }

    $books = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $book = [
            'idLivro' => $row['idLivro'],
            'nomeLivro' => $row['nomeLivro'],
            'nomeAutor' => $row['nomeAutor']
        ];
        
        // Adiciona a imagem em base64 se existir
        if (!empty($row['imgCapa'])) {
            $book['imagem_base64'] = base64_encode($row['imgCapa']);
        }
        
        $books[] = $book;
    }

    echo json_encode(['success' => true, 'data' => $books]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>