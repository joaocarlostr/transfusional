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

// Busca dados do paciente
$query_paciente = "SELECT nome_completo, nome_social FROM sth_dados_paciente WHERE id_paciente = $id_paciente";
$res_paciente = conecta_query($conexao, $query_paciente);
$dados_paciente = pg_fetch_assoc($res_paciente);
$nome_paciente_display = !empty($dados_paciente['nome_social']) ? $dados_paciente['nome_social'] : $dados_paciente['nome_completo'];

// Helper para highlight
function highlight_term($text, $term) {
    if (empty($term) || empty($text)) return htmlspecialchars($text);
    $regex = '/' . preg_quote($term, '/') . '/i';
    return preg_replace($regex, '<span style="color: #ea580c; font-weight: 800; background-color: rgba(234, 88, 12, 0.1); padding: 0 2px; border-radius: 2px;">$0</span>', htmlspecialchars($text));
}

// Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id_reacao          = filter_input(INPUT_POST, 'id_reacao', FILTER_SANITIZE_NUMBER_INT);
        $data_reacao        = $_POST["data_reacao"];
        $hora_reacao        = $_POST["hora_reacao"];
        $id_bolsa           = $_POST["id_bolsa"];
        $observacao         = trim($_POST["observacao"]);
        $num_notificacao    = $_POST["num_notificacao"];
        
        // Lógica para pegar o tipo de reação (imediata ou tardia)
        $tipo_reacao_cat = $_POST["tipo_reacao_cat"]; // 1 imed, 2 lard
        $id_tipo_reacao = null;
        
        if($tipo_reacao_cat == '1') {
            $id_tipo_reacao = $_POST["reacoes_imediatas"];
        } else {
            $id_tipo_reacao = $_POST["reacoes_tardias"];
        }

        if ($action === 'create') {
            $query = "INSERT INTO sth_reacoes_transfusionais (
                data, hora, id_bolsa, observacao, num_notificacao, tipo_reacao
            ) VALUES (
                '$data_reacao', '$hora_reacao', $id_bolsa, '$observacao', '$num_notificacao', $id_tipo_reacao
            )";
            
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Reação transfusional registrada com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao registrar reação.";
            }

        } elseif ($action === 'update' && $id_reacao) {
            $query = "UPDATE sth_reacoes_transfusionais SET 
                data = '$data_reacao', 
                hora = '$hora_reacao', 
                id_bolsa = $id_bolsa, 
                observacao = '$observacao', 
                num_notificacao = '$num_notificacao', 
                tipo_reacao = $id_tipo_reacao
                WHERE id_transfusionais = $id_reacao";

            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Reação atualizada com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao atualizar reação.";
            }
        }
        
        header("Location: crud_cadastro_reacao_transfusional.php?id_paciente=$id_paciente");
        exit;

    } elseif ($action === 'delete') {
        $id_reacao = filter_input(INPUT_POST, 'id_reacao', FILTER_SANITIZE_NUMBER_INT);
        if ($id_reacao) {
            $query = "DELETE FROM sth_reacoes_transfusionais WHERE id_transfusionais = $id_reacao";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Reação excluída com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao excluir reação.";
            }
        }
        header("Location: crud_cadastro_reacao_transfusional.php?id_paciente=$id_paciente");
        exit;
    }
}

// Mode Handling
$mode = $_GET['mode'] ?? 'list';
$edit_id = $_GET['id_reacao'] ?? null;
$edit_data = [];

// Load Edit Data
if ($mode === 'edit' && $edit_id) {
    // Need to join to know if Immediate or Late to pre-fill category radio
    $query_edit = "SELECT rt.*, tr.nome as nome_categoria FROM sth_reacoes_transfusionais rt
                   INNER JOIN sth_tipos_reacoes tr ON tr.id_reacao = rt.tipo_reacao
                   WHERE id_transfusionais = $edit_id";
    $result_edit = conecta_query($conexao, $query_edit);
    if(pg_num_rows($result_edit) > 0){
        $edit_data = pg_fetch_assoc($result_edit);
    }
}

