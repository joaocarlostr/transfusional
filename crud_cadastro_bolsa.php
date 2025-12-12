<?php
include "database.php";
include "function.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se há um paciente selecionado
if (!isset($_GET['id_paciente']) && !isset($_POST['id_paciente'])) {
    header("Location: crud_paciente.php");
    exit;
}

$id_paciente = $_GET['id_paciente'] ?? $_POST['id_paciente'];

// Busca dados do paciente para exibir no cabeçalho
$query_paciente = "SELECT nome_completo, nome_social FROM sth_dados_paciente WHERE id_paciente = $id_paciente";
$res_paciente = conecta_query($conexao, $query_paciente);
$dados_paciente = pg_fetch_assoc($res_paciente);
$nome_paciente_display = !empty($dados_paciente['nome_social']) ? $dados_paciente['nome_social'] : $dados_paciente['nome_completo'];

// Auxiliar para destacar o termo de pesquisa
function highlight_term($text, $term) {
    if (empty($term) || empty($text)) return htmlspecialchars($text);
    $regex = '/' . preg_quote($term, '/') . '/i';
    return preg_replace($regex, '<span style="color: #741c19; font-weight: 800; background-color: rgba(116, 28, 25, 0.1); padding: 0 2px; border-radius: 2px;">$0</span>', htmlspecialchars($text));
}

// Gerencia Ações (Criar, Atualizar, Excluir)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id_bolsa           = filter_input(INPUT_POST, 'id_bolsa', FILTER_SANITIZE_NUMBER_INT);
        $id_hemocomponente  = $_POST["hemocomponente"];
        $numero_bolsa       = $_POST["numero_bolsa"];
        $setor_livro        = !empty($_POST["setor_livro"]) ? $_POST["setor_livro"] : 'NULL';
        $num_sus_bolsa      = $_POST["num_sus_bolsa"];
        $data_transfusao    = $_POST["data_transfusao"];
        $horario_inicio     = $_POST["horario_inicio"];
        $dt_saida           = $_POST["dt_saida"];
        $observacao         = trim($_POST["observacao"]);
        
        $notvisa = isset($_POST["notvisa"]) ? 'ok' : '';
        $shtnovo = isset($_POST["shtnovo"]) ? 'ok' : '';
        $obito   = isset($_POST["obito"]) ? 'sim' : '';
        
        $reserva  = $_POST["reserva"];
        $aliquota = $_POST["aliquota"];

        $valid = true;
        // Validação simples de unicidade da bolsa pode ser feita aqui se necessário

        if ($valid) {
            if ($action === 'create') {
                $query = "INSERT INTO sth_cadastro_bolsa (
                    id_paciente, id_hemocomponente, num_bolsa, id_livro_setor, num_sus, 
                    data_transfusao, horario_inicio, dt_saida, observacao, 
                    notvisa, shtnovo, obito, reserva, aliquota
                ) VALUES (
                    $id_paciente, $id_hemocomponente, '$numero_bolsa', $setor_livro, '$num_sus_bolsa',
                    '$data_transfusao', '$horario_inicio', '$dt_saida', '$observacao',
                    '$notvisa', '$shtnovo', '$obito', '$reserva', '$aliquota'
                )";

                if (conecta_query($conexao, $query)) {
                    $_SESSION['msg_success'] = "Bolsa cadastrada com sucesso!";
                } else {
                    $_SESSION['msg_error'] = "Erro ao cadastrar bolsa.";
                }

            } elseif ($action === 'update' && $id_bolsa) {
                $query = "UPDATE sth_cadastro_bolsa SET 
                    id_hemocomponente = $id_hemocomponente, 
                    num_bolsa = '$numero_bolsa', 
                    id_livro_setor = $setor_livro, 
                    num_sus = '$num_sus_bolsa', 
                    data_transfusao = '$data_transfusao', 
                    horario_inicio = '$horario_inicio', 
                    dt_saida = '$dt_saida', 
                    observacao = '$observacao', 
                    notvisa = '$notvisa', 
                    shtnovo = '$shtnovo', 
                    obito = '$obito', 
                    reserva = '$reserva', 
                    aliquota = '$aliquota'
                    WHERE id_bolsa = $id_bolsa";

                if (conecta_query($conexao, $query)) {
                    $_SESSION['msg_success'] = "Bolsa atualizada com sucesso!";
                } else {
                    $_SESSION['msg_error'] = "Erro ao atualizar bolsa.";
                }
            }
        }
        
        header("Location: crud_cadastro_bolsa.php?id_paciente=$id_paciente");
        exit;

    } elseif ($action === 'delete') {
        $id_bolsa = filter_input(INPUT_POST, 'id_bolsa', FILTER_SANITIZE_NUMBER_INT);
        if ($id_bolsa) {
            // Verifica dependências (ex: Reações Transfusionais)
            $check_reacao = conecta_query($conexao, "SELECT id_transfusionais FROM sth_reacoes_transfusionais WHERE id_bolsa = $id_bolsa");
            if (pg_num_rows($check_reacao) > 0) {
                 $_SESSION['msg_error'] = "Não é possível excluir: Existem reações transfusionais vinculadas a esta bolsa.";
            } else {
                $query = "DELETE FROM sth_cadastro_bolsa WHERE id_bolsa = $id_bolsa";
                if (conecta_query($conexao, $query)) {
                    $_SESSION['msg_success'] = "Bolsa excluída com sucesso!";
                } else {
                    $_SESSION['msg_error'] = "Erro ao excluir bolsa.";
                }
            }
        }
        header("Location: crud_cadastro_bolsa.php?id_paciente=$id_paciente");
        exit;
    }
}

