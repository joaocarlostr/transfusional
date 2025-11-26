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

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
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
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <!-- Corpo -->
    <div class="container container-cp">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Cadastro do Paciente</strong></p>
                <!-- Formulário -->
                <div id="formulario" class="container-cadastro-paciente">
                    <form name="cadastrar_paciente" action="insere.php" method="POST" id="form_paciente">

                        <?php
                            if(isset($_SESSION['validado_paciente'])){
                                
                                if ($_SESSION['validado_paciente'] == 1) {
                                    exibir_mensagem_simples("Paciente não cadastrado!", "CPF ou prontuário já cadastrado.", "warning");
    
                                } else if ($_SESSION['validado_paciente'] == 2) {
                                    exibir_mensagem_simples("Paciente não cadastrado!", "CPF ou prontuário precisam estar preenchidos.", "warning");
     
                                } else if ($_SESSION['validado_paciente'] == 3) {
                                    exibir_mensagem_simples("Paciente não cadastrado!", "Número de registro já cadastrado.", "warning");
     
                                }else if ($_SESSION['validado_paciente'] == 4) {
                                    exibir_mensagem_simples("Paciente não cadastrado!", "Número de registro não pode ser igual ao CPF ou prontuário.", "warning");
                                
                                }else if ($_SESSION['validado_paciente'] == 0) {
                                    exibir_mensagem_simples("Cadastrado!", "Paciente cadastrado com sucesso.", "success");  
                                }

                                $_SESSION['validado_paciente'] = -1;
                            }        
                        ?>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6" style="display: flex;">
                                <div>
                                    <label for="data_requisicao" class="required">Data de cadastro:</label><br>
                                    <!-- pega a data atual -->
                                    <input type="date" size="40" name="data_requisicao" value="<?php echo date('Y-m-d'); ?>" min="1920-01-01"
                                        max="<?php echo date('Y-m-d'); ?>" oninput="dateMaskH(this, event)" id="data_requisicao" readonly="readOlny" required>
                                </div>
                                <div style="margin-left: 50px;">
                                    <label for="rn_sim" class="required">RN?</label><br>
                                    <input type="radio" name="recem_nascido" value="sim" id="rn_sim" required> Sim
                                    <input type="radio" name="recem_nascido" value="nao" id="rn_nao" required> Não
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="num_sus" >CNS:</label><br>
                                <input type="text" id="num_sus" name="num_sus" size="35" maxlength="18" oninput="validaEFormataCns(this)" id="num_sus" placeholder="000.0000.0000.0000">
                            </div>
                        </div>

                        <div class="row">
                            <div class=" col-sm-12 col-md-12 col-lg-6">
                                <label for="nome" class="required">Nome Completo:</label><br>
                                <input type="text" name="nome_completo" id="nome" oninput="adiciona_rn(this, '#rn_sim')" maxlength="255" required>
                            </div>
                            <div class=" col-sm-12 col-md-12 col-lg-6" style="display: flex;">
                                <div>
                                    <label for="abo" class="required">ABO: </label><br>
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
                                <div style="margin-left:40px;">
                                    <label for="rh" class="required">RH: </label><br>
                                    <select name="rh" id="rh" required>
                                        <option value="">Selecione</option>
                                        <option value="Positivo">Positivo</option>
                                        <option value="Negativo">Negativo</option>
                                        <option value="Outro">Outro</option>
                                        <option value="Desconhecido">Desconhecido</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6" style="display:flex;">
                                <div>
                                    <label for="data_nascimento" class="required">Data de Nascimento: </label><br>
                                    <input type="date" name="data_nascimento" oninput="dateMaskH(this, event)"
                                        onBlur="dtnasc(this.value)" id="data_nascimento" min="1920-01-01"
                                        max="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <div style="margin-left:40px;">
                                    <label for="sexo" class="required">Sexo: </label><br>
                                    <select name="sexo" id="sexo" required>
                                        <option value="">Selecione</option>
                                        <option value="F">F</option>
                                        <option value="M">M</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="hosp_internação">Hosp. Internação: </label><br>
                                <input type="text" placeholder="HUM" name="hospital_internacao" id="hosp_internação" value="HUM" readonly="readOlny">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="nome_social">Nome Social Completo:</label><br>
                                <input type="text" maxlength="255" name="nome_social" id="nome_social">
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="setor" class="required">Setor: </label><br>
                                <select name="setor" style="max-width:390px;" id="setor" required>
                                    <option value="">Selecione</option>
                                    <?php
                                        //gera options de todos os setores cadastrados
                                        while ($row_setor = pg_fetch_assoc($result_setor)) {
                                            echo "<option value='$row_setor[id_setor]'> $row_setor[nome_setor] </option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="prontuario" class="required">Prontuário:</label><br>
                                <input type="text" size="35" name="prontuario" id="prontuario" maxlength="15" oninput="apenasNumeros(this)" required>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="leito">Leito:</label><br>
                                <input type="text" name="leito" id="leito" maxlength="200">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <div class="row">
                                    <div>
                                        <label for="registro" class="required">Registro: </label><br>
                                        <input type="text" name="registro" id="registro" oninput="apenasNumeros(this)" maxlength="15" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div>
                                        <label for="cpf" class="required">CPF:</label><br>
                                        <input type="text" name="cpf" id="cpf" oninput="formatarCPF(this)" onblur="validarCPF(this)" maxlength="14" size="20" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div>
                                        <label for="filiacao_mae" class="required">Nome da mãe: </label><br>
                                        <input type="text" name="mae" id="filiacao_mae" maxlength="255" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <div class="row">
                                    <div>
                                        <label for="numero_rt">Número RT: </label><br>
                                        <input type="text" name="numero_rt" id="numero_rt" oninput="apenasNumeros(this)" minlength="10" maxlength="10">
                                    </div>
                                </div>
                                <div class="row">
                                    <div>
                                        <label for="diagnostico">Diagnóstico: </label><br>
                                        <input type="text" name="diagnostico" id="diagnostico" maxlength="255">
                                    </div>
                                </div>
                                <div class="row" >
                                    <label for="observacao_paciente">Observação:</label><br>
                                    <textarea name="observacao" id="observacao_paciente" cols="5" rows="1" maxlength="255"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="botoes-paciente">
                            <input type="submit" value="Cadastrar" class="btn botao-verde" name="inserir_paciente">
                            <input type="reset" value="Limpar Campos" class="btn botao-limpar">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Botão flutuante de ajuda -->
    <div class="floating-button" id="helpButton">
        <i class="fas fa-question"></i>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;">Como cadastrar um paciente?</p>
                    <ul>
                        <li>Primeiro selecione "Sim" ou "Não" para indicar se o paciente é um recém-nascido.</li>
                        <li>Informe o nome completo, data de nascimento, sexo, tipo sanguíneo (ABO) e fator RH do paciente.</li>
                        <li>Forneça o número do CPF, nome completo da mãe e número do Cartão Nacional de Saúde (SUS).</li>
                        <li>Escolha o setor específico onde o paciente está alojado.</li>
                        <li>Adicione o prontuário do paciente, indique o número do leito e qualquer observação relevante.</li>
                        <li>Clique em "Cadastrar". Uma notificação informará o sucesso do cadastro.</li>
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
                title: "Você deseja cadastrar?",
                text: "Você irá cadastrar um novo paciente!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, cadastrar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    
</body>
</html>