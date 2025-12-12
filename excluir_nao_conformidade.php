<?php
include "database.php";
include "function.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_nao_conformidade'])) {
    
    $id = filter_input(INPUT_POST, 'id_nao_conformidade', FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        $query = "DELETE FROM sth_nao_conformidade WHERE id_nao_conformidade = $id";
        
        if (conecta_query($conexao, $query)) {
            $_SESSION['msg_exclusao'] = "Registro excluído com sucesso!";
        } else {
            $_SESSION['msg_exclusao'] = "Erro ao excluir registro. Verifique se não há dependências.";
        }
    }
}

header("Location: grid_nao_conformidades.php");
exit;
?>