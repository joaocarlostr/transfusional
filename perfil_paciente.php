<?php
    include "database.php";
    include "function.php";

    // Consulta SQL para buscar dados do paciente selecionado e nome do setor
    $query_paciente = "SELECT DISTINCT d.*, s.nome_setor 
    FROM STH_dados_paciente d
    INNER JOIN STH_setores s ON d.id_setor = s.id_setor
    WHERE d.id_paciente = $_GET[id_paciente]";

    $result_paciente = conecta_query($conexao, $query_paciente);
    $row_paciente    = pg_fetch_assoc($result_paciente);

    // traz todas as bolsas que nao foram devolvidas deste paciente
    $query_bolsa = "SELECT c.*, h.*, bd.id_bolsa as devolvida FROM STH_Cadastro_Bolsa c
    INNER JOIN STH_Hemocomponentes h   ON h.id_hemocomponente = c.id_hemocomponente
    LEFT JOIN STH_Bolsas_Devolvidas bd ON bd.id_bolsa = c.id_bolsa
    WHERE id_paciente = $row_paciente[id_paciente] and bd.id_bolsa IS NULL 
    ORDER BY c.id_bolsa";

    $result_bolsa = conecta_query($conexao, $query_bolsa);

    //traz todos os setores ativos
    $query_setor_ativo        = "SELECT * FROM STH_setores WHERE status='ativo' ORDER BY nome_setor DESC";
    $result_setor_ativo       = conecta_query($conexao, $query_setor_ativo);
    $result_setor_livro_ativo = conecta_query($conexao, $query_setor_ativo);

    //traz setores inativos
    $query_setor_inativo        = "SELECT * FROM STH_setores WHERE status='' ORDER BY nome_setor DESC";
    $result_setor_inativo       = conecta_query($conexao, $query_setor_inativo);
    $result_setor_livro_inativo = conecta_query($conexao, $query_setor_inativo);

    // Consulta SQL para buscar os hemocomponentes no banco de dados
    $query_hemocomponente  = "SELECT * FROM sth_hemocomponentes ORDER BY sigla";
    $result_hemocomponente = conecta_query($conexao, $query_hemocomponente);

    $_SESSION['id_paciente_atualiza'] = $row_paciente['id_paciente'];

    $pagina = isset($_GET["pagina"]) ? "&pagina=$_GET[pagina]" : "";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">

    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <title>Perfil do Paciente - HUM</title>
</head>

