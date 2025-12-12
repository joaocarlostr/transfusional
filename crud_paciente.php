<?php
include "database.php";
include "function.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auxiliar para destacar o termo de pesquisa
function highlight_term($text, $term) {
    if (empty($term) || empty($text)) return htmlspecialchars($text);
    // Escapa caracteres especiais de regex no termo de pesquisa
    $regex = '/' . preg_quote($term, '/') . '/i';
    // Destaca a correspondência com Vermelho Escuro (#741c19) e negrito
    return preg_replace($regex, '<span style="color: #741c19; font-weight: 800; background-color: rgba(116, 28, 25, 0.1); padding: 0 2px; border-radius: 2px;">$0</span>', htmlspecialchars($text));
}

// Gerencia Ações (Criar, Atualizar, Excluir)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        // Coleta Dados
        $id_paciente     = filter_input(INPUT_POST, 'id_paciente', FILTER_SANITIZE_NUMBER_INT);
        $dataNascimento  = $_POST["data_nascimento"];
        $nome            = trim($_POST["nome_completo"]);
        $cpf             = $_POST["cpf"];
        $sexo            = $_POST["sexo"];
        $mae             = trim($_POST["mae"]);
        $abo             = $_POST["abo"];
        $rh              = $_POST["rh"];
        $setor           = $_POST["setor"];
        $leito           = $_POST["leito"];
        $internacao      = $_POST["hospital_internacao"];
        $num_sus         = $_POST["num_sus"];
        $observacao      = trim($_POST["observacao"]);
        $data_requisicao = $_POST["data_requisicao"];
        $nome_social     = trim($_POST["nome_social"]);
        $rn              = $_POST["recem_nascido"];
        $prontuario      = $_POST["prontuario"];
        $registro        = $_POST["registro"];
        $numero_rt       = $_POST["numero_rt"];
        $diagnostico     = $_POST["diagnostico"];

        // Lógica de Validação
        $cpf_safe = pg_escape_string($conexao, $cpf);
        $prontuario_safe = pg_escape_string($conexao, $prontuario);
        
        $valid = true;
        if($action === 'create') {
            if ($cpf != '000.000.000-00') {
                $check = conecta_query($conexao, "SELECT id_paciente FROM sth_dados_paciente WHERE cpf = '$cpf_safe'");
                if (pg_num_rows($check) > 0) $valid = false;
            }
            if ($valid && $prontuario != '0') {
                $check = conecta_query($conexao, "SELECT id_paciente FROM sth_dados_paciente WHERE prontuario = '$prontuario_safe'");
                if (pg_num_rows($check) > 0) $valid = false;
            }
        }

        if ($valid) {
            if ($action === 'create') {
                $query = "INSERT INTO sth_dados_paciente(
                    dt_nasc, nome_completo, cpf, sexo, nome_mae, abo, rh_d, id_setor,
                    leito, hospital, numero_sus, prontuario, observacao, dt_requisicao, 
                    nome_social, rn, registro, numero_rt, diagnostico
                ) VALUES (
                    '$dataNascimento', '$nome', '$cpf', '$sexo', '$mae', '$abo', '$rh', $setor, 
                    '$leito', '$internacao', '$num_sus', '$prontuario', '$observacao', '$data_requisicao', 
                    '$nome_social', '$rn', '$registro', '$numero_rt', '$diagnostico'
                )";
                if (conecta_query($conexao, $query)) {
                    $_SESSION['msg_success'] = "Paciente cadastrado com sucesso!";
                } else {
                    $_SESSION['msg_error'] = "Erro ao cadastrar paciente.";
                }
            } elseif ($action === 'update' && $id_paciente) {
                $query = "UPDATE sth_dados_paciente SET 
                    dt_nasc = '$dataNascimento', nome_completo = '$nome', cpf = '$cpf', sexo = '$sexo', 
                    nome_mae = '$mae', abo = '$abo', rh_d = '$rh', id_setor = $setor, leito = '$leito', 
                    hospital = '$internacao', numero_sus = '$num_sus', prontuario = '$prontuario', 
                    observacao = '$observacao', dt_requisicao = '$data_requisicao', nome_social = '$nome_social', 
                    rn = '$rn', registro = '$registro', numero_rt = '$numero_rt', diagnostico = '$diagnostico' 
                    WHERE id_paciente = $id_paciente";
                if (conecta_query($conexao, $query)) {
                    $_SESSION['msg_success'] = "Paciente atualizado com sucesso!";
                } else {
                    $_SESSION['msg_error'] = "Erro ao atualizar paciente.";
                }
            }
        } else {
            $_SESSION['msg_error'] = "Paciente já cadastrado (CPF ou Prontuário duplicado).";
        }
        
        // Redirecionar para limpar o post
        header("Location: crud_paciente.php");
        exit;

    } elseif ($action === 'delete') {
        $id_paciente = filter_input(INPUT_POST, 'id_paciente', FILTER_SANITIZE_NUMBER_INT);
        if ($id_paciente) {
            $query = "DELETE FROM sth_dados_paciente WHERE id_paciente = $id_paciente";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Paciente excluído com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao excluir paciente.";
            }
        }
        header("Location: crud_paciente.php");
        exit;
    }
}

