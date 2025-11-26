<?php
    include "database.php";
    include "function.php";

    // inicializando variaveis
    $data_reg = $data_dev = $obs = $motivo = $bolsa = null;

    // Pega id do paciente
    if (isset($_GET['id_paciente'])) {
        $id_paciente             = $_GET['id_paciente'];
        $_SESSION['id_paciente'] = $id_paciente;
        $funcao_load             = null; // get id paciente nao acontece nada
    }

    $id_paciente     = isset($_GET['id_paciente']) ? (int) $_GET['id_paciente'] : (int) $_SESSION['id_paciente'];
    $get_id_paciente = "&id_paciente=$id_paciente";
    $action_form     = "insere.php?id_paciente=$id_paciente";
    $texto_submit    = "Devolver";

    // Consulta SQL para buscar dados da bolsa e do paciente, tras bolsa nao devolvidas e nao transfundidas
    $query_bolsa  = "SELECT cb.id_bolsa, cb.id_paciente, num_sus, num_bolsa, h.sigla, bd.id_bolsa as devolvida
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente 
    LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa
    LEFT JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa";

    // Pega o nome do paciente com preferencia no nome social
    $query_reg_paciente  = "SELECT nome_completo, nome_social FROM STH_Dados_Paciente WHERE id_paciente = $id_paciente";
    $result_reg_paciente = conecta_query($conexao, $query_reg_paciente);
    $row_reg_paciente    = pg_fetch_assoc($result_reg_paciente);

    $nome_paciente = !empty($row_reg_paciente['nome_social']) ? $row_reg_paciente['nome_social'] : $row_reg_paciente['nome_completo'];
    $pagina        = isset($_GET["pagina"])                   ? "&pagina=$_GET[pagina]"          : "";
    $voltar        = "cadastrar_bolsa.php?id_paciente=$id_paciente$pagina";

    if(isset($_GET['id_bolsa_devolvida'])){

        $query_bolsa .= " WHERE cb.id_paciente = $id_paciente and c.id_bolsa is null and bd.id_bolsa IS NULL or bd.id_bolsas_devolvidas = $_GET[id_bolsa_devolvida]
        ORDER BY cb.num_bolsa";

        $funcao_load  = "EditarDados()"; // carrega todos os dados na tela
        $voltar       = "bolsas_devolvidas.php?id_paciente=$id_paciente$pagina";
        $texto_submit = "Atualizar";

        //busca dados da bolsa devolvida ao editar
        $query_editar = "SELECT bd.*, cb.id_paciente FROM sth_bolsas_devolvidas bd
        INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = bd.id_bolsa
        WHERE bd.id_bolsas_devolvidas = $_GET[id_bolsa_devolvida]";
        $result_editar = conecta_query($conexao, $query_editar);
        $row_editar    = pg_fetch_assoc($result_editar);

        $data_reg    = $row_editar['dt_registro'];
        $data_dev    = $row_editar['dt_devolucao'];
        $obs         = $row_editar['observacao']; 
        $motivo      = $row_editar['motivo'];
        $bolsa       = $row_editar['id_bolsa'];
        $id_paciente = $row_editar['id_paciente'];

        $action_form = "atualiza.php?id_bolsa_devolvida=$_GET[id_bolsa_devolvida]"; //action do form, vai inserir se get id reacao
    }else{
        $query_bolsa .= " WHERE bd.id_bolsa IS NULL and c.id_bolsa is null and cb.id_paciente = $id_paciente
        ORDER BY cb.num_bolsa";
    }

    $result_bolsa = conecta_query($conexao, $query_bolsa);

    $query_bolsas_devolvidas = "SELECT cb.num_bolsa, num_sus, h.sigla, bd.dt_devolucao, id_bolsas_devolvidas, motivo
    FROM sth_bolsas_devolvidas bd
    INNER JOIN sth_cadastro_bolsa cb on cb.id_bolsa = bd.id_bolsa
    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
    WHERE cb.id_paciente = $id_paciente";

    $result_bolsas_devolvidas = conecta_query($conexao, $query_bolsas_devolvidas);

    $_SESSION['insere']   = "inserir_bolsa_devolvida";
    $_SESSION['atualiza'] = "atualizar_bolsa_devolvida";
?>
<!DOCTYPE html>
<html lang="pt-br">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">

    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Bolsas Devolvidas - HUM</title>
</head>

