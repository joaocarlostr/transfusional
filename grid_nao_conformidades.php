<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para nao_conformidades
    $query_nao_conformidade = "SELECT * FROM sth_nao_conformidade ORDER BY tipo, nao_conformidade";
    $result_nao_conformidade = conecta_query($conexao, $query_nao_conformidade);
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Lista - Não Conformidades</title>
    <style>
        /* Modern Overrides */
        body { background: #f4f7f6 !important; font-family: 'Montserrat', sans-serif; }
        
        .container-grid { max-width: 1200px; margin: 90px auto 40px auto; padding: 0 15px; }
        .card-grid { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
        
        .card-header-grid { 
            background: linear-gradient(135deg, #741c19 0%, #a02c28 100%); 
            color: #fff !important; 
            padding: 15px 30px; 
            font-size: 1.3rem; 
            font-weight: 600; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .card-header-grid span { color: #fff !important; }

        /* Improved Adicionar Button */
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
        }
        .btn-add:hover { 
            background-color: #218838; 
            color: #fff !important;
            text-decoration: none; 
            transform: translateY(-2px); 
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .btn-add i { margin-right: 8px; }

        .table-custom th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .table-custom td { vertical-align: middle; color: #212529; font-size: 0.9rem; }
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border: none; margin: 0 2px; transition: all 0.2s; }
        .btn-edit { background-color: #e3f2fd; color: #0d47a1; }
        .btn-edit:hover { background-color: #bbdefb; color: #0d47a1; }
        .btn-delete { background-color: #ffebee; color: #c62828; }
        .btn-delete:hover { background-color: #ffcdd2; color: #b71c1c; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background-color: #e8f5e9; color: #2e7d32; }
        .status-inactive { background-color: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container container-grid">
        <div class="card card-grid">
            <div class="card-header card-header-grid">
                <span><i class="fas fa-list mr-2"></i> Lista de Não Conformidades</span>
                <a href="crud_nao_conformidade.php" class="btn-add"><i class="fas fa-plus mr-1"></i> Adicionar</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4 text-left">Tipo</th>
                                <th class="text-left">Descrição</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pr-4" width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (pg_num_rows($result_nao_conformidade) > 0) {
                                while ($row = pg_fetch_assoc($result_nao_conformidade)) {
                                    $statusClass = $row['status'] == 'ativo' ? 'status-active' : 'status-inactive';
                                    $statusText = $row['status'] == 'ativo' ? 'Ativo' : 'Inativo';
                                    echo "<tr>
                                            <td class='pl-4 text-left'>{$row['tipo']}</td>
                                            <td class='text-left'>{$row['nao_conformidade']}</td>
                                            <td class='text-center'><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                                            <td class='text-center pr-4'>
                                                <a href='editar_nao_conformidade.php?id={$row['id_nao_conformidade']}' class='action-btn btn-edit' title='Editar'><i class='fas fa-pencil-alt'></i></a>
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
    
    <!-- Hidden form for deletion -->
    <form id="form-excluir" action="excluir_nao_conformidade.php" method="POST" style="display: none;">
        <input type="hidden" name="id_nao_conformidade" id="delete-id">
    </form>

    <?php include 'includes/footer.php'; ?>
    
    <script>
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
                    document.getElementById('delete-id').value = id;
                    document.getElementById('form-excluir').submit();
                }
            })
        }
        
        // Show success message if redirected back
        <?php if(isset($_SESSION['msg_exclusao'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: '<?php echo $_SESSION['msg_exclusao']; ?>',
                timer: 2000,
                showConfirmButton: false
            });
            <?php unset($_SESSION['msg_exclusao']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
