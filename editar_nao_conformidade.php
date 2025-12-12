<?php
include "database.php";
include "function.php";

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_nao_conformidade = $_POST['id_nao_conformidade'];
    $nao_conformidade = trim($_POST['nao_conformidade']);
    $tipo = $_POST['tipo'];
    $status = isset($_POST['status']) ? 'ativo' : 'inativo'; // Assuming 'ativo'/'inativo' or boolean logic matches DB

    // Update Query
    $query = "UPDATE sth_nao_conformidade SET 
              nao_conformidade = '$nao_conformidade',
              tipo = '$tipo',
              status = '$status'
              WHERE id_nao_conformidade = $id_nao_conformidade";
    
    if (conecta_query($conexao, $query)) {
        $_SESSION['msg_exclusao'] = "Registro atualizado com sucesso!"; // Reusing the session key or creating new
        header("Location: grid_nao_conformidades.php");
        exit;
    } else {
        $msg = "Erro ao atualizar registro.";
    }
}

// Fetch Data for form
if ($id) {
    $query_select = "SELECT * FROM sth_nao_conformidade WHERE id_nao_conformidade = $id";
    $result = conecta_query($conexao, $query_select);
    $row = pg_fetch_assoc($result);
    if (!$row) {
        header("Location: grid_nao_conformidades.php");
        exit;
    }
} else if ($_SERVER["REQUEST_METHOD"] != "POST") {
   // If no ID and not post, redirect
   header("Location: grid_nao_conformidades.php");
   exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Editar - Não Conformidade</title>
    <style>
        /* Override global background image */
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        
        .container-edit { max-width: 800px; margin: 90px auto; }
        .card-edit { border-radius: 12px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .card-header-edit { background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); color: #fff !important; padding: 15px 30px; font-weight: 600; font-size: 1.2rem; }
        .btn-modern { padding: 10px 25px; border-radius: 8px; font-weight: 600; text-transform: uppercase; border: none; font-size: 0.85rem; }
        .btn-success-modern { background-color: #28a745; color: white; }
        .btn-cancel-modern { background-color: #6c757d; color: white; text-decoration: none; display: inline-block; padding: 10px 25px; text-align: center;}
        .form-label { font-weight: 600; color: #4a5568; font-size: 0.9rem; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container container-edit">
        <div class="card card-edit">
            <div class="card-header card-header-edit">
                <i class="fas fa-pencil-alt mr-2"></i> Editar Não Conformidade
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <input type="hidden" name="id_nao_conformidade" value="<?php echo $row['id_nao_conformidade']; ?>">
                    
                    <div class="form-group">
                        <label class="form-label required">Tipo</label>
                        <select name="tipo" class="form-control" required>
                            <option value="">Selecione</option>
                            <?php
                            $options = [
                                "Prescrição médica", 
                                "Ficha de controle de sinais vitais", 
                                "Livro de registro de hemocomponentes", 
                                "Formulário de devolução de hemocomponentes", 
                                "Outros"
                            ];
                            foreach ($options as $opt) {
                                $selected = ($row['tipo'] == $opt) ? 'selected' : '';
                                echo "<option value='$opt' $selected>$opt</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Não Conformidade</label>
                        <input type="text" name="nao_conformidade" class="form-control" value="<?php echo htmlspecialchars($row['nao_conformidade']); ?>" required maxlength="200">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="status" name="status" value="ativo" <?php echo ($row['status'] == 'ativo') ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="status">Ativo</label>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn-modern btn-success-modern"><i class="fas fa-save mr-2"></i> Salvar</button>
                        <a href="grid_nao_conformidades.php" class="btn-modern btn-cancel-modern"><i class="fas fa-times mr-2"></i> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