// Load Dropdowns
// Bolsas do Paciente
$query_bolsas = "SELECT cb.id_bolsa, cb.num_bolsa, h.sigla 
                 FROM sth_cadastro_bolsa cb 
                 INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                 WHERE cb.id_paciente = $id_paciente
                 ORDER BY cb.data_transfusao DESC";
$result_bolsas_select = conecta_query($conexao, $query_bolsas);

// Tipos Reações
$query_imediata = "SELECT * FROM sth_tipos_reacoes WHERE nome = 'Reações Imediatas' ORDER BY descricao";
$res_imediata = conecta_query($conexao, $query_imediata);

$query_tardia = "SELECT * FROM sth_tipos_reacoes WHERE nome = 'Reações Tardias' ORDER BY descricao";
$res_tardia = conecta_query($conexao, $query_tardia);


// List Data
$result_reacoes = null;
if ($mode === 'list') { 
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $where_clause = "WHERE cb.id_paciente = $id_paciente";

    if (!empty($search)) {
        $search_safe = pg_escape_string($conexao, $search);
        $where_clause .= " AND (tr.sigla ILIKE '%$search_safe%' OR cb.num_bolsa ILIKE '%$search_safe%' OR rt.num_notificacao ILIKE '%$search_safe%')";
    }

    $query_list = "SELECT rt.*, cb.num_bolsa, tr.nome as categoria, tr.sigla, tr.descricao 
                   FROM sth_reacoes_transfusionais rt
                   INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = rt.id_bolsa
                   INNER JOIN sth_tipos_reacoes tr ON tr.id_reacao = rt.tipo_reacao
                   $where_clause
                   ORDER BY rt.data DESC, rt.hora DESC";
    $result_reacoes = conecta_query($conexao, $query_list);
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
    <title>Gerenciar Reações - <?php echo $nome_paciente_display; ?></title>
    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        .container-crud { max-width: 1400px; margin: 90px auto 120px auto; padding: 0 20px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        /* Orange Theme for Reaction */
        .card-header-crud { background: linear-gradient(135deg, #e65100 0%, #ff9800 100%); color: #fff !important; padding: 20px 30px; font-size: 1.3rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .card-body-crud { padding: 30px; }
        
        .btn-add { background-color: #e65100; color: #fff !important; border: none; font-weight: 600; padding: 6px 18px; font-size: 0.9rem; border-radius: 30px; text-decoration: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-add:hover { background-color: #bf360c; transform: translateY(-2px); text-decoration: none; }
        
        .table-custom th { background-color: #6c757d !important; border-bottom: 2px solid #e9ecef; color: #fff !important; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; border-radius: 0; }
        .table-custom td { vertical-align: middle; color: #212529; font-size: 0.9rem; }
        
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: none; margin: 0 2px; cursor: pointer; }
        .btn-edit { background-color: #e3f2fd; color: #0d47a1; }
        .btn-delete { background-color: #ffebee; color: #c62828; }

        label.required::after { content: " *"; color: #e53e3e; }
        .form-control:focus, .select2-container--default .select2-selection--single:focus { border-color: #e65100; box-shadow: 0 0 0 3px rgba(230, 81, 0, 0.1); }
        .form-group { margin-bottom: 12px; }
        
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ced4da; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }

        .btn-action-container { display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px; }
        .btn-action-container .btn { position: relative !important; top: auto !important; left: auto !important; opacity: 1 !important; transform: none !important; display: inline-flex !important; align-items: center; justify-content: center; }
        .btn-action-container .btn-add { background-color: #e65100 !important; color: #fff !important; border: 1px solid #e65100 !important; }
        
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
                   <i class="fas fa-file-medical mr-2"></i> Reações Transfusionais - <span class="patient-info-badge"><?php echo $nome_paciente_display; ?></span>
                </div>
                <div>
                    <a href="crud_paciente.php" class="btn btn-sm btn-light text-warning mr-2" style="border-radius: 20px; font-weight:600; color: #e65100 !important;"><i class="fas fa-arrow-left"></i> Voltar</a>
                    <a href="crud_cadastro_reacao_transfusional.php?mode=create&id_paciente=<?php echo $id_paciente; ?>" class="btn-add"><i class="fas fa-plus mr-1"></i> Nova Reação</a>
                </div>
            </div>

            <!-- Search -->
            <div class="p-4 border-bottom bg-light">
                <form method="GET" action="crud_cadastro_reacao_transfusional.php">
                    <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-lg border-0 shadow-sm" placeholder="Pesquisar por: Nº Bolsa, Notificação ou Sigla da Reação" value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 30px 0 0 30px; padding-left: 25px; height: 50px; font-size: 0.9rem;">
                        <input type="hidden" name="mode" value="list">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4 shadow-sm" type="submit" style="border-radius: 0 30px 30px 0; background-color: #e65100; border-color: #e65100; height: 50px;">
                                <i class="fas fa-search mr-2"></i>
                            </button>
                            <?php if(!empty($search)): ?>
                                <a href="crud_cadastro_reacao_transfusional.php?id_paciente=<?php echo $id_paciente; ?>" class="btn btn-outline-danger ml-3 shadow-sm d-flex align-items-center justify-content-center" style="border-radius: 30px; padding: 0 20px; height: 50px; border: 1px solid #dc3545; color: #dc3545; background: #fff;" title="Limpar Filtros">
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
                                <th>Data</th>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th class="text-left">Descrição</th>
                                <th>Sigla</th>
                                <th>Notificação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_reacoes && pg_num_rows($result_reacoes) > 0): ?>
                                <?php while ($row = pg_fetch_assoc($result_reacoes)): ?>
                                    <tr class="text-center">
                                        <td class="pl-4 text-left font-weight-bold" style="color:#e65100;"><?php echo highlight_term($row['num_bolsa'], $search); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['data'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($row['hora'])); ?></td>
                                        <td>
                                            <?php if($row['categoria'] == 'Reações Imediatas'): ?>
                                                <span class="badge badge-danger">Imediata</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Tardia</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-left"><?php echo htmlspecialchars($row['descricao']); ?></td>
                                        <td><?php echo highlight_term($row['sigla'], $search); ?></td>
                                        <td><?php echo highlight_term($row['num_notificacao'], $search); ?></td>
                                        <td>
                                            <a href="crud_cadastro_reacao_transfusional.php?mode=edit&id_reacao=<?php echo $row['id_transfusionais']; ?>&id_paciente=<?php echo $id_paciente; ?>" class="action-btn btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                            <button onclick="confirmarExclusao(<?php echo $row['id_transfusionais']; ?>)" class="action-btn btn-delete" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">Nenhuma reação transfusional registrada para este paciente.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- FORM MODE -->
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-<?php echo $mode === 'create' ? 'plus-circle' : 'edit'; ?> mr-2"></i> <?php echo $mode === 'create' ? 'Nova Reação Transfusional' : 'Editar Reação'; ?></span>
            </div>
            <div class="card-body card-body-crud">
                <form id="formReacao" method="POST" action="crud_cadastro_reacao_transfusional.php">
                    <input type="hidden" name="action" value="<?php echo $mode === 'create' ? 'create' : 'update'; ?>">
                    <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
                    <?php if($edit_id): ?>
                        <input type="hidden" name="id_reacao" value="<?php echo $edit_id; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="id_bolsa" class="required">Nº da Bolsa</label>
                            <select name="id_bolsa" id="id_bolsa" class="form-control select2" required style="width: 100%;">
                                <option value="">Selecione a Bolsa</option>
                                <?php 
                                // Reset pointer of result_bolsas_select if needed but we're reloading page so it's fine
                                while ($row = pg_fetch_assoc($result_bolsas_select)): 
                                    $selected = ($edit_data && $edit_data['id_bolsa'] == $row['id_bolsa']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['id_bolsa']; ?>" <?php echo $selected; ?>>
                                        <?php echo $row['num_bolsa'] . ' - ' . $row['sigla']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group" style="display:flex; align-items:flex-end; padding-bottom:10px;">
                             <div class="h5 mb-0 text-muted"><small>Paciente:</small> <strong><?php echo $nome_paciente_display; ?></strong></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="data_reacao" class="required">Data</label>
                            <input type="date" name="data_reacao" id="data_reacao" class="form-control" max="<?php echo date('Y-m-d'); ?>" required value="<?php echo $edit_data['data'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="hora_reacao" class="required">Hora</label>
                            <input type="time" name="hora_reacao" id="hora_reacao" class="form-control" required value="<?php echo $edit_data['hora'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="num_notificacao" class="required">Nº Notificação</label>
                            <input type="text" name="num_notificacao" id="num_notificacao" class="form-control" maxlength="20" required value="<?php echo $edit_data['num_notificacao'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="required d-block">Tipo de Reação</label>
                            <div class="btn-group btn-group-toggle pt-1" data-toggle="buttons">
                                <label class="btn btn-outline-secondary <?php if($edit_data && $edit_data['nome_categoria'] == 'Reações Imediatas') echo 'active'; ?>">
                                    <input type="radio" name="tipo_reacao_cat" id="cat_1" value="1" required <?php if($edit_data && $edit_data['nome_categoria'] == 'Reações Imediatas') echo 'checked'; ?>> Imediata
                                </label>
                                <label class="btn btn-outline-secondary <?php if($edit_data && $edit_data['nome_categoria'] == 'Reações Tardias') echo 'active'; ?>">
                                    <input type="radio" name="tipo_reacao_cat" id="cat_2" value="2" required <?php if($edit_data && $edit_data['nome_categoria'] == 'Reações Tardias') echo 'checked'; ?>> Tardia
                                </label>
                            </div>
                        </div>
                        <div class="col-md-8 form-group">
                            <label class="required">Especificação da Reação</label>
                            
                            <select name="reacoes_imediatas" id="reacoes_imediatas" class="form-control" style="display:none;">
                                <option value="">Selecione uma reação imediata</option>
                                <?php while ($row = pg_fetch_assoc($res_imediata)): 
                                     $selected = ($edit_data && $edit_data['tipo_reacao'] == $row['id_reacao']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['id_reacao']; ?>" <?php echo $selected; ?>>
                                        <?php echo $row['sigla'] . ' - ' . $row['descricao']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <select name="reacoes_tardias" id="reacoes_tardias" class="form-control" style="display:none;">
                                <option value="">Selecione uma reação tardia</option>
                                <?php while ($row = pg_fetch_assoc($res_tardia)): 
                                     $selected = ($edit_data && $edit_data['tipo_reacao'] == $row['id_reacao']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['id_reacao']; ?>" <?php echo $selected; ?>>
                                        <?php echo $row['sigla'] . ' - ' . $row['descricao']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="observacao" class="required">Observação</label>
                            <textarea name="observacao" id="observacao" rows="3" class="form-control" required><?php echo $edit_data['observacao'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <div class="btn-action-container">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='crud_cadastro_reacao_transfusional.php?id_paciente=<?php echo $id_paciente; ?>'">Cancelar</button>
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
        <input type="hidden" name="id_reacao" id="deleteId">
        <input type="hidden" name="id_paciente" value="<?php echo $id_paciente; ?>">
    </form>

    <?php include 'includes/footer.php'; ?>
    <script type="text/javascript" src="js/script.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            
            // Logic for showing correct select
            function updateReacaoSelect() {
                var cat = $('input[name="tipo_reacao_cat"]:checked').val();
                if (cat == '1') {
                    $('#reacoes_imediatas').show().attr('required', true);
                    $('#reacoes_tardias').hide().removeAttr('required').val('');
                } else if (cat == '2') {
                     $('#reacoes_tardias').show().attr('required', true);
                     $('#reacoes_imediatas').hide().removeAttr('required').val('');
                } else {
                    $('#reacoes_imediatas').hide().removeAttr('required');
                    $('#reacoes_tardias').hide().removeAttr('required');
                }
            }

            $('input[name="tipo_reacao_cat"]').change(updateReacaoSelect);
            
            // Trigger on load if editing
            updateReacaoSelect();
        });

        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Isso removerá esta reação transfusional!",
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
