<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para buscar os setores no banco de dados
    $query_setor  = "SELECT * FROM sth_setores WHERE status='ativo' ORDER BY nome_setor DESC";
    $result_setor = conecta_query($conexao, $query_setor);

    $_SESSION['insere'] = "inserir_paciente";
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

    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <!-- Inclusão do Plugin jQuery Validation-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>

    <title>Cadastro - Paciente</title>

    <style>
        /* Modern Overrides */
        body {
            background: #f4f7f6 !important; /* Clean gray/blue background */
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Override style.css specific containers */
        .container-cp {
            max-width: 1200px;
            margin: 140px auto 40px auto; /* Increased top margin for fixed header */
            padding: 0 15px;
            flex: 1;
        }

        .card-modern {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); /* Soft shadow */
            overflow: hidden;
            border: none;
            margin-bottom: 40px;
        }

        .card-header-modern {
            background: linear-gradient(135deg, #741c19 0%, #a02c28 100%);
            color: #fff;
            padding: 20px 30px;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
        }

        .card-body-modern {
            padding: 40px;
        }

        label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        label.required::after {
            content: " *";
            color: #e53e3e;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #fff !important;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: #2d3748;
        }

        /* Apply custom arrow ONLY to select elements */
        select {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-color: #fff !important;
            /* Simple black chevron SVG */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 1em !important;
            padding-right: 2rem !important; /* Make room for the arrow */
        }

        /* IE10/11 - Hide default arrow */
        select::-ms-expand {
            display: none;
        }

        /* Ensure no background image on text inputs */
        input[type="text"],
        input[type="date"],
        textarea {
            background-image: none !important;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            border-color: #3182ce; /* Blue outline */
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
            outline: none;
        }

        /* Custom Radio Buttons */
        .radio-group {
            display: flex;
            align-items: center;
            gap: 15px;
            height: 100%;
        }

        .radio-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 500;
            color: #4a5568;
        }

        .radio-label input {
            margin-right: 8px;
            width: auto;
        }

        /* Buttons */
        .botoes-paciente {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .btn-modern {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
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

        /* Footer Adjustments */
        .footer {
            position: relative; /* Unlock from fixed if content is long */
            width: 100%;
        }
        
        /* Floating Button Modern Fix */
        .floating-button {
            z-index: 1000;
            background-color: #741c19;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        /* Helper for margins */
        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container container-cp">
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="fas fa-user-plus mr-2"></i> Cadastro do Paciente
            </div>
            <div class="card-body-modern">
                <form name="cadastrar_paciente" action="insere.php" method="POST" id="form_paciente">

                    <?php
                        if(isset($_SESSION['validado_paciente'])){
                            
                            if ($_SESSION['validado_paciente'] == 1) {
                                exibir_mensagem_simples("Paciente não cadastrado!", "CPF ou prontuário já cadastrado.", "warning");
                            } else if ($_SESSION['validado_paciente'] == 2) {
                                exibir_mensagem_simples("Paciente não cadastrado!", "CPF ou prontuário precisam estar preenchidos.", "warning");
                            } else if ($_SESSION['validado_paciente'] == 3) {
                                exibir_mensagem_simples("Paciente não cadastrado!", "Número de registro já cadastrado.", "warning");
                            } else if ($_SESSION['validado_paciente'] == 4) {
                                exibir_mensagem_simples("Paciente não cadastrado!", "Número de registro não pode ser igual ao CPF ou prontuário.", "warning");
                            } else if ($_SESSION['validado_paciente'] == 0) {
                                exibir_mensagem_simples("Cadastrado!", "Paciente cadastrado com sucesso.", "success");  
                            }

                            $_SESSION['validado_paciente'] = -1;
                        }        
                    ?>

                    <!-- Linha 1: Data de cadastro | Recém-Nascido (RN)? -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="data_requisicao" class="required">Data de cadastro</label>
                            <input type="date" name="data_requisicao" value="<?php echo date('Y-m-d'); ?>" id="data_requisicao" readonly class="form-control-plaintext" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="required">Recém-Nascido (RN)?</label>
                            <div class="radio-group" style="padding: 10px 0;">
                                <label class="radio-label">
                                    <input type="radio" name="recem_nascido" value="sim" id="rn_sim" required> Sim
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="recem_nascido" value="nao" id="rn_nao" required> Não
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Linha 2: Prontuário | CNS | CPF -->
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="prontuario" class="required">Prontuário</label>
                            <input type="text" name="prontuario" id="prontuario" maxlength="15" oninput="apenasNumeros(this)" required placeholder="Somente números">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="num_sus">CNS (Cartão Nacional de Saúde)</label>
                            <input type="text" id="num_sus" name="num_sus" maxlength="18" oninput="validaEFormataCns(this)" placeholder="000.0000.0000.0000">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="cpf" class="required">CPF</label>
                            <input type="text" name="cpf" id="cpf" oninput="formatarCPF(this)" onblur="validarCPF(this)" maxlength="14" required placeholder="000.000.000-00">
                        </div>
                    </div>

                    <!-- Linha 3: Nome Completo | Nome da Mãe -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="nome" class="required">Nome Completo</label>
                            <input type="text" name="nome_completo" id="nome" oninput="adiciona_rn(this, '#rn_sim')" maxlength="255" required placeholder="Digite o nome completo">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="filiacao_mae" class="required">Nome da Mãe</label>
                            <input type="text" name="mae" id="filiacao_mae" maxlength="255" required placeholder="Nome completo da mãe">
                        </div>
                    </div>

                    <!-- Linha 4: Data de Nascimento | Sexo | Nome Social Completo -->
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="data_nascimento" class="required">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" oninput="dateMaskH(this, event)" onBlur="dtnasc(this.value)" id="data_nascimento" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="sexo" class="required">Sexo</label>
                            <select name="sexo" id="sexo" required>
                                <option value="">Selecione</option>
                                <option value="F">Feminino</option>
                                <option value="M">Masculino</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="nome_social">Nome Social Completo</label>
                            <input type="text" maxlength="255" name="nome_social" id="nome_social" placeholder="Opcional">
                        </div>
                    </div>

                    <!-- Linha 5: ABO | Fator RH -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="abo" class="required">ABO</label>
                            <select name="abo" id="abo" required>
                                <option value="">Selecione</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="O">O</option>
                                <option value="AB">AB</option>
                                <option value="Outro">Outro</option>
                                <option value="Desconhecido">Desconhecido</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="rh" class="required">Fator RH</label>
                            <select name="rh" id="rh" required>
                                <option value="">Selecione</option>
                                <option value="Positivo">Positivo</option>
                                <option value="Negativo">Negativo</option>
                                <option value="Outro">Outro</option>
                                <option value="Desconhecido">Desconhecido</option>
                            </select>
                        </div>
                    </div>

                    <!-- Linha 6: Hospital de Internação | Setor | Leito -->
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="hosp_internação">Hospital de Internação</label>
                            <input type="text" name="hospital_internacao" id="hosp_internação" value="HUM" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="setor" class="required">Setor</label>
                            <select name="setor" id="setor" required>
                                <option value="">Selecione</option>
                                <?php
                                    while ($row_setor = pg_fetch_assoc($result_setor)) {
                                        echo "<option value='$row_setor[id_setor]'> $row_setor[nome_setor] </option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="leito">Leito</label>
                            <input type="text" name="leito" id="leito" maxlength="200" placeholder="Ex: 102-A">
                        </div>
                    </div>

                    <!-- Linha 7: Registro | Número RT -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="registro" class="required">Registro</label>
                            <input type="text" name="registro" id="registro" oninput="apenasNumeros(this)" maxlength="15" required placeholder="Nº de Registro">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="numero_rt">Número RT</label>
                            <input type="text" name="numero_rt" id="numero_rt" oninput="apenasNumeros(this)" minlength="10" maxlength="10" placeholder="Opcional">
                        </div>
                    </div>

                    <!-- Linha 8: Diagnóstico | Observação -->
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label for="diagnostico">Diagnóstico</label>
                            <input type="text" name="diagnostico" id="diagnostico" maxlength="255" placeholder="Diagnóstico principal">
                        </div>
                        <div class="col-md-7 form-group">
                            <label for="observacao_paciente" style="display:block;">Observação</label>
                            <textarea name="observacao" id="observacao_paciente" rows="2" maxlength="255" placeholder="Informações adicionais..."></textarea>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="botoes-paciente">
                        <button type="submit" name="inserir_paciente" class="btn-modern btn-success-modern">
                            <i class="fas fa-check mr-2"></i> Cadastrar
                        </button>
                        <button type="reset" class="btn-modern btn-reset-modern">
                            <i class="fas fa-eraser mr-2"></i> Limpar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Botão flutuante de ajuda -->
    <div class="floating-button" id="helpButton">
        <i class="fas fa-question"></i>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Cadastro de Paciente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;">Como cadastrar um paciente?</p>
                    <ul class="pl-3">
                        <li>Selecione "Sim" ou "Não" para recém-nascido.</li>
                        <li>Informe nome completo, nascimento, sexo, ABO e RH.</li>
                        <li>Forneça CPF, nome da mãe e CNS (Cartão SUS).</li>
                        <li>Escolha o setor de internação.</li>
                        <li>Preencha prontuário, leito e registro.</li>
                        <li>Clique em "Cadastrar".</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        //adicona RN- se o paciente estiver com rn selecionado sim
        function adiciona_rn(input, rn_sim) {
            //verifica se rn selecionado é sim
            if (document.querySelector(rn_sim).checked) {
                //verifica se o valor do input não começa com RN-
                if (!input.value.startsWith("RN-")) {
                    input.value = "RN-" + input.value;
                }
            }
        }

        //se tiver um nome e só depois selecionar se é rn, se sim adiciona RN-
        document.querySelector('#rn_sim').addEventListener('change', function() {
            if (this.checked) {
                var nome = document.getElementById("nome").value;

                if (!nome.startsWith("RN-")) {
                    document.getElementById("nome").value = "RN-" + nome;
                }
            }
        });

        // se ja tiver um nome no input nome e nao for recem nascido, tira o rn
        document.querySelector('#rn_nao').addEventListener('change', function() {
            if (this.checked) {
                var nome = document.getElementById("nome").value;
                
                if (nome.startsWith("RN-")) {  
                    var sem_rn = nome.substr(3); //tira rn-, se ja tiver selecionado rn sim, e querer trocar, nao é preciso escrever tudo dnv
                    document.getElementById("nome").value = sem_rn; // se trocar o documento pela variavel nome nao funciona
                }
            }
        });

        // BOTÃO DE CONFIRMAÇÃO DE CADASTRO DE PACIENTE
        const form = document.getElementById('form_paciente');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Confirmar cadastro?",
                text: "Verifique se todos os dados estão corretos.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sim, cadastrar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Fix helpers script if missing functions like dateMaskH
        function dateMaskH(input, event) {
            // Simple placeholder if not loaded from js/script.js
            // In a real scenario, js/script.js should handle this.
        }
        
         // Floating Help Button Logic
         $(document).ready(function() {
            $("#helpButton").click(function() {
                $("#helpModal").modal('show');
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    
</body>
</html>