<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para setores
    $query_setores         = "SELECT * FROM sth_setores ORDER BY nome_setor";
    $result_setores        = conecta_query($conexao, $query_setores);
    $result_setores_editar = conecta_query($conexao, $query_setores);

    $_SESSION['insere']   = "inserir_setor";
    $_SESSION['atualiza'] = "atualiza_setor";
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
    <script src="js/sweetalert2.all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

    <title>Cadastro - Setor</title>

    <style>
        /* Modern Overrides */
        body {
            background: #f4f7f6 !important;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Allow scroll if needed on very small screens, but aim to fit */
        }
        .container-cp {
            max-width: 1000px;
            margin: 90px auto 20px auto; /* Reduced top margin from 140px to 90px */
            padding: 0 15px;
            flex: 1;
        }
        .card-modern {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: none;
            margin-bottom: 20px; /* Reduced bottom margin */
        }
        .card-header-modern {
            background: linear-gradient(135deg, #741c19 0%, #a02c28 100%);
            color: #fff;
            padding: 15px 30px; /* Reduced padding */
            font-size: 1.3rem; /* Slightly smaller font */
            font-weight: 600;
            text-align: center;
        }
        .card-body-modern {
            padding: 25px; /* Reduced padding */
        }
        label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
        }
        label.required::after {
            content: " *";
            color: #e53e3e;
        }
        input[type="text"], input[type="date"], select, textarea {
            width: 100%;
            padding: 8px 12px; /* Compact padding */
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #fff !important;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            color: #2d3748;
        }
         /* Apply custom arrow ONLY to select elements */
        select {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-color: #fff !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 1em !important;
            padding-right: 2rem !important;
        }
        /* IE10/11 - Hide default arrow */
        select::-ms-expand { display: none; }
        /* Ensure no background image on text inputs */
        input[type="text"], input[type="date"], textarea { background-image: none !important; }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
            outline: none;
        }
        .btn-modern {
            padding: 10px 25px; /* Compact buttons */
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-success-modern {
            background-color: #28a745;
            color: white;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);
        }
        .btn-success-modern:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(40, 167, 69, 0.3);
        }
        .btn-reset-modern {
            background-color: #6c757d;
            color: white;
            box-shadow: 0 4px 6px rgba(108, 117, 125, 0.2);
        }
        .btn-reset-modern:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(108, 117, 125, 0.3);
        }
        .btn-primary-modern {
            background-color: #007bff;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.2);
        }
        .btn-primary-modern:hover {
            background-color: #0069d9;
            transform: translateY(-2px);
        }
        .footer { position: relative; width: 100%; }
        .botoes-setor {
             display: flex; justify-content: center; gap: 15px; margin-top: 20px;
        }
        .table-responsive {
            margin-top: 15px;
        }
        /* Custom responsive checkbox alignment */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            height: 100%;
            padding-top: 25px; 
        }
        .checkbox-wrapper input {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }
        hr {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        h5 {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container container-cp">
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="fas fa-building mr-2"></i> Cadastro de Setor
            </div>
            <div class="card-body-modern">
                <form action="insere.php" method="POST" name="form" id="form_setor">
                    <?php
                        if(isset($_SESSION['validado_setor']) && $_SESSION['validado_setor'] == 0){
                            exibir_mensagem_simples("Adicionado!", "Setor adicionado com sucesso.", "success");
                        }
                        $_SESSION['validado_setor'] = -1;

                        if(isset($_SESSION['validado_setor_editar']) && $_SESSION['validado_setor_editar'] == 0){
                            exibir_mensagem_simples("Editado!", "Setor editado com sucesso.", "success");
                        }
                        $_SESSION['validado_setor_editar'] = -1;
                    ?>
                    
                    <div class="row">
                        <div class="col-md-9 form-group">
                            <label for="setor" class="required">Nome do setor</label>
                            <input type="text" name="setor" id="setor" maxlength="200" autocomplete="off" required placeholder="Digite o nome do setor">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="status">Status</label>
                            <div class="checkbox-wrapper" style="padding-top: 8px;">
                                <input type='checkbox' name='status' id='status' value='ativo'>
                                <label for="status" style="margin:0; font-weight:normal;">Ativo</label>
                            </div>
                        </div>
                    </div>

                    <div class="botoes-setor">
                        <button type="submit" class="btn-modern btn-success-modern" name="inserir_reacao" onclick="removeRequired()">
                            <i class="fas fa-check mr-2"></i> Adicionar
                        </button>
                        <button type="reset" class="btn-modern btn-reset-modern">
                            <i class="fas fa-eraser mr-2"></i> Limpar
                        </button>
                        <button type="button" class="btn-modern btn-primary-modern" data-toggle="modal" data-target="#setores_modal">
                            <i class="fas fa-pencil-alt mr-2"></i> Editar
                        </button>
                    </div>
                </form>

                <hr>
                <h5 class="text-center" style="color: #4a5568;">Setores Cadastrados</h5>

                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nome do Setor</th>
                                <th class="text-center" width="100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if (!$result_setores) {
                                    echo "<tr><td colspan='2'>Erro ao gerar a query</td></tr>";
                                }else{
                                    if(pg_num_rows($result_setores) > 0) {
                                        while ($row_setor = pg_fetch_assoc($result_setores)) {
                                            $status = !empty($row_setor['status']) ? "checked" : null;
                                            echo "<tr>
                                                    <td> {$row_setor['nome_setor']} </td> 
                                                    <td class='text-center'><input type='checkbox' name='status_setor' $status onclick='return false;' style='width: 16px; height: 16px;'></td>
                                                </tr>";
                                        }
                                    }else{
                                        echo "<tr><td colspan='2' class='text-center'>Nenhum registro encontrado</td></tr>";
                                     }                                                                  
                                }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modal edição -->
                <div class="modal fade" id="setores_modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Editar informações</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="atualiza.php" method="POST" name="form_editar" id="form_setor_editar">
                                    <div class="form-group">
                                        <label for="setores" class="required">Selecione o setor</label>
                                        <select name="setores" id="setores" onchange='editar()' required>
                                            <option value="">Selecione</option>
                                            <?php
                                                while ($row_setor_editar = pg_fetch_assoc($result_setores_editar)) {
                                                    echo "<option value='{$row_setor_editar['id_setor']}' data-status='{$row_setor_editar['status']}'>
                                                        {$row_setor_editar['nome_setor']}
                                                    </option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="setor_editar" class="required">Novo nome do setor</label>
                                        <input type="text" name="setor_editar" id="setor_editar" maxlength="200" required>
                                    </div>
                                    <div class="form-group checkbox-wrapper" style="padding:0;">
                                        <input type='checkbox' name='status_editar' id="status_editar" value='ativo'>
                                        <label for="status_editar" style="margin:0; font-weight:normal;">Ativo</label>
                                    </div>
                                    <div class="mt-4 text-center">
                                        <button type="submit" class="btn-modern btn-success-modern" name="atualizar_setor">Salvar Alterações</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Help -->
    <div class="floating-button" id="helpButton">
        <i class="fas fa-question"></i>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajuda - Responsável</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Como adicionar um setor?</strong></p>
                    <ul>
                        <li>Insira o nome do setor.</li>
                        <li>Selecione "Ativo" se o setor estiver em uso.</li>
                        <li>Clique em "Adicionar".</li>
                    </ul>
                    <p><strong>Como editar um setor?</strong></p>
                    <ul>
                        <li>Clique em "Editar".</li>
                        <li>Selecione o setor na lista.</li>
                        <li>Altere o nome ou status.</li>
                        <li>Clique em "Salvar Alterações".</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function editar() {
            var selectSetor   = document.getElementById('setores');
            var selectedSetor = selectSetor.options[selectSetor.selectedIndex];
            
            if(selectedSetor.value === "") {
                document.getElementById('setor_editar').value = "";
                document.getElementById('status_editar').checked = false;
                return;
            }

            document.getElementById('setor_editar').value  = selectedSetor.text.trim();

            if(selectedSetor.dataset.status == "ativo"){
                document.getElementById('status_editar').checked = true;
            }else{
                document.getElementById('status_editar').checked = false;
            }
        }

        const form = document.getElementById('form_setor');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja adicionar?",
                text: "Você irá adicionar um novo setor!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sim, adicionar",
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        
        $(document).ready(function() {
            $("#helpButton").click(function() {
                $("#helpModal").modal('show');
            });
        });
    </script>
    
    <!-- Scripts removed to prevent duplication with header/footer -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/script.js"></script>
    
</body>
</html>