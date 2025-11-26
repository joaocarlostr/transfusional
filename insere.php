<?php
    include "database.php";
    include "function.php";
    ob_start();

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        //INSERE BOLSA
        if ($_SESSION["insere"] == "adicionar_bolsa") {
            // variavel recebe valor do name do campo  

            $numero_bolsa    = $_POST["numero_bolsa"];
            $hemocomponente  = $_POST["hemocomponente"];  
            $observacao      = trim($_POST["observacao"]);
            $reserva         = $_POST["reserva"];
            $num_sus_bolsa   = $_POST["num_sus_bolsa"];
            $aliquota        = $_POST["aliquota"]; 
            $notvisa         = $_POST["notvisa"];
            $shtnovo         = $_POST["shtnovo"];
            $livro_setor     = $_POST["setor_livro"];
            $obito           = $_POST["obito"];

            //verifica o tamanho da variavel, se for > 0 há uma data e ela precisa estar dentro de aspas
            $dt_saida        = strlen($_POST['dt_saida']) > 0        ? "'$_POST[dt_saida]'"        : 'null';
            $data_transfusao = strlen($_POST['data_transfusao']) > 0 ? "'$_POST[data_transfusao]'" : 'null';
            $horario_inicio  = strlen($_POST['horario_inicio']) > 0  ? "'$_POST[horario_inicio]'"  : 'null';
        
            //busca o id do paciente dependendo de que forma a pagina foi acionada
            $paciente = isset($_GET['id_paciente']) ? $_GET['id_paciente'] : (int) $_SESSION['id_paciente'];

            gravarBolsa($conexao, $numero_bolsa, $paciente, $hemocomponente, 
            $observacao, $reserva, $dt_saida, $num_sus_bolsa, $aliquota, 
            $notvisa, $shtnovo, $data_transfusao, $horario_inicio, 
            $livro_setor, $obito);
        }

        //---------------------------------------------------------------------------------------
        //INSERE PACIENTE
        if ($_SESSION["insere"] == "inserir_paciente") {
            // Coleta os dados do formulário, usar o name
            $dataNascimento  = $_POST["data_nascimento"];
            $nome            = trim($_POST["nome_completo"]);
            $cpf             = $_POST["cpf"];
            $sexo            = $_POST["sexo"];
            $mae             = trim($_POST["mae"]);
            $abo             = $_POST["abo"];
            $rh              = $_POST["rh"];
            $setor           = $_POST["setor"];
            $leito           = $_POST["leito"];
            $internacao      = $_POST["hospital_internacao"];
            $num_sus         = $_POST["num_sus"];
            $observacao      = trim($_POST["observacao"]);
            $data_requisicao = $_POST["data_requisicao"];
            $nome_social     = trim($_POST["nome_social"]);
            $rn              = $_POST["recem_nascido"];
            $prontuario      = $_POST["prontuario"];
            $registro        = $_POST["registro"];
            $numero_rt       = $_POST["numero_rt"];
            $diagnostico     = $_POST["diagnostico"];

            gravarPaciente($conexao, $dataNascimento, $nome, $cpf, 
            $sexo, $mae, $abo, $rh, $setor, $leito, $internacao,
            $num_sus, $prontuario, $observacao, $data_requisicao, 
            $nome_social, $rn, $registro, $numero_rt, $diagnostico);
                    
        }

        //---------------------------------------------------------------------------------------
        //INSERE BOLSAS DEVOLVIDAS
        if ($_SESSION["insere"] == "inserir_bolsa_devolvida") {
            // variavel recebe valor do name do campo
            $motivo       = $_POST["motivo"];
            $dt_devolucao = $_POST["dt_devolucao"];
            $dt_registro  = $_POST["dt_registro"];
            $obs          = trim($_POST["obs"]);
            $bolsa        = $_POST["bolsa"];

            if (isset($_SESSION['id_paciente'])){
                $paciente = (int) $_SESSION['id_paciente'];
            }

            gravarBolsaDevolvida($conexao, $motivo, $dt_devolucao, $dt_registro, $obs, $bolsa, $paciente);
        }

        //---------------------------------------------------------------------------------------
        //INSERE REAÇÕES TRANSFUSIONAIS
        if ($_SESSION["insere"] == "inserir_reacao") {
            // variavel recebe valor do name do campo 
            $tipo_reacao = $_POST["tipo_reacao"];
            $hora        = $_POST["hora_reacao"];
            $data        = $_POST["data_reacao"];
            $observacao  = trim($_POST["observacao"]);
            $id_bolsa    = $_POST["num_bolsa"];
            $notificacao = $_POST["num_notificacao"];

            if ($tipo_reacao == 1) { // Reação Imediata
                $reacaoId = $_POST['reacoes_imediatas'];
            } elseif ($tipo_reacao == 2) { // Reação Tardia
                $reacaoId = $_POST['reacoes_tardias'];
            }

            gravarReacaoTransfusional($conexao, $reacaoId, $hora, $data, $observacao, $id_bolsa, $notificacao); 
        }

        //---------------------------------------------------------------------------------------
        //INSERE SETORES
        if ($_SESSION["insere"] == "inserir_setor") {
            // variavel recebe valor do name do campo 
            $nome_setor = trim($_POST["setor"]);
            $status     = $_POST["status"];

            gravarSetor($conexao, $nome_setor, $status); 
        }

        //---------------------------------------------------------------------------------------
        //INSERE RESPONSÁVEIS
        if ($_SESSION["insere"] == "inserir_responsavel") {
            // variavel recebe valor do name do campo 
            $nome   = trim($_POST["responsavel"]);
            $status = $_POST["status"];

            gravarResponsavel($conexao, $nome, $status); 
        }

        //---------------------------------------------------------------------------------------
        //INSERE NÃO CONFORMIDADES
        if ($_SESSION["insere"] == "inserir_nao_conformidade") {
            // variavel recebe valor do name do campo 
            $nao_conformidade = trim($_POST["nao_conformidade"]);
            $tipo             = $_POST["tipo"];

            gravarNaoConformidade($conexao, $nao_conformidade, $tipo); 
        }
        
        //---------------------------------------------------------------------------------------
        //INSERE CADASTRO DO CONTROLE
        if ($_SESSION["insere"] == "adicionar_controle") {
            // variavel recebe valor do name do campo 
            $dt_busca             = $_POST["data_busca"]; 
            $rt                   = $_POST["rt"];
            $leito_transferido    = $_POST["leito_transferido"];
            $pai_positivo         = $_POST["pai"];
            $fit                  = $_POST["fit"];
            $id_paciente          = $_GET['id_paciente']; 
            $id_setor_transferido = $_POST["setor_transferido"]; 
            $id_bolsa             = $_POST["num_bolsa"];
            $responsavel          = $_POST["responsavel"];
            $setor                = $_POST["setor"];
            $leito                = $_POST["leito"];
            $observacao           = trim($_POST["observacao"]);

            $qtd_nao_conformidade = (int) $_SESSION["qtd_nao_conformidade"];

            for($i=1; $i<=$qtd_nao_conformidade; $i++){
                if($_POST["nao_conformidade$i"] != 0){
                    $id_nao_conformidade[$i] = [(int)$_POST["nao_conformidade$i"]];
                }
            }
            // var_dump($id_nao_conformidade);

            gravarControle($conexao, $id_nao_conformidade, 
            $dt_busca, $rt, $leito_transferido, $pai_positivo, 
            $fit, $id_paciente, $id_setor_transferido, 
            $id_bolsa, $observacao, $responsavel, $setor, $leito); 
        }
    }