<?php
include "database.php";
include "function.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Actions (Create, Update, Delete)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $tipo = $_POST["tipo"];
        $nao_conformidade = trim($_POST["nao_conformidade"]);
        // Assume active by default on create or handled via DB default, but here explicit:
        // Note: The original insert did not have a status field in the form, usually defaults to active or handled in logic. 
        // We will add a status field to the modal for completeness as per CRUD pattern.
        $status = isset($_POST["status"]) ? 'ativo' : 'inativo';

        if (!empty($nao_conformidade) && !empty($tipo)) {
            // Using direct query for consistency with other CRUD files 
            $query = "INSERT INTO sth_nao_conformidade (tipo, nao_conformidade, status) VALUES ('$tipo', '$nao_conformidade', '$status')";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Não conformidade adicionada com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao adicionar não conformidade.";
            }
        }
    } elseif ($action === 'update') {
        $id_nao_conformidade = filter_input(INPUT_POST, 'id_nao_conformidade', FILTER_SANITIZE_NUMBER_INT);
        $tipo = $_POST["tipo"];
        $nao_conformidade = trim($_POST["nao_conformidade"]);
        $status = isset($_POST["status"]) ? 'ativo' : 'inativo';

        if ($id_nao_conformidade && !empty($nao_conformidade) && !empty($tipo)) {
            $query = "UPDATE sth_nao_conformidade SET tipo = '$tipo', nao_conformidade = '$nao_conformidade', status = '$status' WHERE id_nao_conformidade = $id_nao_conformidade";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Não conformidade atualizada com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao atualizar não conformidade.";
            }
        }
    } elseif ($action === 'delete') {
        $id_nao_conformidade = filter_input(INPUT_POST, 'id_nao_conformidade', FILTER_SANITIZE_NUMBER_INT);
        
        if ($id_nao_conformidade) {
            $query = "DELETE FROM sth_nao_conformidade WHERE id_nao_conformidade = $id_nao_conformidade";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Não conformidade excluída com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao excluir não conformidade.";
            }
        }
    }

    // Redirect to self to prevent resubmission
    header("Location: crud_nao_conformidade.php");
    exit;
}

