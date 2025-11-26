<?php
    include "database.php";
    include "function.php";

    $data = $hora = $bolsa = $obs = $notificacao = $id_tipo = $nome_tipo = $funcao_load = null;

    $pagina = isset($_GET["pagina"]) ? "&pagina=$_GET[pagina]" : "";

    if(isset($_GET['id_paciente'])){
        $_SESSION['id_paciente_reacao'] = $_GET['id_paciente']; // pega id do paciente
        $texto_submit                   = "Adicionar"; //coloca texto no botao submit
    }

    $id_paciente     = isset($_GET['id_paciente']) ? $_GET['id_paciente'] : (int) $_SESSION['id_paciente_reacao']; // pega id paciente inteiro
    $get_id_paciente = "&id_paciente=$id_paciente";
    $voltar          = "perfil_paciente.php?id_paciente=$id_paciente$pagina";
    $action_form     = "insere.php?id_paciente=$id_paciente";

    if(isset($_GET['id_reacao'])){

        $texto_submit = "Atualizar"; // texto do botao submit
        $voltar       = "reacao_transfusional.php?id_paciente=$id_paciente$pagina"; //action do form, vai atualizar se get id reacao
        $funcao_load  = "EditarDados()"; // carrega todos os dados na tela

        //Consulta a reacao transfusional especifica
        $query_editar = "SELECT t.nome, rt.* FROM STH_reacoes_transfusionais rt
        INNER JOIN sth_tipos_reacoes t ON t.id_reacao = rt.tipo_reacao
        INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = rt.id_bolsa
        WHERE rt.id_transfusionais = $_GET[id_reacao]";

        $result_editar = conecta_query($conexao, $query_editar);
        $row_editar    = pg_fetch_assoc($result_editar);

        $data        = $row_editar['data'];
        $hora        = $row_editar['hora'];
        $bolsa       = $row_editar['id_bolsa']; 
        $obs         = $row_editar['observacao']; 
        $notificacao = $row_editar['num_notificacao'];
        $id_tipo     = $row_editar['tipo_reacao'];
        $nome_tipo   = $row_editar['nome'];

        $action_form = "atualiza.php?id_reacao=$_GET[id_reacao]"; //action do form, vai inserir se get id reacao
    }

    // Consulta SQL para reações imediatas
    $query_reacoes_imediatas  = "SELECT * FROM STH_Tipos_Reacoes WHERE nome = 'Reações Imediatas'";
    $result_reacoes_imediatas = conecta_query($conexao, $query_reacoes_imediatas);

    // Consulta SQL para reações tardias
    $query_reacoes_tardias  = "SELECT * FROM STH_Tipos_Reacoes WHERE nome = 'Reações Tardias'";
    $result_reacoes_tardias = conecta_query($conexao, $query_reacoes_tardias);

    //Consulta SQL para buscar todos os registros de reações do paciente selecionado
    $query_reg_reacao = "SELECT t.sigla, nome, descricao, rt.*, cb.num_bolsa FROM STH_reacoes_transfusionais rt
    INNER JOIN sth_tipos_reacoes t ON t.id_reacao = rt.tipo_reacao
    INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = rt.id_bolsa
    WHERE cb.id_paciente = $id_paciente ORDER BY rt.data, hora";
    
    $result_reg_reacao = conecta_query($conexao, $query_reg_reacao);

    //pega o nome do paciente
    $query_reg_paciente  = "SELECT nome_completo, nome_social FROM STH_Dados_Paciente WHERE id_paciente = $id_paciente";
    $result_reg_paciente = conecta_query($conexao, $query_reg_paciente);
    $row_reg_paciente    = pg_fetch_assoc($result_reg_paciente);

    $nome_paciente = !empty($row_reg_paciente['nome_social']) ? $row_reg_paciente['nome_social'] : $row_reg_paciente['nome_completo'];

    //pega bolsa desse paciente
    $query_bolsa = "SELECT cb.num_bolsa, cb.id_bolsa, h.sigla FROM sth_cadastro_bolsa cb
    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
    INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
    LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa
    LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa
    WHERE cb.data_transfusao IS NOT NULL and cb.id_paciente = $id_paciente and bd.id_bolsa IS NULL
    ORDER BY num_bolsa";

    $result_bolsa = conecta_query($conexao, $query_bolsa);

    $_SESSION['insere']   = "inserir_reacao";
    $_SESSION["atualiza"] = "atualiza_reacao";
    // $_SESSION["exclui"]   = "exclui_reacao";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

    <title>Reações Transfusionais - HUM</title>