<body onload="<?php echo $funcao_load; ?>">
    <?php include 'includes/header.php'; ?>
    <!-- Corpo -->
    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Bolsas Devolvidas</strong></p>
                <!-- Formulário -->
                <div id="formulario" class="container-devolver-bolsa">
                    <form action="<?php echo $action_form; ?>" method="post" id="form_devolvida" >

                        <?php
                            if(isset($_SESSION['validado_bolsa_devolvida']) && $_SESSION['validado_bolsa_devolvida'] == 0){
                                exibir_mensagem_simples("Devolvida", "Sua bolsa foi devolvida com sucesso.", "success");
                            }
                            $_SESSION['validado_bolsa_devolvida'] = -1;

                            if(isset($_SESSION['validado_bolsa_devolvida_editar']) && $_SESSION['validado_bolsa_devolvida_editar'] == 0){
                                exibir_mensagem_simples("Atualizada!", "Sua bolsa foi atualizada com sucesso.", "success");
                            }
                            $_SESSION['validado_bolsa_devolvida_editar'] = -1;

                            if(isset($_SESSION['validado_bolsa_devolvida_excluir'])){

                                if($_SESSION['validado_bolsa_devolvida_excluir'] == 0){
                                    exibir_mensagem_simples("Excluida!", "Bolsa devolvida excluida com sucesso.", "success");
                                }

                                if($_SESSION['validado_bolsa_devolvida_excluir'] == 1){
                                    echo "<script>
                                            Swal.fire({
                                                title: 'Deseja excluir?',
                                                text: 'Você irá a excluir uma bolsa que foi devolvida!',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#d33',
                                                confirmButtonText: 'Sim, excluir',
                                                cancelButtonText: 'Cancelar'
                                                }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = 'exclui.php?arquivo=bolsa_devolvida$get_id_paciente&confirma=sim';
                                                }
                                            });
                                        </script>";
                                }

                                $_SESSION['validado_bolsa_devolvida_excluir'] = -1;
                            }
                        ?>

                        <div class="row" >
                            <div class="col-lg-5" style="display:flex;" >
                                <div style="margin-right:40px;">
                                    <label for="data_devolucao" class="required">Data de Devolução:</label><br>
                                    <input type="date" size="25" name="dt_devolucao" id="data_devolucao"  value="<?php echo date('Y-m-d'); ?>" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div>
                                    <label for="data_registro" class="required">Data de Registro:</label><br>
                                    <input type="date" size="25" name="dt_registro" id="data_registro" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" 
                                    <?php 
                                        echo "data-reg= '$data_reg' data-dev = '$data_dev' data-obs = '$obs' data-motivo = '$motivo' data-bolsa = '$bolsa'";
                                    ?>required>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <p>Paciente: <?php echo $nome_paciente; ?></p>
                            </div>
                        </div>

                        <div class="row" >
                            <div class="col-lg-5">
                                <label for="observacao">Observação:</label><br>
                                <textarea name="obs" id="observacao" maxlength="255" cols="34" rows="5"></textarea>
                            </div>
                            <div class="col-lg-7" >
                                <div class="row" >
                                    <div class="col-lg-12">
                                        <label for="motivo" class="required">Motivo: </label><br>
                                        <select name="motivo" id="motivo" required>
                                            <option value="">Selecione</option>
                                            <option id="cancelamento_reserva" value="Cancelamento de Reserva" >Cancelamento de Reserva</option>
                                            <option id="paciente_obito" value="Paciente foi a Óbito" >Paciente foi a Óbito</option>
                                            <option id="bolsa_vinculada_erroneamente" value="Bolsa Vinculada Erroneamente" >Bolsa Vinculada Erroneamente</option>
                                            <option id="cancelamento_de_transfusão" value="Cancelamento de Transfusão" >Cancelamento de Transfusão</option>
                                            <option id="recusa_paciente" value="Recusa do paciente" >Recusa do paciente</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="col-lg-12">
                                        <label for="bolsa" class="required">Bolsas cadastradas</label><br>
                                        <select name="bolsa" id="bolsa" required>
                                            <option value="">Selecione</option>
                                            <?php    
                                                // Traz as bolsas cadastradas
                                                while ($row_bolsa = pg_fetch_assoc($result_bolsa)) { 
                                                    echo "<option value='$row_bolsa[id_bolsa]' title='SUS: $row_bolsa[num_sus]'> 
                                                            $row_bolsa[num_bolsa] - $row_bolsa[sigla]
                                                        </option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="botoes-bolsa">
                            <input type="submit" name="inserir_bolsa_devolvida" id="inserir_bolsa_devolvida" class="btn botao-verde" value="<?php echo $texto_submit;?>">
                            <input type="reset" class="btn botao-limpar" value="Limpar Campos">
                            <button type="button" onclick="window.location.href='<?php echo $voltar; ?>'" class="btn botao-voltar">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </button>
                        </div>
                    </form>

                    <div style="height:250px; overflow:auto; margin-top:20px;">
                        <table class="table-striped" id="tabela_bolsa_devolvida" >
                            <tr class="cabecalho-tabela">
                                <th>Excluir</th>
                                <th>Data devolução</th>
                                <th>Num bolsa</th>
                                <th>Hemoc</th>  
                                <th>SUS</th>
                                <th>Motivo</th>
                                <th>Editar</th>
                            </tr>
                        
                            <!-- Dados dos pacientes -->
                            <?php
                                if (!$result_bolsas_devolvidas) {
                                    echo "<tr>
                                            <td colspan='7'>Erro ao gerar a query</td> 
                                        </tr>";
                                }else{
                                    if(pg_num_rows($result_bolsas_devolvidas) > 0) {
                                        
                                        while ($row_bolsa_devolvida = pg_fetch_assoc($result_bolsas_devolvidas)) {

                                            $dt_devolucao = date('d/m/Y', strtotime($row_bolsa_devolvida['dt_devolucao']));

                                            echo "<tr>
                                                    <td onclick='pega_id($row_bolsa_devolvida[id_bolsas_devolvidas])'>
                                                        <i class='fas fa-trash-alt' style='color: red;'></i>
                                                    </td>
                                                    <td>$dt_devolucao</td>
                                                    <td style='text-align: left;'>$row_bolsa_devolvida[num_bolsa]</td>
                                                    <td style='text-align: left;'>$row_bolsa_devolvida[sigla]</td>
                                                    <td>$row_bolsa_devolvida[num_sus]</td>
                                                    <td style='text-align: left;'>$row_bolsa_devolvida[motivo] </td> 
                                                    <td>
                                                        <a href='bolsas_devolvidas?id_bolsa_devolvida=$row_bolsa_devolvida[id_bolsas_devolvidas]$pagina'>
                                                            <i class='fas fa-pencil-alt'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                        }
                                        
                                    }else{
                                        echo "<tr>
                                                <td colspan='7'>Nenhum registo encontrado</td> 
                                            </tr>";
                                    }                             
                                }
                            ?>
                        </table>
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
                    <form action="exclui.php?arquivo=bolsa_devolvida<?php echo $get_id_paciente; ?>" method="POST" name="form_motivo" id="form_motivo" >
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Devolução</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;">Como fazer uma devolução?</p>
                    <ul>
                        <li>Data de Registro: Coloque a data em que a bolsa foi registrada. É importante para rastreamento.</li>
                        <li>Data de Devolução: Insira a data de hoje ou quando a bolsa será devolvida.</li>
                        <li>Observação: Se houver algo especial sobre a devolução, adicione aqui.</li>
                        <li>Motivo: Escolha o motivo da devolução.</li>
                        <li>Bolsas cadastradas: Escolha a bolsa que você está devolvendo.</li>
                        <li>Verifique tudo.</li>
                        <li>Depois de confirmar, clique em "Devolver" para registrar a devolução.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function pega_id(id_bolsa){
            $('#motivo_exclusao').modal('show');
            document.getElementById("id_bolsa").value = id_bolsa
        }

        //carrega os dados para aparecer na tela qundo for editar
        function EditarDados(){
            var selectDados = document.getElementById("data_registro");

            document.getElementById("data_registro").value  = selectDados.dataset.reg;
            document.getElementById("data_devolucao").value = selectDados.dataset.dev;
            document.getElementById("observacao").innerHTML = selectDados.dataset.obs;
            document.getElementById("motivo").value         = selectDados.dataset.motivo;
            document.getElementById("bolsa").value          = selectDados.dataset.bolsa;
        }

        var btn_submit = document.getElementById("inserir_bolsa_devolvida").value.toLowerCase();

        // BOTÃO DE CONFIRMAÇÃO DE INSERÇÃO DE REAÇÃO TRANSFUSIONAL
        const form = document.getElementById('form_devolvida');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja " + btn_submit + "?",
                text: "Você irá " + btn_submit + " uma reação transfusional!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, " + btn_submit,
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>

</body>
</html>