<?php
    include "database.php";
    include "function.php";
    ob_start();

    // Verifica se o formulário foi enviado e se existe a session
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['atualiza'])){

        //ATUALIZAR PACIENTE
        if ($_SESSION['atualiza'] == "atualizar_paciente" ){
            // Coleta os dados do formulário, usar o name
            //post do paciente
            $dataNascimento = $_POST["dt_nascimento"];
            $nome           = trim($_POST["nome_completo"]);
            $cpf            = $_POST["cpf"];
            $sexo           = $_POST["sexo"];
            $mae            = trim($_POST["mae"]);
            $abo            = $_POST["abo"];
            $rh             = $_POST["rh"];
            $setor          = $_POST["setor"];
            $leito          = $_POST["leito"];
            $internacao     = $_POST["hospital_internacao"];
            $num_sus        = $_POST["num_sus"];
            $observacao     = trim($_POST["observacao"]);
            $dataRequisicao = $_POST["data_requisicao"];
            $nome_social    = trim($_POST["nome_social"]);
            $rn             = $_POST["recem_nascido"];
            $prontuario     = $_POST["prontuario"];
            $registro       = $_POST["registro"];
            $id_bolsa       = $_POST["id_bolsa"];
            $numero_rt      = $_POST["numero_rt"];
            $diagnostico    = $_POST["diagnostico"];
                
            //chama a função para atualizar, colocar argumentos igual a assinatura da function
            atualizarPaciente($conexao, $dataNascimento, $nome, $cpf, $sexo, $mae, 
            $abo, $rh, $setor, $leito, $internacao, $num_sus, $prontuario, 
            $observacao, $dataRequisicao, $nome_social, $rn, $registro, 
            $numero_rt, $diagnostico);  
        }

        //---------------------------------------------------------------------------------------
        // ATUALIZAR BOLSA
        if ($_SESSION['atualiza'] == "atualizar_bolsa" ){
            // Coleta os dados do formulário, usar o name
            //post da bolsa
            $notvisa           = $_POST["notvisa"];
            $shtnovo           = $_POST["shtnovo"];
            $observacao_bolsa  = trim($_POST["observacao"]);
            $num_bolsa         = $_POST["numero_bolsa"];
            $num_sus_bolsa     = $_POST["num_sus_bolsa"];
            $id_hemocomponente = $_POST["hemocomponente"];
            $reserva           = $_POST["reserva"];
            $aliquota          = $_POST["aliquota"];
            $livro_setor       = $_POST["setor_livro"];
            $id_bolsa          = $_GET["id_bolsa"];
            $id_paciente       = $_GET["id_paciente"];
            $obito             = $_POST["obito"];

            //verifica o tamanho da variavel, se for > 0 há uma data e ela precisa estar dentro de aspas
            $dt_saida       = strlen($_POST['dt_saida']) > 0        ? "'$_POST[dt_saida]'"        : 'null';
            $dt_validade    = strlen($_POST['data_validade']) > 0   ? "'$_POST[data_validade]'"   : 'null';
            $dt_transfusao  = strlen($_POST['data_transfusao']) > 0 ? "'$_POST[data_transfusao]'" : 'null';
            $horario_inicio = strlen($_POST['horario_inicio']) > 0  ? "'$_POST[horario_inicio]'"  : 'null';

            atualizarBolsa($conexao, $notvisa, $shtnovo, $id_bolsa, $num_bolsa, 
            $num_sus_bolsa, $id_hemocomponente, $dt_transfusao, $dt_saida, 
            $dt_validade, $horario_inicio, $reserva, $aliquota, $livro_setor, 
            $observacao_bolsa, $id_paciente, $obito);
        }

        //---------------------------------------------------------------------------------------
        //ATUALIZAR CONTROLE
        if ($_SESSION['atualiza'] == "adicionar_controle") {
            // Coleta os dados do formulário, usar o name
            $dt_busca    = $_POST['data_busca'];
            $rt          = $_POST['rt'];
            $leito_t     = $_POST['leito_transferido'];
            $bolsa       = $_POST['num_bolsa'];
            $setor_t     = $_POST['setor_transferido'];
            $pai         = $_POST['pai'];
            $fit         = $_POST['fit'];
            $responsavel = $_POST["responsavel"];
            $setor       = $_POST["setor"];
            $leito       = $_POST["leito"];
            $observacao  = trim($_POST["observacao"]);

            $qtd_nao_conformidade = $_SESSION["qtd_nao_conformidade"];

            for($i=1; $i<=$qtd_nao_conformidade; $i++){
                if($_POST["nao_conformidade$i"] != 0){
                    $id_nao_conformidade[$i] = [(int)$_POST["nao_conformidade$i"]];
                }
            }
                    
            //chama a função para atualizar, colocar argumentos igual a assinatura da function
            atualizarControle($conexao, $dt_busca, $rt, $leito_t, $bolsa, $pai, $fit, 
            $setor_t, $observacao, $responsavel, $setor, $leito, $id_nao_conformidade);
        } 

        //---------------------------------------------------------------------------------------
        //ATUALIZA SETORES
        if ($_SESSION["atualiza"] == "atualiza_setor") {
            // variavel recebe valor do name do campo 
            $nome_setor = trim($_POST["setor_editar"]);
            $status     = $_POST["status_editar"];
            $id_setor   = $_POST["setores"];

            atualizarSetor($conexao, $nome_setor, $status, $id_setor); 
        }

        //---------------------------------------------------------------------------------------
        //ATUALIZA RESPONSÁVEL
        if ($_SESSION["atualiza"] == "atualiza_responsavel") {
            // variavel recebe valor do name do campo 
            $nome_responsavel = trim($_POST["responsavel_editar"]);
            $status           = $_POST["status_editar"];
            $id_responsavel   = $_POST["responsaveis"];

            atualizarResponsavel($conexao, $nome_responsavel, $status, $id_responsavel); 
        }

        //---------------------------------------------------------------------------------------
        //ATUALIZA REAÇÃO TRASFUSIONAL
        if ($_SESSION["atualiza"] == "atualiza_reacao") {
            // variavel recebe valor do name do campo 
            $tipo_reacao = $_POST["tipo_reacao"];
            $hora        = $_POST["hora_reacao"];
            $data        = $_POST["data_reacao"];
            $observacao  = trim($_POST["observacao"]);
            $id_bolsa    = $_POST["num_bolsa"];
            $notificacao = $_POST["num_notificacao"];
            $paciente    = $_SESSION['id_paciente_reacao'];

            if ($tipo_reacao == 1) { // Reação Imediata
                $reacaoId = $_POST['reacoes_imediatas'];
            } elseif ($tipo_reacao == 2) { // Reação Tardia
                $reacaoId = $_POST['reacoes_tardias'];
            }

            atualizaReacaoTransfusional($conexao, $reacaoId, $paciente, $hora, $data, 
            $observacao, $id_bolsa, $notificacao); 
        }

        //---------------------------------------------------------------------------------------
        //ATUALIZA BOLSAS DEVOLVIDAS
        if ($_SESSION["atualiza"] == "atualizar_bolsa_devolvida") {
            // variavel recebe valor do name do campo 

            $data_reg = $_POST['dt_registro'];
            $data_dev = $_POST['dt_devolucao'];
            $obs      = trim($_POST['obs']); 
            $motivo   = $_POST['motivo'];
            $bolsa    = $_POST['bolsa'];
            $paciente = $_SESSION['id_paciente'];

            atualizaBolsaDevolvida($conexao, $data_reg, $data_dev, $obs, $motivo, $bolsa, $paciente); 
        }

        //---------------------------------------------------------------------------------------
        //ATUALIZA NÃO CONFORMIDADE
        if ($_SESSION["atualiza"] == "atualizar_nao_conformidade") {
            // variavel recebe valor do name do campo 

            $nao_conformidade    = trim($_POST["nao_conformidade_editar"]);
            $tipo                = $_POST["tipo_editar"];
            $status              = $_POST["status"];
            $id_nao_conformidade = $_POST["nao_conformidades"];

            atualizaNaoConformidade($conexao, $nao_conformidade, $tipo, $status, $id_nao_conformidade); 
        }
    }