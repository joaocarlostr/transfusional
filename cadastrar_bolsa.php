<?php
    include "database.php";
    include "function.php";

    //inicializando variaveis
    $id_hemocomponente = $num_bolsa = $num_sus = $dt_transfusao = $saida       = $horario     = $observacao = 
    $aliquota          = $reserva   = $shtnovo = $notvisa       = $livro_setor = $funcao_load = $obito      = null;

    $texto_submit = "Adicionar"; //coloca texto no botao submit

    //guarda a pagina do buscar
    $pagina = isset($_GET["pagina"]) ? "&pagina=$_GET[pagina]" : "";

    //envia id do paciente para insere.php por session
    if(isset($_GET['id_paciente'])){
        $_SESSION['id_paciente_bolsa']  = $_GET['id_paciente']; // pega id do paciente
    }

    $id_paciente     = isset($_GET['id_paciente']) ? $_GET['id_paciente'] : (int) $_SESSION['id_paciente_bolsa']; // pega id paciente inteiro
    $get_id_paciente = "&id_paciente=$id_paciente"; //add get só quando a pagina atual possuir get id_paciente
    $voltar          = "perfil_paciente.php?id_paciente=$id_paciente$pagina";
    $action_form     = "insere.php?id_paciente=$id_paciente";

    if(isset($_GET['id_bolsa'])){

        $texto_submit = "Atualizar"; // texto do botao submit
        $voltar       = "cadastrar_bolsa.php?id_paciente=$id_paciente$pagina"; //action do form, vai atualizar se get id reacao
        $funcao_load  = "EditarDados()"; // carrega todos os dados na tela

        //Consulta a bolsa especifica
        $query_editar = "SELECT * FROM sth_cadastro_bolsa WHERE id_bolsa = $_GET[id_bolsa]";

        $result_editar = conecta_query($conexao, $query_editar);
        $row_editar    = pg_fetch_assoc($result_editar);

        $id_hemocomponente = $row_editar["id_hemocomponente"];
        $num_bolsa         = $row_editar["num_bolsa"];
        $num_sus           = $row_editar["num_sus"];
        $dt_transfusao     = $row_editar["data_transfusao"];
        $saida             = $row_editar["dt_saida"];
        $horario           = $row_editar["horario_inicio"];
        $observacao        = $row_editar["observacao"];
        $aliquota          = $row_editar["aliquota"];
        $reserva           = $row_editar["reserva"];
        $shtnovo           = $row_editar["shtnovo"];
        $notvisa           = $row_editar["notvisa"];
        $livro_setor       = $row_editar["id_livro_setor"];
        $obito             = $row_editar["obito"];

        $action_form = "atualiza.php?id_bolsa=$_GET[id_bolsa]&id_paciente=$id_paciente"; //action do form, vai inserir se get id reacao
    }

    // -------------------------------------------------------------------------------------------------------------------------------------

    // Consulta SQL para buscar os hemocomponentes no banco de dados
    $query_hemocomponente  = "SELECT * FROM sth_hemocomponentes ORDER BY sigla";
    $result_hemocomponente = conecta_query($conexao, $query_hemocomponente);

    //pega o nome do paciente dando preferencia ao nome social
    $query_reg_paciente  = "SELECT nome_completo, nome_social FROM STH_Dados_Paciente WHERE id_paciente = $id_paciente";
    $result_reg_paciente = conecta_query($conexao, $query_reg_paciente);
    $row_reg_paciente    = pg_fetch_assoc($result_reg_paciente); 

    $nome_paciente = !empty($row_reg_paciente['nome_social']) ? $row_reg_paciente['nome_social'] : $row_reg_paciente['nome_completo'];

    // Consulta SQL para buscar os setores ativos no banco de dados 
    $query_setor              = "SELECT * FROM sth_setores WHERE status='ativo' ORDER BY nome_setor DESC";
    $result_setor_livro_ativo = conecta_query($conexao, $query_setor);

    // Consulta SQL para buscar os setores inativos no banco de dados
    $query_setor                = "SELECT * FROM sth_setores WHERE status='' ORDER BY nome_setor DESC";
    $result_setor_livro_inativo = conecta_query($conexao, $query_setor);

    // Buscar campos da bolsa se ela ja estiver cadastrada | EM TESTE
    $query_bolsa = "SELECT cb.*, h.sigla FROM sth_cadastro_bolsa cb 
    INNER JOIN sth_hemocomponentes h on h.id_hemocomponente = cb.id_hemocomponente
    LEFT JOIN sth_bolsas_devolvidas bd on bd.id_bolsa = cb.id_bolsa
    WHERE bd.id_bolsa IS NULL AND id_paciente = $id_paciente
    ORDER BY cb.data_transfusao, horario_inicio, num_bolsa";
    $result_bolsa = conecta_query($conexao, $query_bolsa);

    //SESIONS
    $_SESSION['insere']   = "adicionar_bolsa";
    $_SESSION['atualiza'] = "atualizar_bolsa";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="css/style.css">

    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>

    <title>Adicionar hemocomponente - HUM</title>
