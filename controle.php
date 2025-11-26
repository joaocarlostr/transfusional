<?php
    include "database.php";
    include "function.php";

    $dt_busca = $rt = $leito_t = $setor_t = $bolsa = $pai = $fit = $responsavel = $setor = $leito = $observacao = $funcao_load = null;
    $submit = $msg_nao_conformidade = "Adicionar";

    //manda id_paciente por session quando for atualizar pois o get vai ser id_controle
    if (isset($_GET['id_paciente_selecionado'])) {
        $id_paciente             = $_GET['id_paciente_selecionado'];
        $_SESSION['id_paciente'] = $id_paciente;
    }
    
    $paciente = isset($_SESSION['id_paciente']) ? (int) $_SESSION['id_paciente'] : (int) $_GET['id_paciente_selecionado'];
    $pagina   = isset($_GET["pagina"])          ? "&pagina=$_GET[pagina]"  : "";
    $voltar   = "cadastrar_bolsa.php?id_paciente=$paciente$pagina";

    // Consulta SQL para buscar os setores ativos no banco de dados
    $query_setor_ativo              = "SELECT * FROM sth_setores WHERE status='ativo' ORDER BY nome_setor DESC";
    $result_setor_ativo             = conecta_query($conexao, $query_setor_ativo);
    $result_setor_livro_ativo       = conecta_query($conexao, $query_setor_ativo);
    $result_setor_transferido_ativo = conecta_query($conexao, $query_setor_ativo);

    // Consulta SQL para buscar os setores inativos no banco de dados
    $query_setor_inativo              = "SELECT * FROM sth_setores WHERE status='' ORDER BY nome_setor DESC";
    $result_setor_inativo             = conecta_query($conexao, $query_setor_inativo);
    $result_setor_livro_inativo       = conecta_query($conexao, $query_setor_inativo);
    $result_setor_transferido_inativo = conecta_query($conexao, $query_setor_inativo);

    // Consulta SQL para buscar os dados da rt
    $query_rt  = "SELECT * FROM sth_dados_rt";
    $result_rt = conecta_query($conexao, $query_rt);

    // Consulta SQL para buscar os responsaveis ativos
    $query_responsavel_ativo  = "SELECT * FROM sth_responsavel where status = 'ativo' ORDER BY nome";
    $result_responsavel_ativo = conecta_query($conexao, $query_responsavel_ativo);

    // Consulta SQL para buscar os responsaveis inativos
    // $query_responsavel_inativo  = "SELECT * FROM sth_responsavel where status = '' ORDER BY nome";
    // $result_responsavel_inativo = conecta_query($conexao, $query_responsavel_inativo);

    //buscar nao conformidades
    $query_nao_conformidade        = "SELECT * FROM sth_nao_conformidade WHERE tipo = ";
    $result_nao_conformidade_ficha = conecta_query($conexao, "$query_nao_conformidade 'Ficha de controle de sinais vitais' ORDER BY nao_conformidade");
    $result_nao_conformidade_livro = conecta_query($conexao, "$query_nao_conformidade 'Livro de registro de hemocomponentes' ORDER BY nao_conformidade");
    $result_nao_conformidade_form  = conecta_query($conexao, "$query_nao_conformidade 'Formulário de devolução de hemocomponentes' ORDER BY nao_conformidade");
    $result_nao_conformidade_pm    = conecta_query($conexao, "$query_nao_conformidade 'Prescrição médica' ORDER BY nao_conformidade");
    $result_nao_conformidade_outro = conecta_query($conexao, "$query_nao_conformidade 'Outros' ORDER BY nao_conformidade");
    
    if(isset($_GET['id_paciente_selecionado'])){
        //consulta SQL para buscar dados da bolsa e nome dos hemocomponentes
        $query_bolsa  = "SELECT cb.id_bolsa, num_bolsa, dt_saida, num_sus, data_transfusao, horario_inicio, id_livro_setor, h.sigla, descricao 
        FROM sth_cadastro_bolsa cb
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
        left join sth_controle c on c.id_bolsa = cb.id_bolsa
        LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa
        WHERE cb.id_paciente = $paciente and bd.id_bolsa IS NULL and c.id_bolsa is null
        ORDER BY c.id_bolsa";
        $result_bolsa = conecta_query($conexao, $query_bolsa);

        //Consulta SQL para buscar todos os registros de controle associados ao paciente
        $query_reg_controle  = " SELECT c.id_controle, cb.horario_inicio, cb.data_transfusao, cb.num_bolsa, num_sus, h.sigla FROM sth_controle c
        INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = c.id_bolsa
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
        WHERE cb.id_paciente = $_GET[id_paciente_selecionado]
        ORDER BY cb.data_transfusao";
        $result_reg_controle = conecta_query($conexao, $query_reg_controle);

        $funcao_load = "campo_paciente_selecionado()";
        $action      = "insere.php?id_paciente=$_GET[id_paciente_selecionado]";
    }

    if(isset($_GET['id_controle'])){
        $submit = $msg_nao_conformidade = "Atualizar";
        $action = "atualiza.php?id_controle=$_GET[id_controle]"; 
        $voltar = "controle.php?id_paciente_selecionado=$paciente$pagina";

        //Consulta SQL para buscar bolsas registradas deste paciente
        $query_bolsa  = "SELECT cb.id_bolsa, num_bolsa, dt_saida, num_sus, data_transfusao, horario_inicio, id_livro_setor, h.sigla, descricao 
        FROM sth_cadastro_bolsa cb
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
        INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
        LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa
        WHERE cb.id_paciente = $paciente and bd.id_bolsa IS NULL
        ORDER BY c.id_bolsa";

        $result_bolsa = conecta_query($conexao, $query_bolsa);

        //seleciona registro especifico
        $query_registro  = "SELECT c.*, b.dt_saida, num_sus, data_transfusao, horario_inicio, id_livro_setor, id_paciente, h.sigla FROM sth_controle c
        INNER JOIN sth_cadastro_bolsa b ON b.id_bolsa = c.id_bolsa
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = b.id_hemocomponente
        WHERE id_controle = $_GET[id_controle]";

        $result_registro = conecta_query($conexao, $query_registro);
        $row_registro    = pg_fetch_assoc($result_registro);

        //seleciona todos os arquivos do paciente
        $query_reg_controle  = "SELECT c.id_controle, dt_busca_ativa, cb.num_bolsa, num_sus, h.sigla FROM sth_controle c
        INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = c.id_bolsa
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
        WHERE cb.id_paciente = $row_registro[id_paciente] and c.id_controle != $_GET[id_controle]";

        $result_reg_controle = conecta_query($conexao, $query_reg_controle);
        $funcao_load         = "registro_selecionado()";

        $dt_busca    = $row_registro['dt_busca_ativa'];
        $rt          = $row_registro['id_rt'];
        $responsavel = $row_registro['id_responsavel'];
        $leito_t     = $row_registro['leito_transferido'];
        $setor_t     = $row_registro['id_setor_transferido'];
        $bolsa       = $row_registro['id_bolsa'];
        $setor       = !empty($row_registro['id_setor']) ? $row_registro['id_setor'] : null;
        $leito       = !empty($row_registro['leito'])    ? $row_registro['leito']    : null;   
        $pai         = $row_registro['pai_positivo'];
        $fit         = $row_registro['fit'];
        $observacao  = $row_registro["observacao"];
    }

    $_SESSION['insere']   = "adicionar_controle";
    $_SESSION['atualiza'] = "adicionar_controle";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.1/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="imagex/png" href="img/gota_sangue.ico">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

    <!-- colocar a jquery sempre primeiro que o javascript-->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/sweetalert2.all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

    <title>Controle - HUM</title>