</head>

<body onload="<?php echo $funcao_load; ?>" >
    <?php include 'includes/header.php'; ?>
    <!-- Corpo -->
    <div class="container">
        <div class="fundo-imagem">
            <div id="corpo" class="borda">
                <p class="mensagem-borda"><strong>Reações Transfusionais</strong></p>
                <!-- Formulário -->
                <div id="formulario" class="container-reacao" >
                    <form action="<?php echo $action_form; ?>" method="POST" name="form" id="form_reacao" >
                        <div class="row" >

                            <?php 
                                if(isset($_SESSION['validado_reacao']) && $_SESSION['validado_reacao'] == 0){
                                    exibir_mensagem_simples("Adicionado!", "Reação transfusional adicionada com sucesso.", "success");
                                } 

                                $_SESSION['validado_reacao'] = -1;

                                if(isset($_SESSION['validado_reacao_editar']) && $_SESSION['validado_reacao_editar'] == 0){
                                    exibir_mensagem_simples("Atualizado!", "Reação transfusional atualizada com sucesso.", "success");
                                }

                                $_SESSION['validado_reacao_editar'] = -1;

                                if(isset($_SESSION['validado_reacao_excluir'])){
                                    if($_SESSION['validado_reacao_excluir'] == 0){
                                        exibir_mensagem_simples("Excluido!", "Reação transfusional excluida com sucesso.", "success");
                                    }
    
                                    if($_SESSION['validado_reacao_excluir'] == 1){
                                        echo "<script>
                                                Swal.fire({
                                                    title: 'Deseja excluir?',
                                                    text: 'Você irá a excluir uma reação transfusional!',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#3085d6',
                                                    cancelButtonColor: '#d33',
                                                    confirmButtonText: 'Sim, excluir',
                                                    cancelButtonText: 'Cancelar'
                                                    }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        window.location.href = 'exclui.php?arquivo=reacao$get_id_paciente&confirma=sim';
                                                    }
                                                });
                                            </script>";
                                    }
                                    
                                    $_SESSION['validado_reacao_excluir'] = -1;
                                }
                            ?>

                            <div class="col-lg-5"  >
                                <div class="row" >
                                    <div class="col-lg-12" style="display:flex;" >
                                        <div style="margin-right:20px;">
                                            <label for="data_reacao" class="required">Data da Reação:</label><br>
                                            <input type="date" size="25" name="data_reacao" id="data_reacao" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" 
                                            oninput="dateMaskH(this, event)" required>
                                        </div>
                                        <div>
                                            <label for="hora" class="required">Hora da Reação:</label><br>
                                            <input type="time" size="25" name="hora_reacao" id="hora"
                                            <?php
                                                echo "data-dt = '$data' data-hora='$hora' data-bolsa='$bolsa' data-obs='$obs' 
                                                data-notificacao='$notificacao' data-idtipo='$id_tipo' data-nometipo='$nome_tipo'";
                                            ?> required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" >
                                    <div class="col-lg-12" >
                                        <label for="num_bolsa" class="required">N° da bolsa / Hemocomponente:</label><br>
                                        <select name="num_bolsa" id="num_bolsa" class="select2" style="width: 347px;" required>
                                            <option value="">Selecione</option>
                                            <?php    
                                                //tras as bolsas cadastradas
                                                while ($row_bolsa = pg_fetch_assoc($result_bolsa)) { 
                                                    echo "<option value='$row_bolsa[id_bolsa]'> $row_bolsa[num_bolsa] - $row_bolsa[sigla] </option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row" >
                                    <div class="col-lg-6">
                                        <label for="observacao" class="required">Observação:</label><br>
                                        <textarea name="observacao" id="observacao" cols="30" rows="3" maxlength="255" required></textarea>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-7" >
                                <div class="row" >
                                    <div class="col-lg-12" >
                                        <p>Paciente: <?php echo $nome_paciente; ?></p>
                                    </div>
                                </div>

                                <div class="row" >
                                    <div class="col-lg-6" >
                                        <label for="num_notificacao" class="required">N° da notificação:</label>
                                        <input type="text" oninput="formatarnotificacao(this)" name="num_notificacao" id="num_notificacao" size="15" maxlength="14" required>
                                    </div>
                                </div>

                                <div class="row" >
                                    <div class="col-lg-6" >
                                        <label for="tipo_1" class="required">Tipo: </label><br>
                                        <input type="radio" name="tipo_reacao" id="tipo_1" value="1"  required> Imediata
                                        <input type="radio" name="tipo_reacao" id="tipo_2" value="2"  required> Tardia
                                    </div>
                                </div>

                                <div class="row" >
                                    <div class="col-lg-6" >
                                        <div id="imediata_reacoes" style="display: none">
                                            <!-- reações imediatas -->
                                            <label for="reacoes_imediatas" class="required">Reacoes:</label><br>
                                            <select name="reacoes_imediatas" id="reacoes_imediatas" style="width:550px;" required>
                                                <option value="">Selecione</option>                 
                                                <?php
                                                    while ($row_imediata = pg_fetch_assoc($result_reacoes_imediatas)) {
                                                        echo "<option value='$row_imediata[id_reacao]'>
                                                                $row_imediata[cod] - $row_imediata[sigla] - $row_imediata[descricao] 
                                                            </option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div id="tardia_reacoes" style="display: none">
                                            <!-- reações tardias -->
                                            <label for="reacoes_tardias" class="required">Reacoes:</label><br>
                                            <select name="reacoes_tardias" id="reacoes_tardias" style="width:550px;" required>
                                                <option value="">Selecione</option>
                                                <?php
                                                    while ($row_tardias = pg_fetch_assoc($result_reacoes_tardias)) {
                                                        echo "<option value='$row_tardias[id_reacao]'>
                                                                $row_tardias[cod] - $row_tardias[sigla] - $row_tardias[descricao]
                                                            </option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>
                        <div class="botoes-reacao" style="margin-top:-20px;" >
                            <input type="submit" class="btn botao-verde"  value="<?php echo $texto_submit;?>" name="inserir_reacao" id="inserir_reacao" onclick="removeRequired()" >
                            <input type="reset"  class="btn botao-limpar" value="Limpar Campos">
                            <button type="button" onclick="window.location.href='<?php echo $voltar;?>'" class="btn botao-voltar">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </button>
                        </div>
                    </form>

                    <div style="height:230px; overflow:auto; margin-top:20px;">
                        <table class="table-striped" >
                            <tr class="cabecalho-tabela">
                                <th>Excluir</th>
                                <th>Num bolsa</th>
                                <th>Hora</th>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Sigla</th>
                                <th>Descricao</th>
                                <th>N° da notificação</th>
                                <th>Editar</th>
                            </tr>
                        
                            <!-- Dados dos pacientes -->
                            <?php
                                if (!$result_reg_reacao) {
                                    echo "<tr>
                                            <td colspan='9'>Erro ao gerar a query.</td> 
                                        </tr>";
                                }else{
                                    if(pg_num_rows($result_reg_reacao) > 0) {
                                        while ($row_reg_reacao = pg_fetch_assoc($result_reg_reacao)) {
                                            echo "<tr>
                                                    <td onclick='pega_id($row_reg_reacao[id_transfusionais])'>
                                                        <i class='fas fa-trash-alt' style='color: red;'></i>
                                                    </td>
                                                    <td style='text-align: left;'> $row_reg_reacao[num_bolsa] </td>
                                                    <td> $row_reg_reacao[hora] </td> 
                                                    <td>" . date('d/m/Y', strtotime($row_reg_reacao['data'])) . "</td> 
                                                    <td style='text-align: left;'> $row_reg_reacao[nome]      </td>
                                                    <td style='text-align: left;'> $row_reg_reacao[sigla]     </td>
                                                    <td style='text-align: left;'> $row_reg_reacao[descricao] </td> 
                                                    <td> $row_reg_reacao[num_notificacao] </td> 
                                                    <td><a href='reacao_transfusional.php?id_reacao=$row_reg_reacao[id_transfusionais]$pagina'><i class='fas fa-pencil-alt'></i></a></td>
                                                </tr>";
                                        }
                                    }else{
                                        echo "<tr>
                                                <td colspan='9'>Nenhum registo encontrado</td> 
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
    <!-- <a href='exclui.php?arquivo=reacao&id_reacao=$row_reg_reacao[id_transfusionais]$get_id_paciente'></a> -->

    <!-- Modal motivo exclusao -->
    <div class="modal" id="motivo_exclusao" role="dialog" tabindex="-1" aria-labelledby="motivo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; width: 100%;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title required" id="info_unificar">Digite o motivo da exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form action="exclui.php?arquivo=reacao<?php echo $get_id_paciente; ?>" method="POST" name="form_motivo" id="form_motivo" >
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="motivo"></label>
                                <textarea name="motivo" id="motivo" cols="30" rows="10" required></textarea>
                                <input type="hidden" name="id_reacao" id="id_reacao">
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Reação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Conteúdo da primeira ajuda -->
                    <p style="font-weight: bold;">Como adicionar uma reação?</p>
                    <ul>
                        <li>Data da Reação: Insira a data em que a reação ocorreu.</li>
                        <li>Hora da Reação: Informe o horário exato da reação.</li>
                        <li>N° da bolsa / Hemocomponente: escolha a bolsa que ocorreu a reação.</li>
                        <li>Observação: Adicione quaisquer observações relevantes sobre a reação.</li>
                        <li>N° da notificação: Adicione o número da notificação.</li>
                        <li>
                            Ao lado desses campos, você encontrará detalhes do paciente para quem a reação está sendo registrada, como o nome. 
                            Em seguida, selecione o tipo de reação (se é imediata ou tardia) e escolha a reação correspondente.
                        </li>
                        <li>Após inserir todas as informações necessárias, verifique se tudo está correto e confirme o registro clicando em "Adicionar". </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function pega_id(id_reacao){
            $('#motivo_exclusao').modal('show');
            document.getElementById("id_reacao").value = id_reacao
        }

        //mostra tipo 1 e zera select do tipo 2
        document.querySelector('#tipo_1').addEventListener('change', function() {
            if (this.checked) {
                document.querySelector('#imediata_reacoes').style.display = 'block';
                document.querySelector('#tardia_reacoes').style.display   = 'none';
                document.querySelector('#reacoes_tardias').selectedIndex  = 0;   
            }
        });

        //mostra tipo 2 e zera select do tipo 1
        document.querySelector('#tipo_2').addEventListener('change', function() {
            if (this.checked) {
                document.querySelector('#imediata_reacoes').style.display  = 'none';
                document.querySelector('#tardia_reacoes').style.display    = 'block';
                document.querySelector('#reacoes_imediatas').selectedIndex = 0;
            }
        });

        //remove required do select do tipo não selecionado quando selecionado algum valor
        function removeRequired() {
            if(document.getElementById("reacoes_imediatas").value  != ""){
                document.getElementById("reacoes_tardias").removeAttribute("required"); 
            }else if(document.getElementById("reacoes_tardias").value  != ""){
                document.getElementById("reacoes_imediatas").removeAttribute("required");
            }
        }

        //carrega os dados para aparecer na tela qundo for editar
        function EditarDados(){
            var selectDados = document.getElementById("hora");
            var nome_tipo = selectDados.dataset.nometipo;

            document.getElementById("tipo_1").removeAttribute('checked');
            document.getElementById("tipo_2").removeAttribute('checked');

            if(nome_tipo == "Reações Imediatas"){
                document.getElementById("tipo_1").setAttribute('checked',true);
                document.querySelector('#imediata_reacoes').style.display = 'block';
                document.querySelector('#tardia_reacoes').style.display   = 'none';
                document.querySelector('#reacoes_tardias').selectedIndex  = 0;  
                document.getElementById("reacoes_imediatas").value        = selectDados.dataset.idtipo;
            }else{
                document.getElementById("tipo_2").setAttribute('checked',true);
                document.querySelector('#imediata_reacoes').style.display  = 'none';
                document.querySelector('#tardia_reacoes').style.display    = 'block';
                document.querySelector('#reacoes_imediatas').selectedIndex = 0;
                document.getElementById("reacoes_tardias").value           = selectDados.dataset.idtipo;
            }

            document.getElementById("hora").value            = selectDados.dataset.hora;
            document.getElementById("data_reacao").value     = selectDados.dataset.dt;
            document.getElementById("num_bolsa").value       = selectDados.dataset.bolsa;
            document.getElementById("observacao").value      = selectDados.dataset.obs;
            document.getElementById("num_notificacao").value = selectDados.dataset.notificacao;
        }

        var btn_submit = document.getElementById("inserir_reacao").value.toLowerCase();

        // BOTÃO DE CONFIRMAÇÃO DE INSERÇÃO DE REAÇÃO TRANSFUSIONAL
        const form = document.getElementById('form_reacao');
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <script type="text/javascript" src="js/script.js"></script>

</body>
</html>