</head>

<body onload="<?php echo $funcao_load; ?>">
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Adicionar hemocomponente</strong></p>
                <div id="formulario" class="container-cadastrar-bolsa">
                    <form action="<?php echo $action_form; ?>" method="post" id="form_bolsa" >
                        <?php
                            if (isset($_SESSION['validado_bolsa'])) {
                                if ($_SESSION['validado_bolsa'] == 1) {
                                    exibir_mensagem_simples("Número da bolsa já cadastrado!", "Bolsa adicionada como alíquota.", "success");

                                } elseif ($_SESSION['validado_bolsa'] == 0) {
                                    exibir_mensagem_simples("Cadastrada!", "Bolsa adicionada com sucesso.", "success");

                                } elseif ($_SESSION['validado_bolsa'] == 2) {
                                    exibir_mensagem_simples("Bolsa não cadastrada!", "Bolsa adicionada anteriormente como não alíquota.", "warning");

                                } elseif ($_SESSION['validado_bolsa'] == 3) {
                                    exibir_mensagem_simples("Bolsa não cadastrada!", "Esse número do SUS já foi cadastrado em uma bolsa diferente.", "warning");
                                }

                                $_SESSION['validado_bolsa'] = -1;
                            }
                            
                            if (isset($_SESSION['bolsa_atualizada'])) {
                                if ($_SESSION['bolsa_atualizada'] == 1) {
                                    exibir_mensagem_simples("Número da bolsa já cadastrado!", "Bolsa adicionada como alíquota.", "success");

                                } elseif ($_SESSION['bolsa_atualizada'] == 2) {
                                    exibir_mensagem_simples("Bolsa não cadastrada!", "Bolsa adicionada anteriormente como não alíquota.", "warning");

                                } elseif ($_SESSION['bolsa_atualizada'] == 3) {
                                    exibir_mensagem_simples("Bolsa não cadastrada!", "Esse número do SUS já foi cadastrado em uma bolsa diferente.", "warning");
                                
                                } elseif($_SESSION['bolsa_atualizada'] == 0){
                                    exibir_mensagem_simples("Atualizada!", "Bolsa atualizada com sucesso.", "success");
                                }

                                $_SESSION['bolsa_atualizada'] = -1;
                            }

                            if(isset($_SESSION['validado_bolsa_excluir'])){
                                
                                if($_SESSION['validado_bolsa_excluir'] == 1){
                                    echo "<script>
                                            Swal.fire({
                                                title: 'Deseja excluir?',
                                                text: 'Você irá a excluir uma bolsa!',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#d33',
                                                confirmButtonText: 'Sim, excluir',
                                                cancelButtonText: 'Cancelar'
                                                }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = 'exclui.php?arquivo=bolsa&id_bolsa=$_GET[id_bolsa]$get_id_paciente&confirma=sim';
                                                }
                                            });
                                        </script>";
                                }elseif($_SESSION['validado_bolsa_excluir'] == 2){
                                    exibir_mensagem_simples("Não foi possível excluir a bolsa.", "Sua bolsa possui reações transfusionais vinculadas, é necessário exclui-las primeiro.", "warning");
                                
                                }else if($_SESSION['validado_bolsa_excluir'] == 0){
                                    exibir_mensagem_simples("Excluida!", "Sua bolsa foi excluida com sucesso.", "success");
                                }

                                $_SESSION['validado_bolsa_excluir'] = -1;
                            }
                        ?>

                        <div class="row">
                            <div class="col-lg-6">
                                <label for="hemocomponente" class="required">Tipo do Hemocomponente:</label><br>
                                <select name="hemocomponente" id="hemocomponente" style="width:455px;" required>
                                    <option value="">Selecione</option>
                                    <?php
                                        //gera options de todos os hemocomponentes cadastrados
                                        while ($row_hemocomponente = pg_fetch_assoc($result_hemocomponente)) {
                                            echo "<option value='$row_hemocomponente[id_hemocomponente]'> $row_hemocomponente[sigla] - $row_hemocomponente[descricao] </option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <p>Paciente: <?php echo $nome_paciente; ?></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <label for="num_bolsa" class="required" >Nº da Bolsa:</label><br>
                                <input type="text" name="numero_bolsa" id="num_bolsa" placeholder="B" oninput="formatarCodigo(this)" minlength="13" maxlength="13"
                                <?php echo "data-hemocomponente = '$id_hemocomponente' 
                                            data-bolsa          = '$num_bolsa' 
                                            data-sus            = '$num_sus' 
                                            data-dttransfusao   = '$dt_transfusao' 
                                            data-dtsaida        = '$saida' 
                                            data-horario        = '$horario' 
                                            data-observacao     = '$observacao'
                                            data-aliquota       = '$aliquota'
                                            data-reserva        = '$reserva'
                                            data-shtnovo        = '$shtnovo'
                                            data-notvisa        = '$notvisa'
                                            data-livrosetor     = '$livro_setor'
                                            data-obito          = '$obito'"; ?> required>
                            </div>
                            <div class="col-lg-6" >
                                <label for="setor_livro">Livro setor:</label><br>
                                <select name="setor_livro" id="setor_livro">
                                    <option value="">Selecione</option>
                                    <optgroup label="Ativos" >
                                        <?php
                                            // gera options de todos os setores ativos cadastrados
                                            while ($row_setor_livro = pg_fetch_assoc($result_setor_livro_ativo)) {
                                                echo "<option value='$row_setor_livro[id_setor]'> $row_setor_livro[nome_setor] </option>"; 
                                                // cuidado com espaços depois e antes das aspas
                                            }
                                        ?>
                                    </optgroup>
                                    <?php
                                        // gera options de todos os setores ativos cadastrados
                                        if(pg_num_rows($result_setor_livro_inativo) > 0){
                                            echo "<optgroup label='Inativos'>";

                                            while ($row_setor_livro = pg_fetch_assoc($result_setor_livro_inativo)) {
                                                echo "<option value='$row_setor_livro[id_setor]'> $row_setor_livro[nome_setor] </option>"; 
                                                // cuidado com espaços depois e antes das aspas
                                            }
                                            
                                            echo "</optgroup>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="num_sus_bolsa" class="required">Número SUS da bolsa:</label><br>
                                        <input type="text" name="num_sus_bolsa" id="num_sus_bolsa" maxlength="11" oninput="validarSUSBolsa(this)" style="width: 455px;" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12" style="display:flex;">
                                        <div style="margin-right:20px;">
                                            <label for="data_transfusao" class="required">Data de transfusão:</label><br>
                                            <input type="date" name="data_transfusao" id="data_transfusao" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div style="margin-right:20px;">
                                            <label for="horario_inicio" class="required">Horário:</label><br>
                                            <input type="time" name="horario_inicio" id="horario_inicio" required>
                                        </div>
                                        <div>
                                            <label for="data_saida" class="required">Saída do hemoc.: </label><br>
                                            <input type="date" name="dt_saida" id="data_saida" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" oninput="validarDatas(this)" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label for="observacao">Observação:</label><br>
                                <textarea name="observacao" id="observacao" cols="40" rows="4" style="width: 455px;" maxlength="255"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div style="margin-top: 20px;" >
                                    NOTIVISA <input type="checkbox" name="notvisa" id="notvisa" value="ok" style="margin-right:15px;">
                                    SHTNOVO  <input type="checkbox" name="shtnovo" id="shtnovo" value="ok" style="margin-right:15px;">
                                    ÓBITO  <input type="checkbox" name="obito" id="obito" value="sim">
                                </div>
                            </div>
                            <div class="col-lg-6" style="display:flex;">
                                <div style="margin-right:70px;">
                                    <label for="reserva_sim" class="required">Reserva:</label><br>
                                    <input type="radio" name="reserva" id="reserva_sim" value="sim" required> Sim
                                    <input type="radio" name="reserva" id="reserva_nao" value="nao" required> Não
                                </div>
                                <div>
                                    <label for="aliquota_sim" class="required">Alíquota:</label><br>
                                    <input type="radio" name="aliquota" id="aliquota_sim" value="sim" required> Sim
                                    <input type="radio" name="aliquota" id="aliquota_nao" value="nao" required> Não
                                </div>
                            </div>
                        </div>

                        <div class="botoes-bolsa">
                            <input type="submit" name="adicionar_bolsa" value="<?php echo $texto_submit; ?>" class=" btn botao-verde">
                            <input type="reset"  value="Limpar Campos" class=" btn botao-limpar" onclick="limpa_select2()">
                            <button type="button" onclick="window.location.href='<?php echo $voltar;?>'" class="btn botao-voltar">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </button>
                        </div>
                    </form>

                    <div style="max-height:300px; overflow:auto; margin-top:20px;">
                        <table class="table-striped" id="tabela_bolsa" >
                            <tr class="cabecalho-tabela">
                                <th>Excluir</th>
                                <th>Data transfusão</th>
                                <th>Horário</th>
                                <th>Num bolsa</th>
                                <th>Hemoc</th>
                                <th>sus</th>
                                <th>Data saída</th>
                                <th>Editar</th>
                            </tr>
                        
                            <?php
                                $controle_devolver = "";

                                if (!$result_bolsa) {
                                    echo "<tr>
                                            <td colspan='8'>Erro ao gerar a query</td> 
                                        </tr>";
                                }else{
                                    if(pg_num_rows($result_bolsa) > 0) {
                                        while ($row_bolsa = pg_fetch_assoc($result_bolsa)) {

                                            $data_saida      = !empty($row_bolsa['dt_saida']) ? date('d/m/Y', strtotime($row_bolsa['dt_saida'])) : '';
                                            $data_transfusao = date('d/m/Y', strtotime($row_bolsa['data_transfusao']));
                                            $hora_minuto     = SubStr($row_bolsa['horario_inicio'], 0, 5);

                                            echo "<tr>
                                                    <td onclick='pega_id($row_bolsa[id_bolsa])'>
                                                        <i class='fas fa-trash-alt' style='color: red;'></i>
                                                    </td>
                                                    <td>$data_transfusao</td> 
                                                    <td>$hora_minuto</td>
                                                    <td style='text-align: left;'>$row_bolsa[num_bolsa]</td>
                                                    <td style='text-align: left;'>$row_bolsa[sigla]</td> 
                                                    <td>$row_bolsa[num_sus]</td> 
                                                    <td>$data_saida</td> 
                                                    <td><a href='cadastrar_bolsa.php?id_bolsa=$row_bolsa[id_bolsa]$pagina'><i class='fas fa-pencil-alt'></i></a></td>
                                                </tr>";
                                        }
                                    }else{
                                        echo "<tr>
                                                <td colspan='8'>Nenhum registo encontrado.</td> 
                                            </tr>";
                                        $controle_devolver = "disabled";
                                     }                                                                  
                                }
                            ?>
                        </table>
                    </div>

                    <div class="botoes-bolsa" >
                        <button type="button" <?php echo $controle_devolver; ?> class="btn controle-devolver" onclick="window.location.href='controle.php?id_paciente_selecionado=<?php echo $id_paciente . $pagina; ?>'">
                            <i class='fas fa-file' style='color: white;'></i> Controle
                        </button>
                        <button type="button" <?php echo $controle_devolver; ?> class="btn controle-devolver" onclick="window.location.href='bolsas_devolvidas.php?id_paciente=<?php echo $id_paciente . $pagina; ?>'">
                            Devolver bolsa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal motivo exclusao -->
    <div class="modal" id="motivo_exclusao" role="dialog" tabindex="-1" aria-labelledby="motivo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; width: 100%;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title required" id="info_unificar">Digite o motivo da exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form action="exclui.php?arquivo=bolsa<?php echo $get_id_paciente; ?>" method="POST" name="form_motivo" id="form_motivo" >
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="motivo"></label>
                                <textarea name="motivo" id="motivo" cols="30" rows="10" required></textarea>
                                <input type="hidden" name="id_bolsa" id="id_bolsa">
                            </div>
                        </div>
                        <div class="row" >
                            <div class="col-lg-12 botoes-unificar">
                                <button type="submit" class="botao-verde" name="btn_motivo">Avançar</button>
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Bolsa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;">Como adicionar uma bolsa?</p>
                    <ul>
                        <li>Uma vez na página, preencha os campos obrigatórios.</li>
                        <li>Antes de prosseguir, verifique se todas as informações inseridas estão corretas e precisas.</li>
                        <li>Depois de revisar e preencher todos os detalhes necessários, confirme a adição da bolsa sanguínea clicando no botão "Adicionar".</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // function teste_aliquota(){
        //     var bolsa = document.getElementById("num_bolsa").value;
        //     console.log(bolsa);
        // }

        //chama o script para pesquisa no select de pacientes
        //.select2 dentro das aspas é o nome da classe
        $(document).ready(function() {
            $(".select2").select2();
        });

        //volta ao index 0 do select2
        function limpa_select2() {
            $('.select2').val(null).trigger('change');
        }

        //pega o id da bolsa antes que percamos
        function pega_id(id_bolsa){
            $('#motivo_exclusao').modal('show');
            document.getElementById("id_bolsa").value = id_bolsa
        }

        //carrega os dados para aparecer na tela qundo for editar
        function EditarDados(){
            var selectDados = document.getElementById("num_bolsa");

            document.getElementById("hemocomponente").value  = selectDados.dataset.hemocomponente;
            document.getElementById("num_bolsa").value       = selectDados.dataset.bolsa;
            document.getElementById("num_sus_bolsa").value   = selectDados.dataset.sus;
            document.getElementById("data_transfusao").value = selectDados.dataset.dttransfusao;
            document.getElementById("data_saida").value      = selectDados.dataset.dtsaida;
            document.getElementById("horario_inicio").value  = selectDados.dataset.horario;
            document.getElementById("observacao").innerHTML  = selectDados.dataset.observacao;
            document.getElementById("setor_livro").value     = selectDados.dataset.livrosetor;

            document.getElementById("aliquota_sim").checked = false;
            document.getElementById("aliquota_nao").checked = true;
            document.getElementById("reserva_sim").checked  = false;
            document.getElementById("reserva_nao").checked  = true;

            //campos radio
            if(selectDados.dataset.aliquota == "sim"){
                document.getElementById("aliquota_sim").checked = true;
                document.getElementById("aliquota_nao").checked = false;
            }

            if(selectDados.dataset.reserva == "sim"){
                document.getElementById("reserva_sim").checked = true;
                document.getElementById("reserva_nao").checked = false;
            }

            //campos checkbox
            document.getElementById("notvisa").checked = false;
            document.getElementById("shtnovo").checked = false;
            document.getElementById("obito").checked   = false;

            if(selectDados.dataset.notvisa == "ok"){
                document.getElementById("notvisa").checked = true;
            }

            if(selectDados.dataset.shtnovo == "ok"){
                document.getElementById("shtnovo").checked = true;
            }

            if(selectDados.dataset.obito == "sim"){
                document.getElementById("obito").checked = true;
            }
        }

        //valida campos datas
        function validarDatas(input) {
            const data_transfusao = new Date(document.getElementById('data_transfusao').value);
            const data_saida      = new Date(input.value);
            input.setCustomValidity('');

            if (data_transfusao < data_saida) {
                input.setCustomValidity('Data de saida não pode ser maior que a data de transfusao');
            }
        }

        // BOTÃO DE CONFIRMAÇÃO DE CADASTRO DE BOLSA
        const form = document.getElementById('form_bolsa');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja adicionar?",
                text: "Você irá adicionar uma nova bolsa!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, adicionar",
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>

    <!-- script select2 busca de paciente / só funciona se ficar aqui embaixo-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>

</body>
</html>