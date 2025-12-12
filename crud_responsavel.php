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
        $nome = trim($_POST["nome_responsavel"]);
        $status = isset($_POST["status"]) ? 'ativo' : 'inativo';

        if (!empty($nome)) {
            $query = "INSERT INTO sth_responsavel (nome, status) VALUES ('$nome', '$status')";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Responsável adicionado com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao adicionar responsável.";
            }
        }
    } elseif ($action === 'update') {
        $id_responsavel = filter_input(INPUT_POST, 'id_responsavel', FILTER_SANITIZE_NUMBER_INT);
        $nome = trim($_POST["nome_responsavel"]);
        $status = isset($_POST["status"]) ? 'ativo' : 'inativo';

        if ($id_responsavel && !empty($nome)) {
            $query = "UPDATE sth_responsavel SET nome = '$nome', status = '$status' WHERE id_responsavel = $id_responsavel";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Responsável atualizado com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao atualizar responsável.";
            }
        }
    } elseif ($action === 'delete') {
        $id_responsavel = filter_input(INPUT_POST, 'id_responsavel', FILTER_SANITIZE_NUMBER_INT);
        
        if ($id_responsavel) {
            $query = "DELETE FROM sth_responsavel WHERE id_responsavel = $id_responsavel";
            if (conecta_query($conexao, $query)) {
                $_SESSION['msg_success'] = "Responsável excluído com sucesso!";
            } else {
                $_SESSION['msg_error'] = "Erro ao excluir responsável. Verifique se não está em uso.";
            }
        }
    }

    // Redirect to self to prevent resubmission
    header("Location: crud_responsavel.php");
    exit;
}

// Fetch Responsibles (Read)
$query_responsaveis = "SELECT * FROM sth_responsavel ORDER BY nome";
$result_responsaveis = conecta_query($conexao, $query_responsaveis);

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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gerenciar - Responsáveis</title>
    <style>
        /* Modern Overrides */
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        
        .container-crud { max-width: 1000px; margin: 90px auto 40px auto; padding: 0 15px; }
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
        .close { color: #fff; opacity: 0.8; }
        .close:hover { opacity: 1; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container container-crud">
        <div class="card card-crud">
            <div class="card-header card-header-crud">
                <span><i class="fas fa-user-md mr-2"></i> Gerenciar Responsáveis</span>
                <button class="btn-add" data-toggle="modal" data-target="#modalResponsavel" onclick="openAddModal()">
                    <i class="fas fa-plus mr-1"></i> Adicionar
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4 text-left">Nome do Responsável</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pr-4" width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (pg_num_rows($result_responsaveis) > 0) {
                                while ($row = pg_fetch_assoc($result_responsaveis)) {
                                    $statusClass = $row['status'] == 'ativo' ? 'status-active' : 'status-inactive';
                                    $statusText = $row['status'] == 'ativo' ? 'Ativo' : 'Inativo';
                                    $nomeSafe = htmlspecialchars($row['nome'], ENT_QUOTES);
                                    echo "<tr>
                                            <td class='pl-4 text-left'>{$row['nome']}</td>
                                            <td class='text-center'><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                                            <td class='text-center pr-4'>
                                                <button onclick='openEditModal({$row['id_responsavel']}, \"{$nomeSafe}\", \"{$row['status']}\")' class='action-btn btn-edit' title='Editar'><i class='fas fa-pencil-alt'></i></button>
                                                <button onclick='confirmarExclusao({$row['id_responsavel']})' class='action-btn btn-delete' title='Excluir'><i class='fas fa-trash-alt'></i></button>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center py-4 text-muted'>Nenhum responsável cadastrado.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Responsavel (Shared for Create/Update) -->
    <div class="modal fade" id="modalResponsavel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar Responsável</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formResponsavel" method="POST" action="">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id_responsavel" id="inputId">
                        
                        <div class="form-group">
                            <label for="inputNome" class="font-weight-bold">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome_responsavel" id="inputNome" required maxlength="200" autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="inputStatus" name="status" value="ativo" checked>
                                <label class="custom-control-label" for="inputStatus">Ativo</label>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
        <input type="hidden" name="id_responsavel" id="deleteId">
    </form>

    <?php include 'includes/footer.php'; ?>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Adicionar Responsável';
            document.getElementById('formAction').value = 'create';
            document.getElementById('inputId').value = '';
            document.getElementById('inputNome').value = '';
            document.getElementById('inputStatus').checked = true;
            document.getElementById('btnSave').innerText = 'Salvar';
            $('#modalResponsavel').modal('show');
        }

        function openEditModal(id, nome, status) {
            document.getElementById('modalTitle').innerText = 'Editar Responsável';
            document.getElementById('formAction').value = 'update';
            document.getElementById('inputId').value = id;
            document.getElementById('inputNome').value = nome;
            document.getElementById('inputStatus').checked = (status === 'ativo');
            document.getElementById('btnSave').innerText = 'Atualizar';
            $('#modalResponsavel').modal('show');
        }

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
