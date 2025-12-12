<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para nao_conformidades
    $query_nao_conformidade       = "SELECT * FROM sth_nao_conformidade ORDER BY tipo, nao_conformidade";
    $result_nao_conformidade      = conecta_query($conexao, $query_nao_conformidade);
    $result_nao_conformidade_view = conecta_query($conexao, $query_nao_conformidade);

    $_SESSION['insere']   = "inserir_nao_conformidade";
    $_SESSION['atualiza'] = "atualizar_nao_conformidade";
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

    <title>Cadastro - Não conformidade</title>

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
            overflow-y: auto;
        }
        .container-cp {
            max-width: 1000px;
            margin: 90px auto 20px auto; /* Compact: 90px top */
            padding: 0 15px;
            flex: 1;
        }
        .card-modern {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: none;
            margin-bottom: 20px; /* Compact: 20px bottom */
        }
        .card-header-modern {
            background: linear-gradient(135deg, #741c19 0%, #a02c28 100%);
            color: #fff;
            padding: 15px 30px; /* Compact padding */
            font-size: 1.3rem; /* Compact font */
            font-weight: 600;
            text-align: center;
        }
        .card-body-modern {
            padding: 25px; /* Compact padding */
        }
        label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.3rem; /* Compact margin */
            font-size: 0.85rem; /* Compact font */
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
            font-size: 0.9rem; /* Compact font */
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
            padding: 10px 25px; /* Compact button padding */
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            font-size: 0.85rem; /* Compact font */
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
        .botoes-nao-conformidade {
             display: flex; justify-content: center; gap: 15px; margin-top: 20px; /* Reduced margin */
        }
        .table-responsive {
            margin-top: 15px; /* Reduced margin */
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            height: 100%;
            padding-top: 25px; 
        }
        .checkbox-wrapper input {
            width: 18px; /* Slightly smaller */
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
                <i class="fas fa-exclamation-triangle mr-2"></i> Cadastro de Não Conformidade
            </div>
            <div class="card-body-modern">
                <form action="insere.php" method="POST" name="form" id="form_nao_conformidade">
                    <?php
                        if(isset($_SESSION['validado_nao_conformidade']) && $_SESSION['validado_nao_conformidade'] == 0){
                            exibir_mensagem_simples("Adicionado!", "Não conformidade adicionada com sucesso.", "success");
                        }
                        $_SESSION['validado_nao_conformidade'] = -1;

                        if(isset($_SESSION['validado_nao_conformidade_editar']) && $_SESSION['validado_nao_conformidade_editar'] == 0){
                            exibir_mensagem_simples("Editado!", "Não conformidade editada com sucesso.", "success");
                        }
                        $_SESSION['validado_nao_conformidade_editar'] = -1;
                    ?>

                    <div class="row">
                        <div class="col-lg-5 form-group">
                            <label for="tipo" class="required">Tipo</label>
                            <select name="tipo" id="tipo" required>
                                <option value="">Selecione</option>
                                <option value="Prescrição médica">Prescrição médica</option>
                                <option value="Ficha de controle de sinais vitais">Ficha de controle de sinais vitais</option>
                                <option value="Livro de registro de hemocomponentes">Livro de registro de hemocomponentes</option>
                                <option value="Formulário de devolução de hemocomponentes">Formulário de devolução de hemocomponentes</option>
                                <option value="Outros">Outros</option>
                            </select>
                        </div>
                        <div class="col-lg-7 form-group">
                            <label for="nao_conformidade" class="required">Não conformidade</label>
                            <input type="text" name="nao_conformidade" id="nao_conformidade" maxlength="200" required placeholder="Descreva a não conformidade">
                        </div>
                    </div>

                    <div class="botoes-nao-conformidade">
                        <button type="submit" class="btn-modern btn-success-modern" name="inserir_reacao" onclick="removeRequired()">
                            <i class="fas fa-check mr-2"></i> Adicionar
                        </button>
                        <a href="grid_nao_conformidades.php" class="btn-modern btn-reset-modern" style="text-decoration: none; display: inline-flex; align-items: center;">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </a>
                    </div>
                </form>
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
                    <p><strong>Como adicionar uma Não conformidade?</strong></p>
                    <ul>
                        <li>Insira o nome da Não conformidade.</li>
                        <li>Selecione "Ativo" se ela estiver operativa.</li>
                        <li>Clique em "Adicionar".</li>
                    </ul>
                    <p><strong>Como editar uma Não conformidade?</strong></p>
                    <ul>
                        <li>Clique em "Editar".</li>
                        <li>Selecione a não conformidade na lista.</li>
                        <li>Altere o nome, tipo ou status.</li>
                        <li>Clique em "Salvar Alterações".</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts removed to prevent duplication with header/footer -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/script.js"></script>
    
    <script>
        // Função para preencher o formulário de edição
        function editar() {
            var selectedOption = $('#nao_conformidades').find(':selected');
            
            if(selectedOption.val() === "") {
                $('#nao_conformidade_editar').val("");
                $('#tipo_editar').val("").trigger('change'); // Trigger change for Select2 if used
                $('#status_editar').prop('checked', false);
                return;
            }

            $('#nao_conformidade_editar').val(selectedOption.text().trim());
            $('#tipo_editar').val(selectedOption.data('tipo')).trigger('change'); // Sync Select2
            
            // Check status properly
            var status = selectedOption.data('status');
            if(status === "ativo" || status === true || status === 1){
                $('#status_editar').prop('checked', true);
            }else{
                $('#status_editar').prop('checked', false);
            }
        }
        
        $(document).ready(function() {
            // 1. Controle dos Checkboxes da Tabela
            $('.check-editar').on('click', function() {
                if ($(this).is(':checked')) {
                    // Desmarca outros
                    $('.check-editar').not(this).prop('checked', false);
                    
                    // Pega ID e atualiza Select
                    var idSelecionado = $(this).val();
                    $('#nao_conformidades').val(idSelecionado).trigger('change');
                } else {
                    // Limpa Select
                    $('#nao_conformidades').val('').trigger('change');
                }
            });

            // 2. Listener para o Select do Modal (dispara a função editar)
            $('#nao_conformidades').on('change', function() {
                editar();
            });
            
            // 3. Validação do Botão "Editar" antes de abrir o modal
            $('#btn-editar-nc').on('click', function(e) {
                // Prevent default behavior
                e.preventDefault();

                var selecionado = $('.check-editar:checked').length;
                
                if(selecionado === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Por favor, selecione um registro na tabela para editar.'
                    });
                } else {
                    // Manually show modal if valid
                    $('#nao_conformidade_modal').modal('show');
                }
            });

            // 4. Ajuda
            $("#helpButton").click(function() {
                $("#helpModal").modal('show');
            });
            
            // 5. Confirmação de Envio do Formulário
            const form = document.getElementById('form_nao_conformidade');
            if(form){
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    Swal.fire({
                        title: "Deseja adicionar?",
                        text: "Você irá adicionar uma nova não conformidade!",
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
            }
        });
    </script>
    
</body>
</html>