<body onload="carrega_dados()" >
    <?php include 'includes/header.php'; ?>
    <!-- Corpo -->
    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Perfil do Paciente</strong></p>
                <!-- Formulário -->
                <div id="formulario" class="container-perfil-paciente">
                    <!-- manda id do paciente por get  -->
                    <form action="atualiza.php" method="POST" id="form_perfil">

                        <?php
                            if (isset($_SESSION['paciente_atualizado'])){

                                if($_SESSION['paciente_atualizado'] == 0){
                                    exibir_mensagem_simples("Atualizado!", "Paciente atualizado com sucesso.", "success");
    
                                }else if ($_SESSION['paciente_atualizado'] == 1) {
                                    exibir_mensagem_simples("Paciente não atualizado!", "CPF ou prontuário já cadastrado.", "warning");
    
                                }else if ($_SESSION['paciente_atualizado'] == 2) {
                                    exibir_mensagem_simples("Paciente não atualizado!", "CPF ou prontuário precisam estar preenchidos.", "warning");  
                                
                                }else if ($_SESSION['paciente_atualizado'] == 3) {
                                    exibir_mensagem_simples("Paciente não atualizado!", "Número de registro já cadastrado.", "warning");
     
                                }else if ($_SESSION['paciente_atualizado'] == 4) {
                                    exibir_mensagem_simples("Paciente não atualizado!", "Número de registro não pode ser igual ao CPF ou prontuário.", "warning");
                                }

                                $_SESSION['paciente_atualizado'] =  -1;
                            }

                            if (isset($_SESSION['validado_paciente']) && $_SESSION['validado_paciente'] == 0) {
                                exibir_mensagem_simples("Cadastrado!", "Paciente cadastrado com sucesso.", "success");  
                            }

                            $_SESSION['validado_paciente'] = -1;
                        ?>

                        <!-- Exibir os dados do receptor -->
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6" style="display: flex;">
                                <div>
                                    <label for="dt_requisicao" class="required">Data de cadastro:</label><br>
                                    <input type="date" size="25" name="data_requisicao" id="dt_requisicao" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" 
                                    readonly="readOlny" <?php echo "value = '$row_paciente[dt_requisicao]'
                                    data-obs  = '$row_paciente[observacao]'
                                    data-abo  = '$row_paciente[abo]'
                                    data-rh   = '$row_paciente[rh_d]'
                                    data-sexo = '$row_paciente[sexo]'"; ?> required>
                                </div>

                                <div style="margin-left: 50px; margin-top:30px;">
                                    <?php
                                        $rn_sim = $row_paciente['rn'] == "sim" ? 'checked' : null;
                                        $rn_nao = $row_paciente['rn'] == "sim" ? null      : 'checked';
                                    ?>

                                    <label for="rn_sim" class="required">RN?</label>
                                    <input type="radio" name="recem_nascido_sim" id="rn_sim" value="sim" <?php echo $rn_sim; ?>> Sim
                                    <input type="radio" name="recem_nascido_nao" id="rn_nao" value="nao" <?php echo $rn_nao; ?>> Não
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="numero_rt" >Número RT: </label><br>
                                <input type="text" size="35" name="numero_rt" id="numero_rt" maxlength="255" value="<?php echo $row_paciente['numero_rt']; ?>" required>
                            </div>   
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="nome" class="required">Nome Completo: </label><br>
                                <input type="text" size="36" name="nome_completo" id="nome" value="<?php echo $row_paciente['nome_completo']; ?>" 
                                 oninput="adiciona_rn(this, '#rn_sim')" maxlength="255" required>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="num_sus">CNS:</label><br>
                                <input type="text" size="35" name="num_sus" id="num_sus" maxlength="18" oninput="validaEFormataCns(this)" 
                                value="<?php echo $row_paciente['numero_sus']; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6" style="display:flex;">
                                <div>
                                    <label for="dt_nascimento" class="required">Data de Nascimento: </label><br>
                                    <input type="date" size="35" name="dt_nascimento" id="dt_nascimento" value="<?php echo $row_paciente['dt_nasc']; ?>" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div style="margin-left:40px;">
                                    <label for="sexo" class="required">Sexo: </label><br>
                                    <select name="sexo" id="sexo" style="width:90px;" required>
                                        <option value = "F"> F </option>
                                        <option value = "M"> M </option>
                                        <option value = "Outro"> Outro </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6" style="display: flex;">
                                <div>
                                    <label for="abo" class="required">ABO: </label><br>
                                    <select name="abo" id="abo">
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
                                    <select name="rh" id="rh">
                                        <option value="Positivo">Positivo</option>
                                        <option value="Negativo">Negativo</option>
                                        <option value="Outro">Outro</option>
                                        <option value="Desconhecido">Desconhecido</option>'
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="nome_social">Nome Social Completo: </label><br>
                                <input type="text" name="nome_social" id="nome_social" maxlength="255" value="<?php echo $row_paciente['nome_social']; ?>" >
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="hospital">Hospital: </label><br>
                                <input type="text" size="36" name="hospital_internacao" id="hospital" value="<?php echo $row_paciente['hospital']; ?>" readonly="readOnly">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="prontuario" class="required">Prontuário:</label><br>
                                <input type="text" size="35" name="prontuario" id="prontuario" maxlength="15"
                                value="<?php echo $row_paciente['prontuario']; ?>" id="prontuario" oninput="apenasNumeros(this)" required>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="setor" class="required">Setor: </label><br>
                                <select name="setor" id="setor" style="width:390px;" required>
                                    <?php
                                        echo "<option value='$row_paciente[id_setor]'> $row_paciente[nome_setor] </option> 
                                        <optgroup label='Ativos'>";

                                        while ($row_setor = pg_fetch_assoc($result_setor_ativo)) {
                                            //gera options com setores diferentes do ja mostrado na tela
                                            if ($row_setor['nome_setor'] != $row_paciente['nome_setor']) {
                                                echo "<option value='$row_setor[id_setor]'> $row_setor[nome_setor] </option>";
                                            }
                                        }

                                        echo "</optgroup>";
                                    ?>
                                    <?php
                                        if(pg_num_rows($result_setor_inativo) > 0){
                                            echo '<optgroup label="Inativos">';

                                            while ($row_setor = pg_fetch_assoc($result_setor_inativo)) {
                                                //gera options com setores diferentes do ja mostrado na tela
                                                if ($row_setor['nome_setor'] != $row_paciente['nome_setor']) {
                                                    echo "<option value='$row_setor[id_setor]'> $row_setor[nome_setor] </option>";
                                                }
                                            }

                                            echo "</optgroup>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="registro" class="required">Registro: </label><br>
                                <input type="text" name="registro" id="registro" value="<?php echo $row_paciente['registro']; ?>" oninput="apenasNumeros(this)" maxlength="15" required>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="leito">Leito: </label><br>
                                <input type="text" size="36" name="leito" id="leito" maxlength="200" value="<?php echo $row_paciente['leito']; ?>">
                            </div>         
                        </div>

                        <div class="row" >
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="cpf" class="required">CPF: </label><br>
                                <input type="text" size="35" name="cpf" id="cpf" oninput="formatarCPF(this)" onblur="validarCPF(this)" maxlength="14" value="<?php echo $row_paciente['cpf']; ?>" required>
                            </div>

                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="diagnostico">Diagnóstico: </label><br>
                                <input type="text" name="diagnostico" id="diagnostico" maxlength="255" value="<?php echo $row_paciente['diagnostico']; ?>">
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:20px;">
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <div>
                                    <label for="mae" class="required">Nome da Mãe: </label><br>
                                    <input type="text" size="35" name="mae" id="mae" maxlength="255" value="<?php echo $row_paciente['nome_mae']; ?>" required>
                                </div> 
                                <div style="margin-bottom:20px; margin-top:40px;">
                                    <!--manda o id do paciente para o cadastro de bolsa-->
                                    <button type="button" class="btn botao-bolsa-reacao" onclick="window.location.href='cadastrar_bolsa.php?id_paciente=<?php echo $row_paciente['id_paciente'] . $pagina; ?>'">
                                        + Bolsa de Hemocomponente
                                    </button>
                                </div>
                                <div>
                                    <button type="button" class="btn botao-bolsa-reacao" onclick="window.location.href='reacao_transfusional.php?id_paciente=<?php echo $row_paciente['id_paciente'] . $pagina; ?>'">
                                        + Reações Transfusionais
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6">
                                <label for="observacao">Observação: </label><br>
                                <textarea name="observacao" id="observacao" cols="39" rows="8" maxlength="255" value="<?php echo $row_paciente['observacao']; ?>"></textarea>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-12">
                            <div class="botoes-perfil" style="display: flex;">
                                <input type="submit" onclick="removeRequired()" value="Atualizar Perfil" class="btn botao-verde" name="atualizar_paciente" id="atualizar_paciente_submit" >
                                <button type="button" onclick="window.location.href='buscar_paciente.php<?php if(isset($_GET['pagina'])){ echo '?pagina='.$_GET['pagina']; } ?>'" class="btn botao-voltar">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </button>
                                <?php $_SESSION['atualiza'] = "atualizar_paciente"; ?>
                            </div>
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Conteúdo da primeira ajuda -->
                    <p style="font-weight: bold;" >Como fazer alterações?</p>
                    <ul>
                        <li>Clique no campo que deseja alterar no perfil do paciente.</li>
                        <li>Altere no campo o dado desejado.</li>
                        <li>Depois de inserir, salve as alterações clicando em "Atualizar perfil".</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function carrega_dados(){
            var select = document.getElementById('dt_requisicao');
            document.getElementById('observacao').innerHTML = select.dataset.obs;
            document.getElementById('abo').value            = select.dataset.abo;
            document.getElementById('rh').value             = select.dataset.rh;
            document.getElementById('sexo').value           = select.dataset.sexo;
        }
        
        //select informacoes de bolsas
        function campo_bolsa() {
            var selectBolsa   = document.getElementById('id_bolsa');
            var selectedBolsa = selectBolsa.options[selectBolsa.selectedIndex];

            document.getElementById('num_bolsa').value        = selectedBolsa.text.split('|')[0].trim();
            document.getElementById('num_sus_bolsa').value    = selectedBolsa.dataset.numsus;
            document.getElementById('data_validade').value    = selectedBolsa.dataset.validade;
            document.getElementById('data_saida').value       = selectedBolsa.dataset.saida;
            document.getElementById('data_transfusao').value  = selectedBolsa.dataset.transfusao;
            document.getElementById('horario_inicio').value   = selectedBolsa.dataset.horario;
            document.getElementById('hemocomponente').value   = selectedBolsa.dataset.hemocomponente;
            document.getElementById('observacao_bolsa').value = selectedBolsa.dataset.observacao;
            document.getElementById('setor_livro').value      = selectedBolsa.dataset.livro;

            document.getElementById("aliquota_nao").setAttribute('checked',true);
            document.getElementById("reserva_nao").setAttribute('checked',true);

            if(selectedBolsa.dataset.reserva == "sim"){
                document.getElementById("reserva_sim").setAttribute('checked',true);
            }

            if(selectedBolsa.dataset.aliquota == "sim"){
                document.getElementById("aliquota_sim").setAttribute('checked',true);
            }

            notvisa = selectedBolsa.dataset.notvisa;
            shtnovo = selectedBolsa.dataset.shtnovo;

            if (notvisa == "ok") {
                document.getElementById("notvisa_sim").checked = true;
            } else {
                document.getElementById("notvisa_sim").checked = false;
            }

            if (shtnovo == "ok") {
                document.getElementById("shtnovo_sim").checked = true;
            } else {
                document.getElementById("shtnovo_sim").checked = false;
            }
            
            // console.log(document.getElementById('dt_validade').value);
        }

        function removeRequired() {
            document.getElementById("num_bolsa").removeAttribute("required"); 
            document.getElementById("num_sus_bolsa").removeAttribute("required"); 
            document.getElementById("hemocomponente").removeAttribute("required"); 
            document.getElementById("data_transfusao").removeAttribute("required"); 
            document.getElementById("reserva_sim").removeAttribute("required"); 
            document.getElementById("aliquota_sim").removeAttribute("required"); 
            document.getElementById("reserva_nao").removeAttribute("required"); 
            document.getElementById("aliquota_nao").removeAttribute("required"); 
        }

        //adicona RN- se o paciente estiver com rn selecionado sim
        function adiciona_rn(input, rn_sim){
            //verifica se rn selecionado é sim
            if(document.querySelector(rn_sim).checked){
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
                var nome   = document.getElementById("nome").value;
                
                if (nome.startsWith("RN-")) {  
                    var sem_rn = nome.substr(3); //tira rn-, se ja tiver selecionado rn sim, e querer trocar, nao é preciso escrever tudo dnv
                    document.getElementById("nome").value = sem_rn; // se trocar o documento pela variavel nome nao funciona
                }
            }
        });

        // BOTÃO DE CONFIRMAÇÃO DE ATUALIZAÇÃO
        const form = document.getElementById('form_perfil');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Você deseja atualizar?",
                text: "Você irá atualizar os dados!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, atualizar",
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>

</body>
</html>