// Fetch Records (Read)
$query_nc = "SELECT * FROM sth_nao_conformidade ORDER BY tipo, nao_conformidade";
$result_nc = conecta_query($conexao, $query_nc);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gerenciar - Não Conformidades</title>
    <style>
        /* Modern Overrides */
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        
        .container-crud { max-width: 1200px; margin: 90px auto 40px auto; padding: 0 15px; }
        .card-crud { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        
        .card-header-crud { 
            background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); 
            color: #fff !important; 
            padding: 15px 30px; 
            font-size: 1.3rem; 
            font-weight: 600; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .card-header-crud span { color: #fff !important; }

        /* Add Button */
        .btn-add { 
            background-color: #28a745; 
            color: #fff !important; 
            border: none; 
            font-weight: 600; 
            padding: 10px 25px; 
            border-radius: 30px; 
            transition: all 0.3s ease; 
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
        }
        .btn-add:hover { 
            background-color: #218838; 
            color: #fff !important;
            text-decoration: none; 
            transform: translateY(-2px); 
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .table-custom th { background-color: #6c757d !important; border-bottom: 2px solid #e9ecef; color: #fff !important; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; border-radius: 0; }
        .table-custom td { vertical-align: middle; color: #212529; font-size: 0.9rem; }
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: none; margin: 0 2px; transition: all 0.2s; }
        .btn-edit { background-color: #e3f2fd; color: #0d47a1; }
        .btn-edit:hover { background-color: #bbdefb; color: #0d47a1; }
        .btn-delete { background-color: #ffebee; color: #c62828; }
        .btn-delete:hover { background-color: #ffcdd2; color: #b71c1c; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background-color: #e8f5e9; color: #2e7d32; }
        .status-inactive { background-color: #ffebee; color: #c62828; }

        /* Modal Styles */
        .modal-header { background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); color: #fff; }
        .modal-title { color: #fff; font-weight: 600; }
        .btn-close { 
            filter: brightness(0) invert(1);
            opacity: 1;
            pointer-events: auto !important;
            cursor: pointer !important;
        }
        .btn-close:hover { 
            opacity: 0.75;
        }
        .btn-close:focus {
            outline: none;
            box-shadow: none;
        }
        
        /* Garantir que botões do modal sejam clicáveis */
        .modal-header .btn-close {
            position: relative;
            z-index: 1051;
            pointer-events: auto !important;
        }
        
        .modal-footer .btn,
        .modal-body .btn {
            pointer-events: auto !important;
            cursor: pointer !important;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container container-crud">
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-exclamation-triangle mr-2"></i> Gerenciar Não Conformidades</span>
                <button class="btn-add" data-toggle="modal" data-target="#modalNC" onclick="openAddModal()">
                    <i class="fas fa-plus mr-1"></i> Adicionar
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4 text-left">Tipo</th>
                                <th class="text-left">Não Conformidade</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pr-4" width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (pg_num_rows($result_nc) > 0) {
                                while ($row = pg_fetch_assoc($result_nc)) {
                                    $statusClass = $row['status'] == 'ativo' ? 'status-active' : 'status-inactive';
                                    $statusText = $row['status'] == 'ativo' ? 'Ativo' : 'Inativo';
                                    $descSafe = htmlspecialchars($row['nao_conformidade'], ENT_QUOTES);
                                    $tipoSafe = htmlspecialchars($row['tipo'], ENT_QUOTES);
                                    echo "<tr>
                                            <td class='pl-4 text-left'>{$row['tipo']}</td>
                                            <td class='text-left'>{$row['nao_conformidade']}</td>
                                            <td class='text-center'><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                                            <td class='text-center pr-4'>
                                                <button onclick='openEditModal({$row['id_nao_conformidade']}, \"{$tipoSafe}\", \"{$descSafe}\", \"{$row['status']}\")' class='action-btn btn-edit' title='Editar'><i class='fas fa-pencil-alt'></i></button>
                                                <button onclick='confirmarExclusao({$row['id_nao_conformidade']})' class='action-btn btn-delete' title='Excluir'><i class='fas fa-trash-alt'></i></button>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Nenhum registro encontrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal NC (Shared for Create/Update) -->
    <div class="modal fade" id="modalNC" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar Não Conformidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="formNC" method="POST" action="">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id_nao_conformidade" id="inputId">
                        
                        <div class="form-group">
                            <label for="inputTipo" class="font-weight-bold">Tipo <span class="text-danger">*</span></label>
                            <select class="form-control" name="tipo" id="inputTipo" required>
                                <option value="">Selecione</option>
                                <option value="Prescrição médica">Prescrição médica</option>
                                <option value="Ficha de controle de sinais vitais">Ficha de controle de sinais vitais</option>
                                <option value="Livro de registro de hemocomponentes">Livro de registro de hemocomponentes</option>
                                <option value="Formulário de devolução de hemocomponentes">Formulário de devolução de hemocomponentes</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="inputDesc" class="font-weight-bold">Descrição <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nao_conformidade" id="inputDesc" required maxlength="200" autocomplete="off" placeholder="Descreva a não conformidade">
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="inputStatus" name="status" value="ativo" checked>
                                <label class="custom-control-label" for="inputStatus">Ativo</label>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" id="btnSave">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for Delete -->
    <form id="formDelete" method="POST" action="" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_nao_conformidade" id="deleteId">
    </form>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Bootstrap 5 Modal - Criar instância sempre que necessário
        function openAddModal() {
            console.log('openAddModal chamado');
            document.getElementById('modalTitle').innerText = 'Adicionar Não Conformidade';
            document.getElementById('formAction').value = 'create';
            document.getElementById('inputId').value = '';
            document.getElementById('inputTipo').value = '';
            document.getElementById('inputDesc').value = '';
            document.getElementById('inputStatus').checked = true;
            document.getElementById('btnSave').innerText = 'Salvar';
            
            const modalElement = document.getElementById('modalNC');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal aberto');
        }

        function openEditModal(id, tipo, desc, status) {
            console.log('openEditModal chamado');
            document.getElementById('modalTitle').innerText = 'Editar Não Conformidade';
            document.getElementById('formAction').value = 'update';
            document.getElementById('inputId').value = id;
            document.getElementById('inputTipo').value = tipo;
            document.getElementById('inputDesc').value = desc;
            document.getElementById('inputStatus').checked = (status === 'ativo');
            document.getElementById('btnSave').innerText = 'Atualizar';
            
            const modalElement = document.getElementById('modalNC');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('Modal aberto');
        }
        
        // Adicionar event listeners quando o documento estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado, configurando event listeners');
            
            // Garantir que os botões com data-bs-dismiss funcionem
            const modalElement = document.getElementById('modalNC');
            if (modalElement) {
                modalElement.addEventListener('click', function(e) {
                    if (e.target.hasAttribute('data-bs-dismiss') || 
                        e.target.closest('[data-bs-dismiss]')) {
                        console.log('Botão de fechar clicado');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                });
            }
        });

        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter isso!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('formDelete').submit();
                }
            })
        }

        <?php if(isset($_SESSION['msg_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: '<?php echo $_SESSION['msg_success']; ?>',
                timer: 2000,
                showConfirmButton: false
            });
            <?php unset($_SESSION['msg_success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['msg_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: '<?php echo $_SESSION['msg_error']; ?>',
            });
            <?php unset($_SESSION['msg_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