// Configurações de Modo (List / Create / Edit)
$mode = $_GET['mode'] ?? 'list';
$edit_id = $_GET['id_bolsa'] ?? null;
$edit_data = [];

// Carrega dados para Edição
if ($mode === 'edit' && $edit_id) {
    $query_edit = "SELECT * FROM sth_cadastro_bolsa WHERE id_bolsa = $edit_id";
    $result_edit = conecta_query($conexao, $query_edit);
    if(pg_num_rows($result_edit) > 0){
        $edit_data = pg_fetch_assoc($result_edit);
    }
}

// Carrega Listas para Dropdowns
$query_hemocomponente = "SELECT * FROM sth_hemocomponentes ORDER BY sigla";
$result_hemocomponente = conecta_query($conexao, $query_hemocomponente);

$query_setor_ativo = "SELECT * FROM sth_setores WHERE status='ativo' ORDER BY nome_setor DESC";
$result_setor_ativo = conecta_query($conexao, $query_setor_ativo);

$query_setor_inativo = "SELECT * FROM sth_setores WHERE status='' ORDER BY nome_setor DESC";
$result_setor_inativo = conecta_query($conexao, $query_setor_inativo);


// Busca Listagem de Bolsas
$result_bolsas = null;
if ($mode === 'list') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clause = "WHERE cb.id_paciente = $id_paciente";
    
    if (!empty($search)) {
        $search_safe = pg_escape_string($conexao, $search);
        // Busca por Num Bolsa ou Sigla do Hemocomponente
        $where_clause .= " AND (cb.num_bolsa ILIKE '%$search_safe%' OR h.sigla ILIKE '%$search_safe%' OR cb.num_sus ILIKE '%$search_safe%')";
    }

    // Paginação
    $items_per_page = 15;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $items_per_page;

    $query_count = "SELECT COUNT(*) as total FROM sth_cadastro_bolsa cb 
                    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente 
                    $where_clause";
    $result_count = conecta_query($conexao, $query_count);
    $row_count = pg_fetch_assoc($result_count);
    $total_records = $row_count['total'];
    $total_pages = ceil($total_records / $items_per_page);

    $query_bolsas = "SELECT cb.*, h.sigla, h.descricao as hemo_desc 
                     FROM sth_cadastro_bolsa cb 
                     INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                     $where_clause
                     ORDER BY cb.data_transfusao DESC, cb.horario_inicio DESC
                     LIMIT $items_per_page OFFSET $offset";
    $result_bolsas = conecta_query($conexao, $query_bolsas);
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="css/style.css">
    <title>Gerenciar Bolsas - <?php echo $nome_paciente_display; ?></title>
    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        .container-crud { max-width: 1400px; margin: 90px auto 120px auto; padding: 0 20px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        .card-header-crud { background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); color: #fff !important; padding: 20px 30px; font-size: 1.3rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .card-body-crud { padding: 30px; }
        
        .btn-add { background-color: #28a745; color: #fff !important; border: none; font-weight: 600; padding: 6px 18px; font-size: 0.9rem; border-radius: 30px; text-decoration: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-add:hover { background-color: #218838; transform: translateY(-2px); text-decoration: none; }
        
        .table-custom th { background-color: #6c757d !important; border-bottom: 2px solid #e9ecef; color: #fff !important; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; border-radius: 0; }
        .table-custom td { vertical-align: middle; color: #212529; font-size: 0.9rem; }
        
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: none; margin: 0 2px; cursor: pointer; }
        .btn-edit { background-color: #e3f2fd; color: #0d47a1; }
        .btn-delete { background-color: #ffebee; color: #c62828; }

        label.required::after { content: " *"; color: #e53e3e; }
        .form-control:focus, .select2-container--default .select2-selection--single:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1); }
        .form-group { margin-bottom: 12px; }
        
        /* Select2 Style Overrides to match Bootstrap 4 */
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ced4da; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }

        .btn-action-container { display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .btn-action-container .btn {
             position: relative !important; top: auto !important; left: auto !important; opacity: 1 !important; transform: none !important; display: inline-flex !important; align-items: center; justify-content: center;
        }
        .btn-action-container .btn-add { background-color: #28a745 !important; color: #fff !important; border: 1px solid #28a745 !important; }
        
        .patient-info-badge { background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 500; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container container-crud">
        
        <?php if($mode === 'list'): ?>
        <!-- LIST MODE -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <div>
                    <i class="fas fa-tint mr-2"></i> Bolsas - <span class="patient-info-badge"><?php echo $nome_paciente_display; ?></span>
                </div>
                <div>
                    <a href="crud_paciente.php" class="btn btn-sm btn-light text-danger mr-2" style="border-radius: 20px; font-weight:600;"><i class="fas fa-arrow-left"></i> Voltar</a>
                    <a href="crud_cadastro_bolsa.php?mode=create&id_paciente=<?php echo $id_paciente; ?>" class="btn-add"><i class="fas fa-plus mr-1"></i> Nova Bolsa</a>
                </div>
            </div>

            <!-- Search -->
            <div class="p-4 border-bottom bg-light">
                <form method="GET" action="crud_cadastro_bolsa.php">
                    <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-lg border-0 shadow-sm" placeholder="Pesquisar por: Nº Bolsa, Hemocomponente ou SUS" value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 30px 0 0 30px; padding-left: 25px; height: 50px; font-size: 0.9rem;">
                        <input type="hidden" name="mode" value="list">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4 shadow-sm" type="submit" style="border-radius: 0 30px 30px 0; background-color: #741c19; border-color: #741c19; height: 50px;">
                                <i class="fas fa-search mr-2"></i>
                            </button>
                            <?php if(!empty($search)): ?>
                                <a href="crud_cadastro_bolsa.php?id_paciente=<?php echo $id_paciente; ?>" class="btn btn-outline-danger ml-3 shadow-sm d-flex align-items-center justify-content-center" style="border-radius: 30px; padding: 0 20px; height: 50px; border: 1px solid #dc3545; color: #dc3545; background: #fff;" title="Limpar Filtros">
                                    <i class="fas fa-eraser"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr class="text-center">
                                <th class="pl-4 text-left">Nº Bolsa</th>
                                <th class="text-left">Hemocomponente</th>
                                <th>Nº SUS</th>
                                <th>Data Transfusão</th>
                                <th>Hora</th>
                                <th>Saída</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (pg_num_rows($result_bolsas) > 0): ?>
                                <?php while ($row = pg_fetch_assoc($result_bolsas)): ?>
                                    <tr class="text-center">
                                        <td class="pl-4 text-left font-weight-bold" style="color:#741c19;"><?php echo highlight_term($row['num_bolsa'], $search); ?></td>
                                        <td class="text-left"><?php echo highlight_term($row['sigla'], $search); ?> <small class="text-muted">- <?php echo $row['hemo_desc']; ?></small></td>
                                        <td><?php echo highlight_term($row['num_sus'], $search); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['data_transfusao'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($row['horario_inicio'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['dt_saida'])); ?></td>
                                        <td>
                                            <a href="crud_cadastro_bolsa.php?mode=edit&id_bolsa=<?php echo $row['id_bolsa']; ?>&id_paciente=<?php echo $id_paciente; ?>" class="action-btn btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                            <button onclick="confirmarExclusao(<?php echo $row['id_bolsa']; ?>)" class="action-btn btn-delete" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Nenhuma bolsa cadastrada para este paciente.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                 <?php if ($total_pages > 1): ?>
                <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando <?php echo pg_num_rows($result_bolsas); ?> de <?php echo $total_records; ?> registros</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                             <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?mode=list&page=<?php echo $i; ?>&id_paciente=<?php echo $id_paciente; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- FORM MODE (CREATE/EDIT) -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-<?php echo $mode === 'create' ? 'plus-circle' : 'edit'; ?> mr-2"></i> <?php echo $mode === 'create' ? 'Nova Bolsa' : 'Editar Bolsa'; ?></span>
            </div>
            <div class="card-body card-body-crud">
                <form id="formBolsa" method="POST" action="crud_cadastro_bolsa.php">
                    <input type="hidden" name="action" value="<?php echo $mode === 'create' ? 'create' : 'update'; ?>">
                    <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
                    <?php if($edit_id): ?>
                        <input type="hidden" name="id_bolsa" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <!-- Linha 1 -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                             <label for="hemocomponente" class="required">Hemocomponente</label>
                             <select name="hemocomponente" id="hemocomponente" class="form-control select2" required style="width: 100%;">
                                <option value="">Selecione</option>
                                <?php while ($row = pg_fetch_assoc($result_hemocomponente)): 
                                    $selected = ($edit_data && $edit_data['id_hemocomponente'] == $row['id_hemocomponente']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['id_hemocomponente']; ?>" <?php echo $selected; ?>>
                                        <?php echo $row['sigla'] . ' - ' . $row['descricao']; ?>
                                    </option>
                                <?php endwhile; ?>
                             </select>
                        </div>
                        <div class="col-md-6 form-group" style="display:flex; align-items:flex-end; padding-bottom:10px;">
                             <div class="h5 mb-0 text-muted"><small>Paciente:</small> <strong><?php echo $nome_paciente_display; ?></strong></div>
                        </div>
                    </div>

                    <!-- Linha 2 -->
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="numero_bolsa" class="required">Nº da Bolsa</label>
                            <input type="text" name="numero_bolsa" id="numero_bolsa" maxlength="13" minlength="13" class="form-control" placeholder="Ex: B123456789012" required value="<?php echo $edit_data['num_bolsa'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4 form-group">
                             <label for="setor_livro">Livro Setor</label>
                             <select name="setor_livro" id="setor_livro" class="form-control select2" style="width: 100%;">
                                <option value="">Selecione</option>
                                <optgroup label="Ativos">
                                    <?php while ($row = pg_fetch_assoc($result_setor_ativo)): 
                                         $selected = ($edit_data && $edit_data['id_livro_setor'] == $row['id_setor']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $row['id_setor']; ?>" <?php echo $selected; ?>><?php echo $row['nome_setor']; ?></option>
                                    <?php endwhile; ?>
                                </optgroup>
                                <?php if(pg_num_rows($result_setor_inativo) > 0): ?>
                                <optgroup label="Inativos">
                                    <?php while ($row = pg_fetch_assoc($result_setor_inativo)): 
                                          $selected = ($edit_data && $edit_data['id_livro_setor'] == $row['id_setor']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $row['id_setor']; ?>" <?php echo $selected; ?>><?php echo $row['nome_setor']; ?></option>
                                    <?php endwhile; ?>
                                </optgroup>
                                <?php endif; ?>
                             </select>
                        </div>
                         <div class="col-md-4 form-group">
                            <label for="num_sus_bolsa" class="required">Nº SUS da Bolsa</label>
                            <input type="text" name="num_sus_bolsa" id="num_sus_bolsa" maxlength="15" class="form-control" required value="<?php echo $edit_data['num_sus'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Linha 3 -->
                    <div class="row">
                         <div class="col-md-4 form-group">
                            <label for="data_transfusao" class="required">Data da Transfusão</label>
                            <input type="date" name="data_transfusao" id="data_transfusao" class="form-control" max="<?php echo date('Y-m-d'); ?>" required value="<?php echo $edit_data['data_transfusao'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="horario_inicio" class="required">Horário</label>
                            <input type="time" name="horario_inicio" id="horario_inicio" class="form-control" required value="<?php echo $edit_data['horario_inicio'] ?? ''; ?>">
                        </div>
                         <div class="col-md-4 form-group">
                            <label for="dt_saida" class="required">Data de Saída</label>
                            <input type="date" name="dt_saida" id="dt_saida" class="form-control" max="<?php echo date('Y-m-d'); ?>" required value="<?php echo $edit_data['dt_saida'] ?? ''; ?>">
                        </div>
                    </div>

                     <!-- Linha 4 Description -->
                     <div class="row">
                        <div class="col-md-12 form-group">
                             <label for="observacao">Observação</label>
                             <textarea name="observacao" id="observacao" rows="2" class="form-control" maxlength="255"><?php echo $edit_data['observacao'] ?? ''; ?></textarea>
                        </div>
                     </div>

                     <!-- Linha 5 Options -->
                     <div class="row bg-light p-3 rounded mb-3">
                         <div class="col-md-5 d-flex align-items-center">
                             <div class="custom-control custom-checkbox mr-4">
                                <input type="checkbox" class="custom-control-input" id="notvisa" name="notvisa" <?php if($edit_data && $edit_data['notvisa'] == 'ok') echo 'checked'; ?>>
                                <label class="custom-control-label" for="notvisa">NOTIVISA</label>
                             </div>
                             <div class="custom-control custom-checkbox mr-4">
                                <input type="checkbox" class="custom-control-input" id="shtnovo" name="shtnovo" <?php if($edit_data && $edit_data['shtnovo'] == 'ok') echo 'checked'; ?>>
                                <label class="custom-control-label" for="shtnovo">SHTNOVO</label>
                             </div>
                             <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="obito" name="obito" <?php if($edit_data && $edit_data['obito'] == 'sim') echo 'checked'; ?>>
                                <label class="custom-control-label text-danger" for="obito">ÓBITO</label>
                             </div>
                         </div>
                         <div class="col-md-7 d-flex align-items-center justify-content-end">
                             <div class="mr-5">
                                 <label class="required mr-2 font-weight-bold">Reserva:</label>
                                 <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="reserva" id="res_s" value="sim" required <?php if($edit_data && $edit_data['reserva'] == 'sim') echo 'checked'; ?>>
                                    <label class="form-check-label" for="res_s">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="reserva" id="res_n" value="nao" required <?php if(!$edit_data || $edit_data['reserva'] == 'nao') echo 'checked'; ?>>
                                    <label class="form-check-label" for="res_n">Não</label>
                                </div>
                             </div>
                             <div>
                                 <label class="required mr-2 font-weight-bold">Alíquota:</label>
                                 <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="aliquota" id="ali_s" value="sim" required <?php if($edit_data && $edit_data['aliquota'] == 'sim') echo 'checked'; ?>>
                                    <label class="form-check-label" for="ali_s">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="aliquota" id="ali_n" value="nao" required <?php if(!$edit_data || $edit_data['aliquota'] == 'nao') echo 'checked'; ?>>
                                    <label class="form-check-label" for="ali_n">Não</label>
                                </div>
                             </div>
                         </div>
                     </div>

                    <div class="btn-action-container">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='crud_cadastro_bolsa.php?id_paciente=<?php echo $id_paciente; ?>'">Cancelar</button>
                        <button type="submit" class="btn btn-add"><?php echo $mode === 'create' ? 'Cadastrar' : 'Salvar'; ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Delete Form -->
    <form id="formDelete" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_bolsa" id="deleteId">
        <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
    </form>

    <?php include 'includes/footer.php'; ?>
    <script type="text/javascript" src="js/script.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Isso removerá a bolsa permanentemente!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteId').val(id);
                    $('#formDelete').submit();
                }
            })
        }

        // Validação de Data Saída >= Data Transfusão
        $('#dt_saida').on('change', function() {
            var dtTransfusao = $('#data_transfusao').val();
            var dtSaida = $(this).val();
            if(dtTransfusao && dtSaida && dtSaida > dtTransfusao) {
                // Wait, logic in validation was: data_transfusao < data_saida -> "Data de saida não pode ser maior que a data de transfusao"?
                // Let's check original logic: "if (data_transfusao < data_saida) { error }"
                // This implies Transfusion Date MUST be >= Exit Date? 
                // USUALLY: Exit from stock (Saida) happens BEFORE or ON Transfusion.
                // So Saída <= Transfusão.
                // If Saida > Transfusao (Exit after transfusion), that's weird.
                // Original code: if (data_transfusao < data_saida) error. Correct.
                
                if (new Date(dtTransfusao) < new Date(dtSaida)) {
                    this.setCustomValidity('A data de saída do estoque não pode ser posterior à transfusão.');
                    Swal.fire('Atenção', 'A data de saída do estoque não pode ser posterior à data da transfusão.', 'warning');
                     $(this).val('');
                } else {
                    this.setCustomValidity('');
                }
            } else {
                 this.setCustomValidity('');
            }
        });

         <?php if(isset($_SESSION['msg_success'])): ?>
            Swal.fire('Sucesso!', '<?php echo $_SESSION['msg_success']; ?>', 'success');
            setTimeout(() => { window.history.replaceState(null, null, window.location.pathname + window.location.search); }, 500);
            <?php unset($_SESSION['msg_success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['msg_error'])): ?>
            Swal.fire('Erro!', '<?php echo $_SESSION['msg_error']; ?>', 'error');
            setTimeout(() => { window.history.replaceState(null, null, window.location.pathname + window.location.search); }, 500);
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