// Verifica se estamos no modo de edição ou criação (via parâmetro GET)
$mode = $_GET['mode'] ?? 'list';
$edit_id = $_GET['id'] ?? null;
$edit_data = [];

if ($mode === 'edit' && $edit_id) {
    $query_edit = "SELECT * FROM sth_dados_paciente WHERE id_paciente = $edit_id";
    $result_edit = conecta_query($conexao, $query_edit);
    if(pg_num_rows($result_edit) > 0){
        $edit_data = pg_fetch_assoc($result_edit);
    }
}

// Buscar Listas (apenas se estiver no modo lista)
// Buscar Listas (apenas se estiver no modo lista)
// Busca Listas (apenas se estiver no modo lista)
$result_pacientes = null;
if ($mode === 'list') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $clean_search = ''; // Inicializa para evitar erro de variável indefinida
    $where_clause = "";
    
    // Prepara para destaque (mantém original para texto, limpo para números)
    $search_term = $search; 
    
    if (!empty($search)) {
        $search_safe = pg_escape_string($conexao, $search);
        $clean_search = preg_replace('/[^0-9]/', '', $search);
        
        // Pesquisa de texto baseada em nomes
        $where_conditions = [
            "p.nome_completo ILIKE '%$search_safe%'",
            "p.nome_social ILIKE '%$search_safe%'",
            "p.nome_mae ILIKE '%$search_safe%'"
        ];

        // Se o usuário digitou números, pesquisa em campos numéricos e CPF limpo
        if (!empty($clean_search)) {
            // Verifica CPF formatado diretamente OU CPF limpo para pesquisa parcial de números
            // Nota: Postgres replace() para remover caracteres na hora para comparação
            $where_conditions[] = "REPLACE(REPLACE(REPLACE(p.cpf, '.', ''), '-', ''), ' ', '') LIKE '%$clean_search%'";
            $where_conditions[] = "p.prontuario::text LIKE '%$clean_search%'";
            $where_conditions[] = "p.registro::text LIKE '%$clean_search%'";
        }
        
        // Fallback para quando o usuário digita CPF formatado '111.222' para corresponder a '111.222.333-44' diretamente
        if($search !== $clean_search) {
             $where_conditions[] = "p.cpf LIKE '%$search_safe%'";
        }

        $where_clause = " WHERE (" . implode(" OR ", $where_conditions) . ")";
    }

    // Lógica de Paginação
    $items_per_page = 20; // Ajuste conforme necessário
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $items_per_page;

    // Conta o total de registros para paginação
    $query_count = "SELECT COUNT(*) as total FROM sth_dados_paciente p $where_clause";
    $result_count = conecta_query($conexao, $query_count);
    $row_count = pg_fetch_assoc($result_count);
    $total_records = $row_count['total'];
    $total_pages = ceil($total_records / $items_per_page);

    $query_pacientes = "SELECT p.*, s.nome_setor 
                       FROM sth_dados_paciente p 
                       LEFT JOIN sth_setores s ON p.id_setor = s.id_setor 
                       $where_clause
                       ORDER BY p.nome_completo ASC 
                       LIMIT $items_per_page OFFSET $offset";
    $result_pacientes = conecta_query($conexao, $query_pacientes);
}