</head>

<body onload="<?php echo $funcao_load;?>" >
    <?php include 'includes/header.php';?>
    <!-- Corpo -->
        <div class="container-controle-geral" >
            <div class="fundo-imagem">
                <div id="corpo-controle">
                    <p class="mensagem-borda"><strong>Controle de hemocomponentes transfundidos HUM / Serviço Transfusional</strong></p>
                    <div id="formulario" class="container-controle">
                        <form id="form_controle" method="POST" action="<?php echo $action; ?>">

                            <?php
                                if(isset($_SESSION['validado_controle']) && $_SESSION['validado_controle'] == 0){
                                    exibir_mensagem_simples("Adicionado!", "Controle adicionado com sucesso.", "success");
                                }

                                $_SESSION['validado_controle'] = -1;
                                
                                if(isset($_SESSION['validado_controle_editar']) && $_SESSION['validado_controle_editar'] == 0){
                                    exibir_mensagem_simples("Atualizado!", "Controle atualizado com sucesso.", "success");
                                }

                                $_SESSION['validado_controle_editar'] = -1;
                            ?>

                            <div class="cabecalho-controle-bolsa" ><!-- fazer css proprio -->
                                <div class="row row-column" >
                                    <div class="col-sm-12 col-md-6 col-lg-3">
                                        <label for="dt_busca" class="required">Data de busca ativa:</label><br>   <!-- pega a data atual -->
                                        <?php 
                                            echo "<input type='date' size='20' name='data_busca' id='dt_busca' min='1920-01-01' 
                                            max='" . date('Y-m-d') . "' oninput='dateMaskH(this, event)' required
                                            data-busca       = '$dt_busca'    data-rt    = '$rt'    data-leitot     = '$leito_t' 
                                            data-setort      = '$setor_t'     data-pai   = '$pai'   data-fit        = '$fit' 
                                            data-setorc      = '$setor'       data-leito = '$leito' data-observacao = '$observacao' 
                                            data-responsavel = '$responsavel' data-bolsa = '$bolsa' >";                                    
                                        ?>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-4" >
                                        <label for="rt" class="required">RT - Modalidade:</label>
                                        <select name="rt" id="rt" required>
                                            <option value="">Selecione</option>
                                            <?php
                                                //gera options de todos os dados da rt
                                                while ($row_rt = pg_fetch_assoc($result_rt)) {
                                                    echo "<option value='$row_rt[id_rt]'> $row_rt[reduzido] - $row_rt[descricao_rt] </option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-3">
                                        <label for="responsavel" class="required">Responsável pelo Registro:</label><br>
                                        <select name="responsavel" id="responsavel" required>
                                            <option value="">Selecione</option>
                                            <optgroup label="Ativos" >
                                                <?php
                                                    //gera options de todos os dados da rt
                                                    while ($row_responsavel = pg_fetch_assoc($result_responsavel_ativo)) {
                                                        echo "<option value='$row_responsavel[id_responsavel]'> $row_responsavel[nome] </option>";
                                                    }
                                                ?>
                                            </optgroup>
                                            <!-- <optgroup label="Inativos" >
                                                <?php
                                                    //gera options de todos os dados da rt
                                                    // while ($row_responsavel = pg_fetch_assoc($result_responsavel_inativo)) {
                                                    //     echo '<option value="' . $row_responsavel['id_responsavel'] . '">' .
                                                    //             $row_responsavel['nome'] .
                                                    //         '</option>';
                                                    // }
                                                ?>
                                            </optgroup> -->
                                        </select>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-2" >
                                        <br>
                                        <button type="button" id="avancada-button" class="btn controle_registro" style="cursor:pointer;" 
                                        data-bs-target="#controle_registro" data-bs-toggle="modal"><i class="fas fa-file"></i> Consultar registros</button>
                                    </div>
                                </div>

                                <div class="modal fade" id="controle_registro" tabindex="-1" aria-labelledby="info" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px; width: 100%;">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="info">Consultar registros</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div style="max-height:500px; overflow:scroll;">
                                                    <table id="tabela_registros" class="display table-striped">
                                                        <tr>
                                                            <!-- <th>id controle</th> -->
                                                            <th>Data transfusão</th>
                                                            <th>Horário</th>
                                                            <th>Bolsa</th>
                                                            <th>N° SUS</th>
                                                            <th>Hemoc.</th>
                                                            <th>Editar</th>
                                                        </tr> 

                                                        <!-- Dados dos pacientes -->
                                                        <?php
                                                            if (!$result_reg_controle) {
                                                                echo "<tr> <td colspan='5'>Erro ao gerar a query.</td> </tr>";
                                                            } else {
                                                                if (pg_num_rows($result_reg_controle) > 0) {
                                                                    while ($row_reg_controle = pg_fetch_assoc($result_reg_controle)) {
                                                                        echo "<tr>
                                                                            <td>". date('d/m/Y', strtotime($row_reg_controle['data_transfusao'])) ."</td>
                                                                            <td style='text-align: left;'>".
                                                                                SubStr($row_reg_controle['horario_inicio'], 0, 5)."
                                                                            </td>
                                                                            <td style='text-align: left;'>$row_reg_controle[num_bolsa]</td>
                                                                            <td>$row_reg_controle[num_sus]</td>
                                                                            <td style='text-align: left;'>$row_reg_controle[sigla]</td>
                                                                            <td>
                                                                                <a href='controle.php?id_controle=$row_reg_controle[id_controle]$pagina'>
                                                                                <i class='fas fa-pencil-alt'></i></a>
                                                                            </td>
                                                                        </tr>";
                                                                    }
                                                                }else{
                                                                    echo "<tr> <td colspan='5'>Nenhum registro encontrado.</td> </tr>";
                                                                }                                                     
                                                            }
                                                        ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <fieldset class="cabecalho-controle-bolsa" >
                                <legend>Dados da bolsa</legend>

                                <div class="row row-column">
                                    <div class="col-sm-12 col-md-7 col-lg-auto" >
                                        <label for="num_bolsa" class="required">N° da bolsa / Hemocomponente:</label><br>
                                        <select name="num_bolsa" id="num_bolsa" class="select2" style="width: 347px;" onchange="campo_bolsa()" required>
                                            <option value="">Selecione</option>
                                            <?php    
                                                //tras as bolsas cadastradas
                                                while ($row_bolsa = pg_fetch_assoc($result_bolsa)) { 
                                                        echo "<option value = '$row_bolsa[id_bolsa]'      
                                                        data-saida      = '$row_bolsa[dt_saida]'        
                                                        data-transfusao = '$row_bolsa[data_transfusao]' 
                                                        data-horario    = '$row_bolsa[horario_inicio]'  
                                                        data-livro      = '$row_bolsa[id_livro_setor]'  
                                                        data-sus        = '$row_bolsa[num_sus]' >
                                                        $row_bolsa[num_bolsa] | $row_bolsa[sigla]</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="col-sm-12 col-md-5 col-lg-auto" >
                                        <label for="num_sus">Número do SUS:</label><br>
                                        <input type="text" name="num_sus" id="num_sus" maxlength="11" placeholder="00000000000" readOnly="readOnly">
                                        <!-- oninput="validaEFormataCns(this)" -->
                                    </div>
                                    <div class="col-sm-12 col-md-7 col-lg-auto" >
                                        <label for="data" class="required">Data de transfusão:</label><br>
                                        <input type="date" name="data" id="data" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" readOnly="readOnly" required>
                                    </div>
                                    <div class="col-sm-12 col-md-5 col-lg-auto" >
                                        <label for="horario_inicio">Horário início:</label><br>
                                        <input type="time" name="horario_inicio" id="horario_inicio" readOnly="readOnly">
                                    </div>
                                    <div class="col-sm-12 col-md-5 col-lg-auto" >
                                        <label for="data_saida">Saída do hemocentro:</label><br>
                                        <input type="date" name="dt_saida" id="data_saida" min="1920-01-01" max="<?php echo date('Y-m-d'); ?>" 
                                        oninput="dateMaskH(this, event)" readOnly="readOnly">
                                    </div>
                                    <div class="col-sm-12 col-md-7 col-lg-auto" >
                                        <label for="setor_livro" class="required">Livro setor:</label><br>
                                        <select name="setor_livro" id="setor_livro" style="width:350px;" required>
                                            <option value="">Selecione</option>
                                            <optgroup label="Ativos" >
                                                <?php
                                                    // gera options de todos os setores cadastrados
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
                                        <!-- <input type="text" size="9" name="livro_setor" id="livro_setor" > -->
                                    </div>
                                </div>
                            </fieldset>
                            
                            <fieldset class="cabecalho-controle-paciente" >
                                <legend>Dados do paciente</legend>
                                <div class="row row-column">
                                    <div class="col-sm-12 col-md-12 col-lg-6">
                                        <label for="pacientes">Paciente:</label><br>
                                        <?php // pega id por get
                                            $disabled = null; 
                                            
                                            //seleciona paciente especifico
                                            $query_paciente_selecionado = "SELECT dp.*, s.nome_setor FROM sth_dados_paciente dp
                                            INNER JOIN sth_setores s ON dp.id_setor = s.id_setor 
                                            WHERE dp.id_paciente = $paciente
                                            ORDER BY dp.id_paciente";

                                            $result_paciente_selecionado = conecta_query($conexao, $query_paciente_selecionado);
                                            $row_paciente_selecionado    = pg_fetch_assoc($result_paciente_selecionado);

                                            if(isset($_GET['id_controle']) || isset($_GET['id_paciente_selecionado'])){
                                                $nome_paciente = !empty($row_paciente_selecionado['nome_social']) ? $row_paciente_selecionado['nome_social'] : $row_paciente_selecionado['nome_completo'];

                                                echo "<input type='text' name='paciente' readOnly='readOnly' id='pacientes'
                                                data-dt         = '$row_paciente_selecionado[dt_nasc]' 
                                                data-setor      = '$row_paciente_selecionado[nome_setor]'
                                                data-leito      = '$row_paciente_selecionado[leito]'
                                                data-prontuario = '$row_paciente_selecionado[prontuario]'
                                                value = '$nome_paciente'>";

                                            }else{
                                                echo '<p style="color: red;"> Erro! Nenhum paciente foi selecionado </p>';
                                                $disabled = "disabled";
                                            }
                                        ?>
                                    </div>

                                    <div class="col-sm-12 col-md-6 col-lg-4">
                                        <label class="required" for="setor">Setor:</label><br>
                                        <select name="setor" id="setor" required>
                                            <option value="">Selecione</option>
                                            <optgroup label="Ativos" >
                                                <?php
                                                    // gera options de todos os setores cadastrados
                                                    while ($row_setor = pg_fetch_assoc($result_setor_ativo)) {
                                                        echo "<option value='$row_setor[id_setor]'> $row_setor[nome_setor] </option>"; 
                                                            // cuidado com espaços depois e antes das aspas
                                                    }
                                                ?>
                                            </optgroup>
                                            <?php
                                                // gera options de todos os setores ativos cadastrados
                                                if(pg_num_rows($result_setor_inativo) > 0){
                                                    echo "<optgroup label='Inativos'>";

                                                    while ($row_setor = pg_fetch_assoc($result_setor_inativo)) {
                                                        echo "<option value='$row_setor[id_setor]'> $row_setor[nome_setor] </option>"; 
                                                            // cuidado com espaços depois e antes das aspas
                                                    }

                                                    echo "</optgroup>";
                                                }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-sm-12 col-md-6 col-lg-2">
                                        <label for="leito">Leito:</label><br>
                                        <input type="text" size="10" name="leito" id="leito" maxlength="30">
                                    </div>
                                </div>
                                
                                <div class="row row-column">
                                    <div class="col-sm-12 col-md-6 col-lg-2">
                                        <label for="dt_nascimento">Data de Nascimento:</label><br>
                                        <input type="date" size="20" name="data_nascimento" id="dt_nascimento" max="<?php echo date('Y-m-d'); ?>" readOnly="readOnly">
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-4">
                                        <label for="prontuario" >Prontuário: </label><br>
                                        <input type="text" size="15" name="prontuario" id="prontuario" readOnly="readOnly">
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-4">
                                        <label for="setor_transferido">Transferido para: </label><br>
                                        <select name="setor_transferido" id="setor_transferido">
                                            <option value="">Selecione</option>
                                            <optgroup label="Ativos" >
                                                <?php
                                                    // gera options de todos os setores cadastrados
                                                    while ($row_setor_transferido = pg_fetch_assoc($result_setor_transferido_ativo)) {
                                                        echo "<option value='$row_setor_transferido[id_setor]'> $row_setor_transferido[nome_setor] </option>"; 
                                                        // cuidado com espaços depois e antes das aspas
                                                    }
                                                ?>
                                            </optgroup>
                                            <?php
                                                // gera options de todos os setores ativos cadastrados
                                                if(pg_num_rows($result_setor_transferido_inativo) > 0){
                                                    echo "<optgroup label='Inativos'>";

                                                    while ($row_setor_transferido_inativo = pg_fetch_assoc($result_setor_transferido_inativo)) {
                                                        echo "<option value='$row_setor_transferido_inativo[id_setor]'> $row_setor_transferido_inativo[nome_setor] </option>"; 
                                                        // cuidado com espaços depois e antes das aspas
                                                    }

                                                    echo "</optgroup>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-12 col-md-6 col-lg-2">
                                        <label for="leito_transferido" >Leito: </label><br>
                                        <input type="text" size="10" name="leito_transferido" id="leito_transferido">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="cabecalho-controle-checagem" >
                                <div class="row" >
                                    <div class="col-sm-12 col-md-12 col-lg-6" >
                                        <label for="observacao" >Observação:</label><br>
                                        <textarea name="observacao" id="observacao" cols="10" rows="5" maxlength="255"></textarea>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-6">
                                        <br>
                                        <input type='checkbox' name='fit' id='fit_sim' value='sim'>
                                        <label for="fit_sim" style="color:red;" >FIT</label><br>
                                        <input type='checkbox' name='pai' id='pai_sim' value='sim'>
                                        <label for="pai_sim" style="color:red;" >PAI positivo  </label><br><br>

                                        <button type="button" class="btn controle_registro" data-bs-toggle="modal" data-bs-target="#naoConformidadeModal">
                                            <?php echo  "$msg_nao_conformidade Não Conformidade"; ?>
                                        </button>
                                    </div>
                                </div>

                                <div class="modal fade" id="naoConformidadeModal" tabindex="-1" aria-labelledby="naoConformidadeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 1500px; width: 100%;">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="naoConformidadeModalLabel">Não Conformidades</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div style="height:500px; overflow:scroll; overflow-x: hidden;">
                                                    <div class="row" >
                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <div style="max-height:200px; overflow:auto; margin-right:60px;">
                                                                <table class="table-striped" >
                                                                    <tr class="cabecalho-tabela">
                                                                        <th style="background-color:#cacfd2;">Prescrição médica</th>
                                                                    </tr>
                                                                    
                                                                    <?php
                                                                        $cont = 0;

                                                                        //gera options de todos os dados da rt
                                                                        if (!$result_nao_conformidade_pm) {
                                                                            echo "<tr>
                                                                                    <td>Erro ao gerar a query</td> 
                                                                                </tr>";
                                                                        }else{
                                                                            if(pg_num_rows($result_nao_conformidade_pm) > 0) {
                                                                                while ($row_nao_conformidade_pm = pg_fetch_assoc($result_nao_conformidade_pm)) {

                                                                                    $cont++;
                                                                                    $reg_nao_conformidade = null;

                                                                                    if(isset($_GET['id_controle'])){
                                                                                        $result_registro_nao_conformidade = conecta_query($conexao, "SELECT * FROM sth_controle_nao_conformidade WHERE id_controle = $row_registro[id_controle]");
                                                                                        
                                                                                        while ($row = pg_fetch_assoc($result_registro_nao_conformidade)) {
                                                                                            if($row_nao_conformidade_pm["id_nao_conformidade"] == $row["id_nao_conformidade"]){
                                                                                                $reg_nao_conformidade = 'checked';
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    echo "<tr>
                                                                                            <td style='text-align: left;'><input type='checkbox' name='nao_conformidade$cont' value='$row_nao_conformidade_pm[id_nao_conformidade]' id='nao_conformidade$cont' $reg_nao_conformidade> 
                                                                                            $row_nao_conformidade_pm[nao_conformidade]</td> 
                                                                                        </tr>";
                                                                                }
                                                                            }else{
                                                                                echo "<tr>
                                                                                        <td colspan='1'>Nenhum registo encontrado</td> 
                                                                                    </tr>";
                                                                            }
                                                                        }
                                                                    ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row" >
                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <div style="max-height:200px; overflow:auto; margin-right:60px;">
                                                                <table class="table-striped" >
                                                                    <tr class="cabecalho-tabela">
                                                                        <th style="background-color:#cacfd2;">Ficha de controle de sinais vitais</th>
                                                                    </tr>
                                                                    <?php
                                                                        //gera options de todos os dados da rt
                                                                        if (!$result_nao_conformidade_ficha) {
                                                                            echo "<tr>
                                                                                    <td>Erro ao gerar a query</td> 
                                                                                </tr>";
                                                                        }else{
                                                                            if(pg_num_rows($result_nao_conformidade_ficha) > 0) {
                                                                                while ($row_nao_conformidade_ficha = pg_fetch_assoc($result_nao_conformidade_ficha)) {
                                                                                    
                                                                                    $cont++;
                                                                                    $reg_nao_conformidade = null;
                                                                                    
                                                                                    if(isset($_GET['id_controle'])){
                                                                                        $result_registro_nao_conformidade = conecta_query($conexao, "SELECT * FROM sth_controle_nao_conformidade WHERE id_controle = $row_registro[id_controle]");
                                                                                        
                                                                                        while ($row = pg_fetch_assoc($result_registro_nao_conformidade)) {
                                                                                            if($row_nao_conformidade_ficha["id_nao_conformidade"] == $row["id_nao_conformidade"]){
                                                                                                $reg_nao_conformidade = 'checked';
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    echo "<tr>
                                                                                            <td style='text-align: left;'><input type='checkbox' name='nao_conformidade$cont' value='$row_nao_conformidade_ficha[id_nao_conformidade]' id='nao_conformidade$cont' $reg_nao_conformidade> 
                                                                                            $row_nao_conformidade_ficha[nao_conformidade]</td> 
                                                                                        </tr>";
                                                                                }
                                                                            }else{
                                                                                echo "<tr>
                                                                                        <td colspan='1'>Nenhum registo encontrado</td> 
                                                                                    </tr>";
                                                                            }
                                                                        }
                                                                    ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row" >
                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <div style="max-height:200px; overflow:auto; margin-right:60px;">
                                                                <table class="table-striped" >
                                                                    <tr class="cabecalho-tabela">
                                                                        <th style="background-color:#cacfd2;">Livro de registro de hemocomponentes</th>
                                                                    </tr>
                                                                    <?php
                                                                        //gera options de todos os dados da rt
                                                                        if (!$result_nao_conformidade_livro) {
                                                                            echo "<tr>
                                                                                    <td>Erro ao gerar a query</td> 
                                                                                </tr>";
                                                                        }else{
                                                                            if(pg_num_rows($result_nao_conformidade_livro) > 0) {
                                                                                while ($row_nao_conformidade_livro = pg_fetch_assoc($result_nao_conformidade_livro)) {
                                                                                    
                                                                                    $cont++;
                                                                                    $reg_nao_conformidade = null;
                                                                                    
                                                                                    if(isset($_GET['id_controle'])){
                                                                                        $result_registro_nao_conformidade = conecta_query($conexao, "SELECT * FROM sth_controle_nao_conformidade WHERE id_controle = $row_registro[id_controle]");
                                                                                        
                                                                                        while ($row = pg_fetch_assoc($result_registro_nao_conformidade)) {
                                                                                            if($row_nao_conformidade_livro["id_nao_conformidade"] == $row["id_nao_conformidade"]){
                                                                                                $reg_nao_conformidade = 'checked';
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    echo "<tr>
                                                                                            <td style='text-align: left;'><input type='checkbox' name='nao_conformidade$cont' value='$row_nao_conformidade_livro[id_nao_conformidade]' id='nao_conformidade$cont' $reg_nao_conformidade>
                                                                                            $row_nao_conformidade_livro[nao_conformidade]</td> 
                                                                                        </tr>";
                                                                                }
                                                                            }else{
                                                                                echo "<tr>
                                                                                        <td colspan='1'>Nenhum registo encontrado</td> 
                                                                                    </tr>";
                                                                            }
                                                                        }
                                                                    ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row" >
                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <div style="max-height:200px; overflow:auto; margin-right:60px;">
                                                                <table class="table-striped" >
                                                                    <tr class="cabecalho-tabela">
                                                                        <th style="background-color:#cacfd2;">Formulário de devolução de hemocomponentes</th>
                                                                    </tr>
                                                                    <?php
                                                                        //gera options de todos os dados da rt
                                                                        if (!$result_nao_conformidade_form) {
                                                                            echo "<tr>
                                                                                    <td>Erro ao gerar a query</td> 
                                                                                </tr>";
                                                                        }else{
                                                                            if(pg_num_rows($result_nao_conformidade_form) > 0) {
                                                                                while ($row_nao_conformidade_form = pg_fetch_assoc($result_nao_conformidade_form)) {
                                                                                    
                                                                                    $cont++;
                                                                                    $reg_nao_conformidade = null;
                                                                                    
                                                                                    if(isset($_GET['id_controle'])){
                                                                                        $result_registro_nao_conformidade = conecta_query($conexao, "SELECT * FROM sth_controle_nao_conformidade WHERE id_controle = $row_registro[id_controle]");
                                                                                        
                                                                                        while ($row = pg_fetch_assoc($result_registro_nao_conformidade)) {
                                                                                            if($row_nao_conformidade_form["id_nao_conformidade"] == $row["id_nao_conformidade"]){
                                                                                                $reg_nao_conformidade = 'checked';
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    echo "<tr>
                                                                                            <td style='text-align: left;'><input type='checkbox' name='nao_conformidade$cont' value='$row_nao_conformidade_form[id_nao_conformidade]' id='nao_conformidade$cont' $reg_nao_conformidade>
                                                                                            $row_nao_conformidade_form[nao_conformidade]</td> 
                                                                                        </tr>";
                                                                                }
                                                                            }else{
                                                                                echo "<tr>
                                                                                        <td colspan='1'>Nenhum registo encontrado</td> 
                                                                                    </tr>";
                                                                            }
                                                                        }
                                                                    ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row" >
                                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                                            <div style="max-height:200px; overflow:auto; margin-right:60px;">
                                                                <table class="table-striped" >
                                                                    <tr class="cabecalho-tabela">
                                                                        <th style="background-color:#cacfd2;">Outros</th>
                                                                    </tr>
                                                                    <?php
                                                                        //gera options de todos os dados da rt
                                                                        if (!$result_nao_conformidade_form) {
                                                                            echo "<tr>
                                                                                    <td>Erro ao gerar a query</td> 
                                                                                </tr>";
                                                                        }else{
                                                                            if(pg_num_rows($result_nao_conformidade_form) > 0) {
                                                                                while ($row_nao_conformidade_outro = pg_fetch_assoc($result_nao_conformidade_outro)) {
                                                                                    
                                                                                    $cont++;
                                                                                    $reg_nao_conformidade = null;
                                                                                    
                                                                                    if(isset($_GET['id_controle'])){
                                                                                        $result_registro_nao_conformidade = conecta_query($conexao, "SELECT * FROM sth_controle_nao_conformidade WHERE id_controle = $row_registro[id_controle]");
                                                                                        
                                                                                        while ($row = pg_fetch_assoc($result_registro_nao_conformidade)) {
                                                                                            if($row_nao_conformidade_outro["id_nao_conformidade"] == $row["id_nao_conformidade"]){
                                                                                                $reg_nao_conformidade = 'checked';
                                                                                            }
                                                                                        }
                                                                                    }

                                                                                    echo "<tr>
                                                                                            <td style='text-align: left;'><input type='checkbox' name='nao_conformidade$cont' value='$row_nao_conformidade_outro[id_nao_conformidade]' id='nao_conformidade$cont' $reg_nao_conformidade> 
                                                                                            $row_nao_conformidade_outro[nao_conformidade]</td> 
                                                                                        </tr>";
                                                                                }
                                                                            }else{
                                                                                echo "<tr>
                                                                                        <td colspan='1'>Nenhum registo encontrado</td> 
                                                                                    </tr>";
                                                                            }
                                                                        }
                                                                        $_SESSION["qtd_nao_conformidade"] = $cont;
                                                                    ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer botoes-controle">
                                                <button type="button" class="botao-verde" style="width:150px;" data-bs-dismiss="modal">Ok</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="botoes-controle col-sm-12 col-md-12 col-lg-12">
                                <input type="submit" value="<?php echo $submit;?>" class="btn botao-verde" name="<?php echo $submit . "_controle";?>" <?php echo $disabled;?> id="controle_submit" >
                                <input type="reset"  value="Limpar Campos" class="btn botao-limpar" onclick="window.location.reload()" >
                                <button type="button" onclick="window.location.href='<?php echo $voltar;?>'" class="btn botao-voltar">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <!-- </div> -->
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
                    <h5 class="modal-title" id="helpModalLabel">Ajuda - Controle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p style="font-weight: bold;" >Como cadastrar um controle?</p>
                    <ul>
                        <li>Insira os dados pedidos na tela e clique no botão Adicionar;</li>
                        <li>Não se esqueça de preencher todos os campos obrigatórios.</li>
                    </ul>
                    <p style="font-weight: bold;" >Como consultar os registros de controle?</p>
                    <ul>
                        <li>Clique no botão Consultar registros na parte superior à direita da tela;</li>
                        <li>Escolha qual registro você pretende editar e clique no lápis na coluna "Editar";</li>
                        <li>Insira os dados que deseja alterar e clique no botão atualizar.</li>
                    </ul>
                    <p style="font-weight: bold;" >Não consigo alterar os dados do paciente.</p>
                    <ul>
                        <li>Os dados a seguir não podem ser alterados aqui pois foram pré-cadastrados;</li>
                        <li>Data de nascimento, prontuário;</li>
                        <li>Vá ao perfil para fazer alterações.</li>
                    </ul>
                    <p style="font-weight: bold;" >Não consigo alterar os dados da bolsa.</p>
                    <ul>
                        <li>Os dados a seguir não podem ser alterados aqui pois foram pré-cadastrados;</li>
                        <li>Data de saída do hemocentro, número do sus, data de transfusão, horário início e livro setor;</li>
                        <li>Vá ao botão "+ Bolsa de hemocomponente" no perfil para fazer alterações.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        //faz select2 funcionar
        $(document).ready(function() {
            $(".select2").select2();
        });

        //reseta select2
        function limpa_select2(){
            $('.select2').val(null).trigger('change');
        }

        //insere campo automatico dos dados do paciente
        function campo_paciente_selecionado() {
            var selectPaciente = document.getElementById('pacientes');

            var dt         = selectPaciente.dataset.dt;
            var leito      = selectPaciente.dataset.leito;
            var setor      = selectPaciente.dataset.setor;
            var prontuario = selectPaciente.dataset.prontuario;

                // alert(dt);
            // if(!selectPaciente){
            if(leito == undefined){
                leito = null;
            }

            //adiciona valor nos input
            document.getElementById('dt_nascimento').value = dt;
            document.getElementById('leito').value         = leito;
            document.getElementById("prontuario").value    = prontuario;

            //mantem select selecionado
            var select = document.querySelector('#setor');

            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].text === setor) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }

        document.getElementById('setor_livro').addEventListener('mousedown', function(event) {
            event.preventDefault(); // Previne a interação do usuário
        });

        //select de bolsas, insere campos relacionados a bolsa
        function campo_bolsa(){
            var selectBolsa   = document.getElementById('num_bolsa');
            var selectedBolsa = selectBolsa.options[selectBolsa.selectedIndex];

            var dt_saida      = selectedBolsa.dataset.saida;
            var num_sus       = selectedBolsa.dataset.sus;
            var dt_transfusao = selectedBolsa.dataset.transfusao;
            var horario       = selectedBolsa.dataset.horario;
            var livro_setor   = selectedBolsa.dataset.livro;

            var selectSetorLivro = document.querySelector('#setor_livro');

            for (var i = 0; i < selectSetorLivro.options.length; i++) {
                if (selectSetorLivro.options[i].value === livro_setor) {
                    selectSetorLivro.selectedIndex = i;
                    break;
                }
            }

            if(selectBolsa.selectedIndex != 0){
                document.getElementById('data_saida').value     = dt_saida;
                document.getElementById('num_sus').value        = num_sus;
                document.getElementById('data').value           = dt_transfusao;
                document.getElementById("horario_inicio").value = horario;
            }else{
                document.getElementById('data_saida').value     = '';
                document.getElementById('num_sus').value        = '';
                document.getElementById('data').value           = '';
                document.getElementById("horario_inicio").value = '';
            }

            //desabilita todo select mas ele não pode ser enviado por post/get, solução no final
            // $("#setor_livro").prop('disabled',true);
        }

        //tras as informações do registro selecionado
        function registro_selecionado(){
            var selectControle = document.getElementById("dt_busca");

            var dt_busca    = selectControle.dataset.busca;
            var rt          = selectControle.dataset.rt;
            var responsavel = selectControle.dataset.responsavel;
            var leito_t     = selectControle.dataset.leitot;
            var setor_t     = selectControle.dataset.setort;
            var observacao  = selectControle.dataset.observacao;
            var bolsa       = selectControle.dataset.bolsa;
            var pai         = selectControle.dataset.pai;
            var fit         = selectControle.dataset.fit;
            var setorc      = selectControle.dataset.setorc;
            var leito       = selectControle.dataset.leito;

            var selectRt = document.querySelector('#rt'); 

            for (var i = 0; i < selectRt.options.length; i++) {
                if (selectRt.options[i].value === rt) {
                    selectRt.selectedIndex = i;
                    break;
                }
            }

            var selectResponsavel = document.querySelector('#responsavel'); 

            for (var i = 0; i < selectResponsavel.options.length; i++) {
                if (selectResponsavel.options[i].value === responsavel) {
                    selectResponsavel.selectedIndex = i;
                    break;
                }
            }

            document.getElementById("dt_busca").value          = dt_busca;
            document.getElementById("leito_transferido").value = leito_t;
            campo_paciente_selecionado();

            if(setor && leito){

                var selectSetorControle = document.querySelector('#setor'); 

                for (var i = 0; i < selectSetorControle.options.length; i++) {
                    if (selectSetorControle.options[i].value === setorc) {
                        selectSetorControle.selectedIndex = i;
                        break;
                    }
                }

                document.getElementById("leito").value = leito;
            }

            // console.log(setorc, document.querySelector('#setor').value, leito, document.getElementById("leito").value);

            var selectSetorT = document.querySelector('#setor_transferido'); 

            for (var i = 0; i < selectSetorT.options.length; i++) {
                if (selectSetorT.options[i].value === setor_t) {
                    selectSetorT.selectedIndex = i;
                    break;
                }
            }

            var selectBolsa = document.querySelector('#num_bolsa');

            for (var i = 0; i < selectBolsa.options.length; i++) {
                if (selectBolsa.options[i].value === bolsa) {
                    selectBolsa.selectedIndex = i;
                    break;
                }
            }

            var num_bolsa = selectBolsa.options[selectBolsa.selectedIndex].value;
            $('.select2').val(num_bolsa);
            $('.select2').trigger('change'); 

            document.getElementById("pai_sim").removeAttribute('checked');
            document.getElementById("fit_sim").removeAttribute('checked');

            if(pai == "sim"){
                document.getElementById("pai_sim").setAttribute('checked',true);
            }

            if(fit == "sim"){
                document.getElementById("fit_sim").setAttribute('checked',true);
            }

            document.getElementById("observacao").innerHTML = observacao;
        }

        //habilita o select setor para que possa mandar para o banco com post
        // $('#form_controle').on('submit', function() {
        //     // $('#setor').prop('disabled', false);
        //     $('#setor_livro').prop('disabled', false);
        // });

        var btn_controle = document.getElementById("controle_submit").value;
        var operacao = '';

        if(btn_controle == "Adicionar"){
            btn_controle = "adicionar";
        }else{
            btn_controle = "atualizar";
        }

        function selecionaTudoSim(){
            var seleciona_sim = document.getElementById("seleciona_sim");
            
            if(seleciona_sim.checked){

                document.getElementById("ssvv_sim").setAttribute('checked',true);
                document.getElementById("dupla_checagem_sim").setAttribute('checked',true);
                document.getElementById("pm_sim").setAttribute('checked',true);
                document.getElementById("checagem_pm_sim").setAttribute('checked',true);
                document.getElementById("livro_ass_sim").setAttribute('checked',true);
                document.getElementById("livro_coren_sim").setAttribute('checked',true);
                document.getElementById("livro_etiqueta_sim").setAttribute('checked',true);
                document.getElementById("livro_validade_sim").setAttribute('checked',true);
                document.getElementById("pai_sim").setAttribute('checked',true);
                document.getElementById("fit_sim").setAttribute('checked',true);
                // console.log("passou sim", seleciona_sim);
            }else{
                document.getElementById("ssvv_sim").removeAttribute('checked');
                document.getElementById("dupla_checagem_sim").removeAttribute('checked');
                document.getElementById("pm_sim").removeAttribute('checked');
                document.getElementById("checagem_pm_sim").removeAttribute('checked');
                document.getElementById("livro_ass_sim").removeAttribute('checked');
                document.getElementById("livro_coren_sim").removeAttribute('checked');
                document.getElementById("livro_etiqueta_sim").removeAttribute('checked');
                document.getElementById("livro_validade_sim").removeAttribute('checked');
                document.getElementById("pai_sim").removeAttribute('checked');
                document.getElementById("fit_sim").removeAttribute('checked');
                // console.log("passou nao", seleciona_sim);
            }
            // console.log(document.getElementById("seleciona_sim").checked);
        }

        // BOTÃO DE CONFIRMAÇÃO DE INSERÇÃO DE REAÇÃO TRANSFUSIONAL
        const form = document.getElementById('form_controle');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            Swal.fire({
                title: "Deseja " + btn_controle + "?",
                text: "Você irá " + btn_controle + " um novo registro no controle!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, " + btn_controle,
                cancelButtonText: "Cancelar"
                }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

    </script>

    <!-- script select2 busca de paciente -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script type="text/javascript" src="js/script.js"></script>
    
</body>
</html>