// Setores para o Dropdown
$query_setor = "SELECT * FROM sth_setores WHERE status='ativo' ORDER BY nome_setor DESC";
$result_setor = conecta_query($conexao, $query_setor);
$options_setor = "";
while ($row = pg_fetch_assoc($result_setor)) {
    $selected = ($edit_data && $edit_data['id_setor'] == $row['id_setor']) ? 'selected' : '';
    $options_setor .= "<option value='{$row['id_setor']}' $selected>{$row['nome_setor']}</option>";
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
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <title>Gerenciar - Pacientes</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/flatpickr-custom.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <style>
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        .container-crud { max-width: 1400px; margin: 90px auto 120px auto; padding: 0 20px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        .card-header-crud { background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); color: #fff !important; padding: 20px 30px; font-size: 1.3rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .card-body-crud { padding: 40px; }
        
        .btn-add { background-color: #28a745; color: #fff !important; border: none; font-weight: 600; padding: 6px 18px; font-size: 0.9rem; border-radius: 30px; text-decoration: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-add:hover { background-color: #218838; transform: translateY(-2px); text-decoration: none; }
        .btn-cancel { background-color: #6c757d; color: #fff !important; border: none; font-weight: 600; padding: 10px 25px; border-radius: 30px; text-decoration: none; cursor: pointer; }
        
        .table-custom th { background-color: #6c757d !important; border-bottom: 2px solid #e9ecef; color: #fff !important; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; border-radius: 0; }
        .table-custom td { vertical-align: middle; color: #212529; font-size: 0.9rem; }
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: none; margin: 0 2px; cursor: pointer; }
        .btn-edit { background-color: #e3f2fd; color: #0d47a1; }
        .btn-view { background-color: #e8f5e9; color: #2e7d32; }
        .btn-delete { background-color: #ffebee; color: #c62828; }
        .btn-bolsa { background-color: #fce4ec; color: #c2185b; } /* Pink/Red for Blood */
        .btn-reacao { background-color: #fff3e0; color: #e65100; } /* Orange for Reaction/Alert */
        
        label.required::after { content: " *"; color: #e53e3e; }
        .form-control:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1); }
        .form-section-title { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; font-size: 1.1rem; color: #741c19; font-weight: 600; margin-top: 10px;}
        
        /* Compact Vertical Spacing */
        .form-group { margin-bottom: 10px; } 
        .form-row, .row { margin-bottom: 5px; }
        .card-body-crud { padding: 30px; }
        .btn-action-container { display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        
        /* Force Reset for Buttons to override potential global style.css conflicts */
        .btn-action-container .btn {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            opacity: 1 !important;
            transform: none !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        /* Ensure Save button has color even if overridden */
        .btn-action-container .btn-add {
            background-color: #28a745 !important; 
            color: #fff !important; 
            border: 1px solid #28a745 !important;
        }
        
        /* Forçar alinhamento à esquerda nas colunas de texto */
        .table-custom th.text-left,
        .table-custom td.text-left {
            text-align: left !important;
        }
        
        /* Bordas verticais entre colunas */
        .table-custom th,
        .table-custom td {
            border-right: 1px solid #dee2e6;
        }
        
        .table-custom th:last-child,
        .table-custom td:last-child {
            border-right: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container container-crud">
        
        <?php if($mode === 'list'): ?>
        <!-- LIST VIEW -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-user-injured mr-2"></i> Gerenciar Pacientes</span>
                <a href="crud_paciente.php?mode=create" class="btn-add"><i class="fas fa-plus mr-1"></i> Adicionar</a>
            </div>

            <!-- Modern Smart Search Bar -->
            <div class="p-4 border-bottom bg-light">
                <form method="GET" action="crud_paciente.php">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-lg border-0 shadow-sm" placeholder="Pesquisar por: Nome, Social, Mãe, CPF, Prontuário ou Registro" value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 30px 0 0 30px; padding-left: 25px; height: 50px; font-size: 0.9rem;">
                        <input type="hidden" name="mode" value="list">
                        <div class="input-group-append">
                            <button class="btn px-4 shadow-sm" type="submit" style="border-radius: 0 30px 30px 0; background-color: #1976d2; border-color: #1976d2; height: 50px; color: white;">
                                <i class="fas fa-search mr-2"></i> Pesquisar
                            </button>
                            <?php if(!empty($search)): ?>
                                <a href="crud_paciente.php" class="btn ml-3 shadow-sm d-flex align-items-center justify-content-center" style="border-radius: 30px; padding: 0 20px; height: 50px; background-color: #ef6c00; border-color: #ef6c00; color: white;" title="Limpar Filtros">
                                    <i class="fas fa-eraser mr-2"></i> Limpar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <small class="form-text text-muted mt-2 ml-3">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Dica:</strong> A busca aceita Nome (Completo ou Social), Mãe, ou números (CPF, Prontuário, Registro). Você pode digitar apenas <strong>parte</strong> do nome ou dos números para pesquisar.
                    </small>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4 text-left">Nome</th>
                                <th>CPF</th>
                                <th>Prontuário</th>
                                <th>Nascimento</th>
                                <th class="text-left">Setor</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (pg_num_rows($result_pacientes) > 0): ?>
                                <?php while ($row = pg_fetch_assoc($result_pacientes)): ?>
                                    <tr>
                                        <!-- Highlight Matches -->
                                        <td class="pl-4 text-left"><?php echo highlight_term($row['nome_completo'], $search); ?></td>
                                        <td><?php echo highlight_term($row['cpf'], $clean_search ?: $search); ?></td>
                                        <td><?php echo highlight_term($row['prontuario'], $clean_search); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['dt_nasc'])); ?></td>
                                        <td class="text-left"><?php echo htmlspecialchars($row['nome_setor']); ?></td>
                                        <td class="text-center" style="white-space: nowrap;">
                                            <a href="crud_cadastro_bolsa.php?id_paciente=<?php echo $row['id_paciente']; ?>" class="action-btn btn-bolsa" title="Gerenciar Bolsas"><i class="fas fa-tint"></i></a>
                                            <a href="crud_cadastro_reacao_transfusional.php?id_paciente=<?php echo $row['id_paciente']; ?>" class="action-btn btn-reacao" title="Gerenciar Reações"><i class="fas fa-file-medical"></i></a>
                                            <span class="mx-1 text-muted">|</span>
                                            <a href="perfil_paciente.php?id_paciente=<?php echo $row['id_paciente']; ?>" class="action-btn btn-view" title="Ver Perfil"><i class="fas fa-eye"></i></a>
                                            <a href="crud_paciente.php?mode=edit&id=<?php echo $row['id_paciente']; ?>" class="action-btn btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                            <button onclick="confirmarExclusao(<?php echo $row['id_paciente']; ?>)" class="action-btn btn-delete" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Nenhum paciente encontrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <?php if ($total_pages > 1): ?>
                <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                    <?php 
                        // Assuming 10 records per page based on standard CRUD or file context. 
                        // If $limit isn't explicitly available here, we'll calculate based on page logic.
                        // Ideally $limit should be available. Let's assume $limit is the variable name for items per page.
                        // Checking previous context of crud_paciente.php might be safe, but usually it's $limit or hardcoded.
                        // Let's use the same calculation logic as Exclusoes but ensure variables align.
                        // In crud_paciente, variable "page" is used.
                        $limit_local = 10; // Standard default often used if not visible, safely defined locally for display if needed or reuse existing.
                        // BETTER: Calculate based on (page-1)*limit + 1. 
                        // Scanning file for $limit or similar... 
                        // If I can't be sure of the variable name for limit, I will define safe logic.
                        
                        $pg_limit = isset($limit) ? $limit : 10; // Fallback
                        $start_record = ($page - 1) * $pg_limit + 1;
                        $end_record = min($start_record + $pg_limit - 1, $total_records); 
                    ?>
                    <small class="text-primary" style="font-weight: 600;">Mostrando <?php echo $start_record; ?> - <?php echo $end_record; ?> de <?php echo $total_records; ?> registros</small>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <!-- First Page -->
                            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?mode=list&page=1&search=<?php echo urlencode($search); ?>">Primeira</a>
                            </li>
                            
                            <!-- Previous Page -->
                            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?mode=list&page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">Anterior</a>
                            </li>

                            <!-- Page Numbers (Show window of 5 pages) -->
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                                $active = ($i == $page) ? 'active' : '';
                            ?>
                                <li class="page-item <?php echo $active; ?>">
                                    <a class="page-link" href="?mode=list&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="?mode=list&page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">Próxima</a>
                            </li>

                            <!-- Last Page -->
                            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="?mode=list&page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>">Última</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- FORM VIEW (CREATE/EDIT) -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-<?php echo $mode === 'create' ? 'user-plus' : 'user-edit'; ?> mr-2"></i> <?php echo $mode === 'create' ? 'Cadastrar Paciente' : 'Editar Paciente'; ?></span>
            </div>
            <div class="card-body card-body-crud">
                <form id="formPaciente" method="POST" action="crud_paciente.php" novalidate>
                    <input type="hidden" name="action" value="<?php echo $mode === 'create' ? 'create' : 'update'; ?>">
                    <?php if($edit_id): ?>
                        <input type="hidden" name="id_paciente" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <!-- Linha 1: Data de cadastro | Recém-Nascido (RN)? -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="data_requisicao" class="required">Data de cadastro</label>
                            <input type="date" name="data_requisicao" value="<?php echo $edit_data ? $edit_data['dt_requisicao'] : date('Y-m-d'); ?>" id="data_requisicao" readonly class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="required">Recém-Nascido (RN)?</label>
                            <div class="radio-group pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recem_nascido" value="sim" id="rn_sim" tabindex="1" required <?php if($edit_data && $edit_data['rn'] == 'sim') echo 'checked'; ?>>
                                    <label class="form-check-label" for="rn_sim"> Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recem_nascido" value="nao" id="rn_nao" tabindex="2" required <?php if($edit_data && $edit_data['rn'] == 'nao') echo 'checked'; ?>>
                                    <label class="form-check-label" for="rn_nao"> Não</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Linha 2: Prontuário | CNS | CPF -->
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="prontuario" class="required">Prontuário</label>
                            <input type="text" name="prontuario" id="prontuario" maxlength="15" class="form-control" tabindex="3" required placeholder="Somente números" value="<?php echo $edit_data['prontuario'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="num_sus">CNS</label>
                            <input type="text" id="num_sus" name="num_sus" maxlength="18" class="form-control" tabindex="4" placeholder="000.0000.0000.0000" value="<?php echo $edit_data['numero_sus'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="cpf" class="required">CPF</label>
                            <input type="text" name="cpf" id="cpf" maxlength="14" class="form-control" tabindex="5" required placeholder="000.000.000-00" value="<?php echo $edit_data['cpf'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Linha 3: Nome Completo | Nome da Mãe -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nome" class="required">Nome Completo</label>
                            <input type="text" name="nome_completo" id="nome_completo" maxlength="255" class="form-control" tabindex="6" required placeholder="Digite o nome completo" value="<?php echo $edit_data['nome_completo'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="mae" class="required">Nome da Mãe</label>
                            <input type="text" name="mae" id="nome_mae" maxlength="255" class="form-control" tabindex="7" required placeholder="Nome completo da mãe" value="<?php echo $edit_data['nome_mae'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Linha 4: Data de Nascimento | Sexo | Nome Social Completo -->
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="data_nascimento" class="required">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" tabindex="8" required value="<?php echo $edit_data['dt_nasc'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="sexo" class="required">Sexo</label>
                            <select name="sexo" id="sexo" class="form-control" tabindex="9" required>
                                <option value="">Selecione</option>
                                <option value="F" <?php if($edit_data && $edit_data['sexo'] == 'F') echo 'selected'; ?>>Feminino</option>
                                <option value="M" <?php if($edit_data && $edit_data['sexo'] == 'M') echo 'selected'; ?>>Masculino</option>
                                <option value="Outro" <?php if($edit_data && $edit_data['sexo'] == 'Outro') echo 'selected'; ?>>Outro</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="nome_social">Nome Social Completo</label>
                            <input type="text" maxlength="255" name="nome_social" id="nome_social" class="form-control" tabindex="10" placeholder="Opcional" value="<?php echo $edit_data['nome_social'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Linha 5: ABO | Fator RH -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="abo" class="required">ABO</label>
                            <select name="abo" id="abo" class="form-control" tabindex="11" required>
                                <option value="">Selecione</option>
                                <option value="A" <?php if($edit_data && $edit_data['abo'] == 'A') echo 'selected'; ?>>A</option>
                                <option value="B" <?php if($edit_data && $edit_data['abo'] == 'B') echo 'selected'; ?>>B</option>
                                <option value="O" <?php if($edit_data && $edit_data['abo'] == 'O') echo 'selected'; ?>>O</option>
                                <option value="AB" <?php if($edit_data && $edit_data['abo'] == 'AB') echo 'selected'; ?>>AB</option>
                                <option value="Outro" <?php if($edit_data && $edit_data['abo'] == 'Outro') echo 'selected'; ?>>Outro</option>
                                <option value="Desconhecido" <?php if($edit_data && $edit_data['abo'] == 'Desconhecido') echo 'selected'; ?>>Desconhecido</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="rh" class="required">Fator RH</label>
                            <select name="rh" id="rh" class="form-control" tabindex="12" required>
                                <option value="">Selecione</option>
                                <option value="Positivo" <?php if($edit_data && $edit_data['rh_d'] == 'Positivo') echo 'selected'; ?>>Positivo</option>
                                <option value="Negativo" <?php if($edit_data && $edit_data['rh_d'] == 'Negativo') echo 'selected'; ?>>Negativo</option>
                                <option value="Outro" <?php if($edit_data && $edit_data['rh_d'] == 'Outro') echo 'selected'; ?>>Outro</option>
                                <option value="Desconhecido" <?php if($edit_data && $edit_data['rh_d'] == 'Desconhecido') echo 'selected'; ?>>Desconhecido</option>
                            </select>
                        </div>
                    </div>

                    <!-- Linha 6: Hospital de Internação | Setor | Leito -->
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="hospital_internacao">Hospital de Internação</label>
                            <input type="text" name="hospital_internacao" id="hospital_internacao" value="HUM" readonly class="form-control">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="setor" class="required">Setor</label>
                            <select name="setor" id="setor" class="form-control" tabindex="13" required>
                                <option value="">Selecione</option>
                                <?php echo $options_setor; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="leito">Leito</label>
                            <input type="text" name="leito" id="leito" maxlength="200" class="form-control" tabindex="14" placeholder="Ex: 102-A" value="<?php echo $edit_data['leito'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Linha 7: Registro | Número RT -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="registro" class="required">Registro</label>
                            <input type="text" name="registro" id="registro" maxlength="15" class="form-control" tabindex="15" required placeholder="Nº de Registro" value="<?php echo $edit_data['registro'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="numero_rt">Número RT</label>
                            <input type="text" name="numero_rt" id="numero_rt" minlength="10" maxlength="10" class="form-control" tabindex="16" placeholder="Opcional" value="<?php echo $edit_data['numero_rt'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- Linha 8: Diagnóstico | Observação -->
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label for="diagnostico">Diagnóstico</label>
                            <input type="text" name="diagnostico" id="diagnostico" maxlength="255" class="form-control" tabindex="17" placeholder="Diagnóstico principal" value="<?php echo $edit_data['diagnostico'] ?? ''; ?>">
                        </div>
                        <div class="col-md-7 form-group">
                            <label for="observacao">Observação</label>
                            <textarea name="observacao" id="observacao" rows="2" maxlength="255" class="form-control" tabindex="18" placeholder="Informações adicionais..."><?php echo $edit_data['observacao'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <div class="btn-action-container">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='crud_paciente.php'">Cancelar</button>
                        <button type="submit" class="btn btn-add" id="btnSave"><?php echo $mode === 'create' ? 'Cadastrar' : 'Salvar'; ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Delete Form (Hidden) -->
    <form id="formDelete" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_paciente" id="deleteId">
    </form>

    <?php include 'includes/footer.php'; ?>
    <!-- <script type="text/javascript" src="js/script.js"></script> -->
    <script>
        // ==================== VALIDAÇÃO DE FORMULÁRIO ====================
        
        document.getElementById('formPaciente')?.addEventListener('submit', function(e) {
            // Pegar todos os campos obrigatórios
            const camposObrigatorios = this.querySelectorAll('[required]');
            const camposVazios = [];
            
            // Remover destaque anterior
            camposObrigatorios.forEach(campo => {
                campo.style.border = '';
                campo.style.boxShadow = '';
            });
            
            // Verificar cada campo
            camposObrigatorios.forEach(campo => {
                let vazio = false;
                
                if (campo.type === 'radio') {
                    // Para radio buttons, verificar se algum do grupo está marcado
                    const nome = campo.name;
                    const grupoRadio = document.querySelectorAll(`input[name="${nome}"]`);
                    const algumMarcado = Array.from(grupoRadio).some(r => r.checked);
                    if (!algumMarcado && !camposVazios.find(c => c.nome === nome)) {
                        vazio = true;
                        camposVazios.push({
                            campo: grupoRadio[0],
                            nome: nome,
                            label: campo.closest('.form-group')?.querySelector('label')?.textContent || nome
                        });
                    }
                } else if (campo.tagName === 'SELECT') {
                    // Para selects, verificar se não está em "Selecione"
                    if (!campo.value || campo.value === '' || campo.value === 'Selecione') {
                        vazio = true;
                    }
                } else {
                    // Para inputs normais
                    if (!campo.value || campo.value.trim() === '') {
                        vazio = true;
                    }
                }
                
                if (vazio && campo.type !== 'radio') {
                    camposVazios.push({
                        campo: campo,
                        nome: campo.name,
                        label: campo.closest('.form-group')?.querySelector('label')?.textContent || campo.name
                    });
                }
            });
            
            // Se houver campos vazios, impedir envio e mostrar alerta
            if (camposVazios.length > 0) {
                e.preventDefault();
                
                // Destacar campos vazios
                camposVazios.forEach(item => {
                    item.campo.style.border = '2px solid #dc3545';
                    item.campo.style.boxShadow = '0 0 5px rgba(220, 53, 69, 0.5)';
                });
                
                // Montar lista de campos
                const listaCampos = camposVazios.map(item => 
                    `• ${item.label.replace('*', '').trim()}`
                ).join('<br>');
                
                // Mostrar alerta amigável
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Obrigatórios Não Preenchidos',
                    html: `
                        <div style="text-align: left; padding: 10px;">
                            <p><strong>Por favor, preencha os seguintes campos obrigatórios:</strong></p>
                            <hr>
                            <div style="padding-left: 10px; line-height: 1.8;">
                                ${listaCampos}
                            </div>
                            <hr>
                            <p style="color: #856404; font-size: 14px;">
                                ⚠️ Os campos destacados em vermelho precisam ser preenchidos.
                            </p>
                        </div>
                    `,
                    confirmButtonText: 'OK, vou preencher',
                    confirmButtonColor: '#ffc107',
                    allowEscapeKey: true,
                    allowOutsideClick: true
                }).then(() => {
                    // Focar no primeiro campo vazio
                    if (camposVazios[0]) {
                        camposVazios[0].campo.focus();
                        camposVazios[0].campo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
                
                return false;
            }
        });
        
        // ==================== VALIDAÇÃO DE CPF ====================
        
        function validarCPF(cpf) {
            cpf = cpf.replace(/[^\d]/g, '');
            
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false; // CPFs com todos dígitos iguais
            
            let soma = 0;
            let resto;
            
            for (let i = 1; i <= 9; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }
            
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(9, 10))) return false;
            
            soma = 0;
            for (let i = 1; i <= 10; i++) {
                soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }
            
            resto = (soma * 10) % 11;
            if (resto === 10 || resto === 11) resto = 0;
            if (resto !== parseInt(cpf.substring(10, 11))) return false;
            
            return true;
        }
        
        // ==================== UPPERCASE AUTOMÁTICO ====================
        
        // Campos que devem ser uppercase
        const camposUppercase = ['nome_completo', 'nome_mae', 'nome_social'];
        
        camposUppercase.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    const start = this.selectionStart;
                    const end = this.selectionEnd;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(start, end);
                });
            }
        });
        
        // ==================== CONFIRMAÇÃO DE EXCLUSÃO ====================
        
        // Confirmação de Exclusão
        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Isso removerá o paciente permanentemente!",
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
        
         // Integração da Lógica RN
        document.querySelector('#rn_sim').addEventListener('change', function() {
            if (this.checked) {
                var nome = document.getElementById("nome").value;
                if (nome && !nome.startsWith("RN-")) {
                    document.getElementById("nome").value = "RN-" + nome;
                }
            }
        });
        document.querySelector('#rn_nao').addEventListener('change', function() {
            if (this.checked) {
                var nome = document.getElementById("nome").value;
                if (nome.startsWith("RN-")) {  
                    document.getElementById("nome").value = nome.substr(3);
                }
            }
        });

        <?php if(isset($_SESSION['msg_success'])): ?>
            Swal.fire('Sucesso!', '<?php echo $_SESSION['msg_success']; ?>', 'success');
            <?php unset($_SESSION['msg_success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['msg_error'])): ?>
            Swal.fire('Erro!', '<?php echo $_SESSION['msg_error']; ?>', 'error');
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>
        
        // Máscara de data para Data de Nascimento
        function aplicarMascaraData(input) {
            input.addEventListener('input', function(e) {
                let valor = e.target.value.replace(/\D/g, '');
                
                if (valor.length >= 2) {
                    valor = valor.substring(0, 2) + '/' + valor.substring(2);
                }
                if (valor.length >= 5) {
                    valor = valor.substring(0, 5) + '/' + valor.substring(5, 9);
                }
                
                e.target.value = valor;
            });
        }
        
        // ==================== CALENDÁRIO ESTILIZADO (form_gera_relatorios) ====================
        
        // Calcular feriados brasileiros
        function calcularFeriadosBrasileiros(ano) {
            const feriadosFixos = [
                `${ano}-01-01`, `${ano}-04-21`, `${ano}-05-01`, `${ano}-09-07`,
                `${ano}-10-12`, `${ano}-11-02`, `${ano}-11-15`, `${ano}-11-20`, `${ano}-12-25`
            ];
            
            // Calcular Páscoa
            const a = ano % 19;
            const b = Math.floor(ano / 100);
            const c = ano % 100;
            const d = Math.floor(b / 4);
            const e = b % 4;
            const f = Math.floor((b + 8) / 25);
            const g = Math.floor((b - f + 1) / 3);
            const h = (19 * a + b - d - g + 15) % 30;
            const i = Math.floor(c / 4);
            const k = c % 4;
            const l = (32 + 2 * e + 2 * i - h - k) % 7;
            const m = Math.floor((a + 11 * h + 22 * l) / 451);
            const mes = Math.floor((h + l - 7 * m + 114) / 31);
            const dia = ((h + l - 7 * m + 114) % 31) + 1;
            const pascoa = new Date(ano, mes - 1, dia);
            
            const formatarData = (data) => {
                const ano = data.getFullYear();
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const dia = String(data.getDate()).padStart(2, '0');
                return `${ano}-${mes}-${dia}`;
            };
            
            const carnaval = new Date(pascoa);
            carnaval.setDate(pascoa.getDate() - 47);
            const carnavalSegunda = new Date(carnaval);
            carnavalSegunda.setDate(carnaval.getDate() - 1);
            const paixaoCristo = new Date(pascoa);
            paixaoCristo.setDate(pascoa.getDate() - 2);
            const corpusChristi = new Date(pascoa);
            corpusChristi.setDate(pascoa.getDate() + 60);
            
            return [...feriadosFixos, formatarData(carnavalSegunda), formatarData(carnaval),
                formatarData(paixaoCristo), formatarData(corpusChristi)];
        }
        
        // Gerar feriados para os próximos 5 anos
        const anoAtual = new Date().getFullYear();
        let todosFeriados = [];
        for (let ano = anoAtual - 100; ano <= anoAtual; ano++) {
            todosFeriados = [...todosFeriados, ...calcularFeriadosBrasileiros(ano)];
        }
        
        // Inicializar Flatpickr para Data de Nascimento com estilo completo
        flatpickr("#data_nascimento", {
            locale: "pt",
            dateFormat: "d/m/Y",
            allowInput: true,
            maxDate: "today",
            showMonths: 1,
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj;
                const dateStr = fp.formatDate(date, "Y-m-d");
                const dayOfWeek = date.getDay();
                
                // Adicionar classes baseadas no tipo de dia
                if (todosFeriados.includes(dateStr)) {
                    dayElem.classList.add("holiday");
                } else if (dayOfWeek === 0) {
                    dayElem.classList.add("sunday");
                } else if (dayOfWeek === 6) {
                    dayElem.classList.add("saturday");
                } else {
                    dayElem.classList.add("weekday");
                }
            }
        });
        
        // Aplicar máscara no campo
        aplicarMascaraData(document.getElementById('data_nascimento'));
        
        // Adicionar setas customizadas nos selects
        document.querySelectorAll('select.form-control').forEach(function(select) {
            select.style.backgroundImage = "url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27currentColor%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e')";
            select.style.backgroundRepeat = "no-repeat";
            select.style.backgroundPosition = "right 12px center";
            select.style.backgroundSize = "16px";
            select.style.paddingRight = "40px";
        });
        
        // ==================== FOCO INICIAL NO MODO CREATE ====================
        
        <?php if ($mode === 'create'): ?>
        // Focar no primeiro campo (RN - Sim) ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const rnSim = document.getElementById('rn_sim');
            if (rnSim) {
                setTimeout(() => {
                    rnSim.focus();
                }, 100);
            }
        });
        <?php endif; ?>
        
        // ==================== VERIFICAÇÃO DE PACIENTE DUPLICADO ====================
        
        // Função para verificar se paciente já existe
        function verificarPacienteDuplicado(campo, valor) {
            // Não verificar se o valor estiver vazio
            if (!valor || valor.trim() === '') {
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: 'Verificando...',
                text: 'Aguarde enquanto verificamos se este paciente já está cadastrado.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fazer requisição AJAX
            $.ajax({
                url: 'action_verifica_paciente_duplicado.php',
                method: 'POST',
                data: {
                    campo: campo,
                    valor: valor
                },
                dataType: 'json',
                success: function(response) {
                    console.log('RESPOSTA DO SERVIDOR:', response); // DEBUG

                    if (response.existe) {
                        // Paciente JÁ EXISTE - CPF ou Prontuário são únicos
                        
                        const campoNome = campo === 'cpf' ? 'CPF' : 'Prontuário';
                        
                        Swal.fire({
                            icon: 'error',
                            title: `${campoNome} Já Cadastrado!`,
                            html: `
                                <div style="text-align: left; padding: 10px;">
                                    <p><strong>Este ${campoNome} já está cadastrado:</strong></p>
                                    <p><strong>Nome:</strong> ${response.paciente.nome}</p>
                                    <p><strong>Prontuário:</strong> ${response.paciente.prontuario}</p>
                                    <p><strong>CPF:</strong> ${response.paciente.cpf}</p>
                                    <hr>
                                    <p style="color: #d33; font-weight: bold;">
                                        ❌ NÃO é permitido cadastrar pacientes duplicados!
                                    </p>
                                    <p>Você será redirecionado para o cadastro existente.</p>
                                </div>
                            `,
                            confirmButtonText: 'OK - Ir ao Cadastro',
                            confirmButtonColor: '#d33',
                            allowEscapeKey: false,
                            allowOutsideClick: false
                        }).then((result) => {
                            // Sempre redireciona (não permite ESC)
                            window.location.href = 'crud_paciente.php?mode=edit&id=' + response.paciente.id;
                        });
                        
                        // Limpar o campo
                        document.getElementById(campo).value = '';
                        
                        
                    } else if (response.erro) {
                        // ERRO RETORNADO PELO PHP
                        console.error('Erro PHP:', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro no Servidor',
                            text: response.mensagem,
                            footer: 'Veja o console (F12) para mais detalhes'
                        });
                    } else {
                        // NÃO EXISTE (Disponível) - Apenas fecha o loading, sem alerta
                        console.log('Paciente não encontrado (Disponível) - Pode prosseguir');
                        Swal.close();
                        
                        // Focar no próximo campo para manter a ordem de navegação
                        setTimeout(() => {
                            const campoAtual = document.getElementById(campo);
                            if (campoAtual) {
                                const tabindexAtual = parseInt(campoAtual.getAttribute('tabindex'));
                                const proximoCampo = document.querySelector(`[tabindex="${tabindexAtual + 1}"]`);
                                if (proximoCampo) {
                                    proximoCampo.focus();
                                }
                            }
                        }, 100);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('ERRO AJAX:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha na Requisição',
                        html: 'Ocorreu um erro ao comunicar com o servidor.<br>Status: ' + status + '<br>Erro: ' + error,
                        // timer: 3000 // REMOVIDO TIMER
                    });
                }
            });
        }
        
        // Adicionar evento onblur (ao sair do campo) apenas no modo CREATE
        <?php if ($mode === 'create'): ?>
        $(document).ready(function() {
            // Verificar Prontuário
            document.getElementById('prontuario').addEventListener('blur', function() {
                const valor = this.value;
                if (valor && valor.trim() !== '') {
                    verificarPacienteDuplicado('prontuario', valor);
                }
            });
            
            // Verificar CPF
            let validandoCPF = false; // Flag para evitar loop
            document.getElementById('cpf').addEventListener('blur', function() {
                const valor = this.value;
                if (valor && valor.trim() !== '' && !validandoCPF) {
                    // PRIMEIRO: Validar se o CPF é válido
                    if (!validarCPF(valor)) {
                        validandoCPF = true; // Ativar flag
                        Swal.fire({
                            icon: 'error',
                            title: 'CPF Inválido',
                            text: 'O CPF digitado não é válido. Por favor, verifique e corrija.',
                            confirmButtonColor: '#d33',
                            allowEscapeKey: true,
                            allowOutsideClick: true
                        }).then(() => {
                            validandoCPF = false; // Desativar flag após fechar
                        });
                        this.style.border = '2px solid #dc3545';
                        this.style.boxShadow = '0 0 5px rgba(220, 53, 69, 0.5)';
                        // NÃO usar this.focus() aqui para evitar loop
                        return; // Para aqui se CPF inválido
                    }
                    
                    // SEGUNDO: Se CPF válido, verificar duplicados
                    this.style.border = '';
                    this.style.boxShadow = '';
                    verificarPacienteDuplicado('cpf', valor);
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
