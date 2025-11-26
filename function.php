<?php
    include "database.php";
    session_start();

    // IMPORTANTE: não armazene o objeto/recursos de conexão com o banco na sessão.
    // Objetos internos como PgSql\Connection não podem ser serializados pelo PHP
    // (gera: "Serialization of 'PgSql\\Connection' is not allowed").
    // Se precisar manter informações de conexão entre requests, armazene apenas
    // parâmetros simples (host, user, dbname) ou reconecte em cada request.

    //---------------------------------------------------------------------------------------
    //conecta query com banco e valida o resultado
    function conecta_query($conexao, $query){
        $resultado = pg_query($conexao, $query);

        if (!$resultado) {
            die("Erro na execução da consulta: " . pg_last_error($conexao));
        }

        return $resultado;
    }

    //---------------------------------------------------------------------------------------
    //Funcao para retornar o numero de linhas afetadas | usar com INSERT/UPDATE/DELETE
    function busca_linhas_afetadas($conexao, $query) {
        $resultado = conecta_query($conexao, $query);
        
        if (!$resultado) {
            die('Erro na consulta de linhas afetadas: ' . pg_last_error($conexao));
        }
        
        return pg_affected_rows($resultado);
    }

    //---------------------------------------------------------------------------------------
    //Gera lista de ids para um select com in ()
    function gerarListaIds($conexao, $consulta, $coluna_id) {
        $result_query = conecta_query($conexao, $consulta);
        $ids          = [];

        while ($row = pg_fetch_assoc($result_query)) {
            $ids[] = (int) $row[$coluna_id];
        }
        
        return $ids;
    }

    //---------------------------------------------------------------------------------------
    //verifica se a query é verdadeira, redireciona e fecha o banco
    function redireciona($conexao, $query, $location){

        if(!$query){
            echo "erro ao inserir";
        }else{
            header("Location:$location");
        }    
                
        pg_close($conexao);
    }

    //---------------------------------------------------------------------------------------
    // Funcao para nomear os meses
    function nomeia_mes($numero_mes){
        $numero_mes = ltrim($numero_mes, '0');
        $nome_mes = [
            1  => 'JANEIRO', 
            2  => 'FEVEREIRO', 
            3  => 'MARÇO', 
            4  => 'ABRIL', 
            5  => 'MAIO', 
            6  => 'JUNHO',
            7  => 'JULHO', 
            8  => 'AGOSTO', 
            9  => 'SETEMBRO', 
            10 => 'OUTUBRO', 
            11 => 'NOVEMBRO', 
            12 => 'DEZEMBRO'
        ];

        return $nome_mes[$numero_mes];
    }

    //---------------------------------------------------------------------------------------
    //nomeia mes com sigla
    function nomeia_mes_sigla($numero_mes){
        $numero_mes = ltrim($numero_mes, '0');
        $nome_mes = [
            1  => 'JAN', 
            2  => 'FEV', 
            3  => 'MAR', 
            4  => 'ABR', 
            5  => 'MAI', 
            6  => 'JUN',
            7  => 'JUL', 
            8  => 'AGO', 
            9  => 'SET', 
            10 => 'OUT', 
            11 => 'NOV', 
            12 => 'DEZ'
        ];

        return $nome_mes[$numero_mes];
    }

    //---------------------------------------------------------------------------------------
    // gera alert com mensagem
    function exibir_mensagem_simples($titulo, $mensagem, $tipo) {
        echo "<script>
                Swal.fire({
                    title: '$titulo',
                    text: '$mensagem',
                    icon: '$tipo',
                    showConfirmButton: true
                });
            </script>";
    }

    //-----------------------------------------------------------------------------------------
    // gera sigla do hemocomponente
    function sigla_hemocomponente($hemocomponente_descricao){
        $hemocomponente_sigla = [
            'E002 - CONCENTRADO DE HEMACIAS-'                                  => 'CH',
            'E002 - CONCENTRADO DE HEMACIAS-IRRADIADO / LEUCOREDUZIDO'         => 'CHFI',
            'E002 - CONCENTRADO DE HEMACIAS-IRRADIADO'                         => 'CHI',
            'E002 - CONCENTRADO DE HEMACIAS-LEUCOREDUZIDO'                     => 'CHF',
            'E003 - CONCENTRADO DE HEMACIAS-LAVADOLEUCOREDUZIDO'               => 'CHLF',
            'E010 - PLASMA FRESCO CONGELADO-'                                  => 'PFC', 
            'E011 - PLASMA FRESCO-DESCONGELADO'                                => 'PFD',
            'E020 - CONCENTRADO DE PLAQUETAS RANDOMICO-'                       => 'CPR',
            'E022 - POOL DE CONCENTRADO DE PLAQUETAS RANDOMICAS-LEUCOREDUZIDO' => 'POOL_CP',
            'E024 - CONCENTRADO DE PLAQUETAS AFERESE-'                         => 'CP_AFÉRESE', 
            'E024 - CONCENTRADO DE PLAQUETAS AFERESE-LEUCOREDUZIDO'            => 'CP_AFÉRESE', 
            'E028 - CRIOPRECIPITADO-'                                          => 'CRIO' 
        ];

        return $hemocomponente_sigla[$hemocomponente_descricao];
    }

    //desvincular bolsa devolvida | ainda em teste | utilizando outro método
    // function desvincula_bolsa($conexao, $motivo, $id_bolsa){
    //     $query = conecta_query($conexao, "UPDATE cadastro_bolsa SET id_paciente = null
    //     WHERE id_bolsa = $id_bolsa");    

    //     redireciona($conexao, $query, "bolsas_devolvidas.php");
    // }

    // ------------------------------------------------------------------------------------------------------------
    //                                                   INSERT 
    // ------------------------------------------------------------------------------------------------------------

    // GRAVAR PACIENTE
    function gravarPaciente($conexao, $dataNascimento, $nome, $cpf, $sexo, $mae, $abo, $rh, $setor, $leito, $internacao,
        $num_sus, $prontuario, $observacao, $data_requisicao, $nome_social, $rn, $registro, $numero_rt, $diagnostico) {        

        //conta se há paciente com esse cpf
        $busca_cpf  = conecta_query($conexao, "SELECT cpf FROM sth_dados_paciente WHERE cpf = '$cpf'");
        $valida_cpf = pg_num_rows($busca_cpf);

        //conta se ja existe paciente com esse prontuario
        $busca_prontuario  = conecta_query($conexao, "SELECT prontuario FROM sth_dados_paciente WHERE prontuario = '$prontuario'");
        $valida_prontuario = pg_num_rows($busca_prontuario);

        //conta se ja existe paciente com esse registro
        $busca_registro  = conecta_query($conexao, "SELECT registro FROM sth_dados_paciente WHERE registro = '$registro'");
        $valida_registro = pg_num_rows($busca_registro);

        //ignoramos cpf 0 pois pode haver varios
        if($cpf == '000.000.000-00'){
            $valida_cpf = 0;
        }else if($prontuario == '0'){
            $valida_prontuario = 0;
        }

        //um dos campos é obrigatório
        if($cpf == '000.000.000-00' && $prontuario == '0'){
            $valida_cpf = 1;
        }

        if($registro == $prontuario || $registro == $cpf){
            $valida_registro = 1;
        }

        //conta quantas linhas tem com esses cpf/prontuario. obs: não deve haver linhas
        $valida = $valida_cpf + $valida_prontuario + $valida_registro;

        // echo $valida . " cpf " . $valida_cpf . " pron " . $valida_prontuario;

        if($valida == 0){
            $query = conecta_query($conexao, "INSERT INTO sth_dados_paciente(
                dt_nasc, nome_completo, 
                cpf, 
                sexo, 
                nome_mae, 
                abo, 
                rh_d, 
                id_setor,
                leito, 
                hospital, 
                numero_sus, 
                prontuario, 
                observacao, 
                dt_requisicao, 
                nome_social, 
                rn,
                registro,
                numero_rt,
                diagnostico
                ) VALUES (
                '$dataNascimento', 
                '$nome', 
                '$cpf', 
                '$sexo', 
                '$mae', 
                '$abo', 
                '$rh', 
                 $setor, 
                '$leito', 
                '$internacao',
                '$num_sus', 
                '$prontuario', 
                '$observacao', 
                '$data_requisicao', 
                '$nome_social', 
                '$rn',
                '$registro',
                '$numero_rt',
                '$diagnostico')"); 

            $query_id  = conecta_query($conexao, "SELECT id_paciente FROM sth_dados_paciente 
            WHERE cpf = '$cpf' and prontuario = '$prontuario' and registro = '$registro'");
            $result_id = pg_fetch_assoc($query_id);

            $_SESSION['validado_paciente'] = 0;
            redireciona($conexao, $busca_prontuario, "perfil_paciente.php?id_paciente=$result_id[id_paciente]");

        }else{
            $_SESSION['validado_paciente'] = $cpf == '000.000.000-00' && $prontuario == '0' ? 2 : 1;
            if ($valida_registro > 0) $_SESSION['validado_paciente'] = 3;
            if($registro == $prontuario || $registro == $cpf) $_SESSION['validado_paciente'] = 4;
            redireciona($conexao, $busca_prontuario, "cadastro_paciente.php");
        }
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR BOLSA
    function gravarBolsa($conexao, $numero_bolsa, $paciente, $hemocomponente, $observacao, $reserva, 
    $dt_saida, $num_sus_bolsa, $aliquota, $notvisa, $shtnovo, $data_transfusao, $horario_inicio, $livro_setor, $obito){

        if(empty($livro_setor)){
            $livro_setor = 'null';
        }

        $query_sus = conecta_query($conexao, "SELECT num_bolsa, num_sus
        FROM sth_cadastro_bolsa
        WHERE num_bolsa != '$numero_bolsa' AND num_sus = '$num_sus_bolsa' AND id_hemocomponente not in (20, 8)");

        if(pg_num_rows($query_sus) > 0){
            $_SESSION['validado_bolsa'] = 3; // num sus ja cadastrado em outra bolsa
            $query = true;

        }else{
            $query_busca = "SELECT num_bolsa, iniciais, dt_validade, id_hemocomponente, num_sus, aliquota
            FROM sth_cadastro_bolsa 
            WHERE num_bolsa = '$numero_bolsa' AND num_sus = '$num_sus_bolsa' AND id_hemocomponente = $hemocomponente";

            $busca = conecta_query($conexao, $query_busca);

            if(pg_num_rows($busca) == 0){
                $query = conecta_query($conexao, "INSERT INTO sth_cadastro_bolsa(
                    num_bolsa, 
                    id_paciente, 
                    id_hemocomponente, 
                    observacao, 
                    reserva, 
                    dt_saida, 
                    num_sus, 
                    aliquota, 
                    notvisa, 
                    shtnovo, 
                    data_transfusao, 
                    horario_inicio, 
                    id_livro_setor,
                    obito
                    ) VALUES (
                    '$numero_bolsa', 
                    '$paciente', 
                    '$hemocomponente', 
                    '$observacao', 
                    '$reserva', 
                     $dt_saida,
                    '$num_sus_bolsa', 
                    '$aliquota', 
                    '$notvisa', 
                    '$shtnovo', 
                     $data_transfusao, 
                     $horario_inicio, 
                     $livro_setor,
                    '$obito')");
        
                $_SESSION['validado_bolsa'] = 0; //cadastrada com sucessso

            }else{
                $query_busca .= " AND aliquota = 'sim'";
                $busca        = conecta_query($conexao, $query_busca);
                $query        = $busca;

                $_SESSION['validado_bolsa'] = 2; // adicionado como nao aliquota antes

                if(pg_num_rows($busca) > 0){
                    $result_busca = pg_fetch_assoc($busca);
                    $query        = conecta_query($conexao, "INSERT INTO sth_cadastro_bolsa(
                        num_bolsa, 
                        id_paciente, 
                        id_hemocomponente, 
                        observacao, 
                        reserva, 
                        dt_saida, 
                        num_sus, 
                        aliquota, 
                        notvisa, 
                        shtnovo, 
                        data_transfusao, 
                        horario_inicio, 
                        id_livro_setor
                        ) VALUES (
                        '$result_busca[num_bolsa]', 
                        '$paciente', 
                        '$result_busca[id_hemocomponente]', 
                        '$observacao', 
                        '$reserva', 
                        $dt_saida,
                        '$result_busca[num_sus]', 
                        'sim', 
                        '$notvisa', 
                        '$shtnovo', 
                        $data_transfusao, 
                        $horario_inicio, 
                        $livro_setor)");

                    $_SESSION['validado_bolsa'] = 1; // bolsa adicionada como aliquota
                }
            }
        }
        redireciona($conexao, $query, "cadastrar_bolsa.php?id_paciente=$paciente");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR BOLSA DEVOLVIDA
    function gravarBolsaDevolvida($conexao, $motivo, $dt_devolucao, $dt_registro, $obs, $bolsa, $paciente){
        $query = conecta_query($conexao, "INSERT INTO sth_bolsas_devolvidas(
            motivo, 
            dt_devolucao, 
            dt_registro, 
            observacao, 
            id_bolsa
            ) VALUES (
            '$motivo',
            '$dt_devolucao',
            '$dt_registro',
            '$obs',
            '$bolsa')");

        //melhorar desvinculamento
        // desvincula_bolsa($conexao, $motivo, $bolsa);

        $_SESSION['validado_bolsa_devolvida'] = 0;
        redireciona($conexao, $query, "bolsas_devolvidas.php?id_paciente=$paciente");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR REAÇÃO TRANSFUSIONAL
    function gravarReacaoTransfusional($conexao, $reacaoId, $hora, $data, $observacao, $id_bolsa, $notificacao){

        if($id_bolsa == null){
            $id_bolsa = 'null';
        }

        $query = conecta_query($conexao, "INSERT INTO sth_reacoes_transfusionais(
            tipo_reacao, 
            hora, 
            data, 
            observacao, 
            num_notificacao, 
            id_bolsa
            ) VALUES(
            '$reacaoId', 
            '$hora', 
            '$data', 
            '$observacao', 
            '$notificacao', 
             $id_bolsa)");

        $_SESSION['validado_reacao'] = 0;
        redireciona($conexao, $query, "reacao_transfusional.php?id_paciente=$_GET[id_paciente]");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR SETOR
    function gravarSetor($conexao, $nome_setor, $status){

        $query = conecta_query($conexao, "INSERT INTO sth_setores(nome_setor, status)
        VALUES('$nome_setor', '$status')");

        $_SESSION['validado_setor'] = 0;
        redireciona($conexao, $query, "cadastrar_setor.php");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR RESPONSAVEL
    function gravarResponsavel($conexao, $nome, $status){

        $query = conecta_query($conexao, "INSERT INTO sth_responsavel(nome, status)
        VALUES('$nome', '$status')");

        $_SESSION['validado_responsavel'] = 0;
        redireciona($conexao, $query, "cadastrar_responsavel.php");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR NÃO CONFORMIDADE
    function gravarNaoConformidade($conexao, $nao_conformidade, $tipo){

        $query = conecta_query($conexao, "INSERT INTO sth_nao_conformidade(nao_conformidade, tipo, status)
        VALUES('$nao_conformidade', '$tipo', 'ativo')");

        $_SESSION['validado_nao_conformidade'] = 0;
        redireciona($conexao, $query, "cadastrar_nao_conformidade.php");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR CONTROLE
    function gravarControle($conexao, $id_nao_conformidade, $dt_busca, $rt, $leito_transferido, $pai_positivo, 
        $fit, $id_paciente, $id_setor_transferido, $id_bolsa, $observacao, $responsavel, $setor, $leito){

        if($id_setor_transferido == null){
            $id_setor_transferido = 'null';
        }

        $query = conecta_query($conexao, "INSERT INTO sth_controle(
            dt_busca_ativa, 
            id_rt, 
            leito_transferido, 
            pai_positivo, 
            fit, 
            id_setor_transferido, 
            id_bolsa, id_responsavel, 
            id_setor, 
            leito, 
            observacao
            ) VALUES (
            '$dt_busca', 
            '$rt', 
            '$leito_transferido', 
            '$pai_positivo', 
            '$fit', 
             $id_setor_transferido, 
            '$id_bolsa', 
            '$responsavel', 
             $setor, 
            '$leito', 
            '$observacao')");

        $result_id_controle = conecta_query($conexao, "SELECT id_controle FROM sth_controle WHERE id_bolsa = $id_bolsa");
        $row_id_controle    = pg_fetch_assoc($result_id_controle);

        foreach($id_nao_conformidade as $value){
            conecta_query($conexao, "INSERT INTO sth_controle_nao_conformidade(id_controle, id_nao_conformidade) VALUES ($row_id_controle[id_controle], $value[0])");
        }

        $_SESSION['validado_controle'] =  0;
        redireciona($conexao, $query, "controle.php?id_paciente_selecionado=$id_paciente");
    }

    //---------------------------------------------------------------------------------------
    //GRAVAR EXCLUSÃO
    function gravarExclusao($conexao, $motivo, $tipo, $identificador, $identificador_aux, $dt_registro, $prontuario, $campo_extra){
        date_default_timezone_set('America/Sao_Paulo');

        $data              = date('d/m/Y H:i:s');
        $id_usuario_logado = isset($_SESSION["id"]) ? (int) $_SESSION["id"] : 0;

        conecta_query($conexao, "INSERT INTO sth_exclusoes(
            motivo, 
            tipo_registro, 
            dt_exclusao, 
            id_usuario, 
            identificador, 
            identificador_aux, 
            dt_registro, 
            prontuario, 
            campo_extra
            ) VALUES (
            '$motivo', 
            '$tipo', 
            '$data', 
             $id_usuario_logado, 
            '$identificador', 
            '$identificador_aux', 
             $dt_registro, 
            '$prontuario', 
            '$campo_extra')");
    }

    // ----------------------------------------------------------------------------------------------------------
    //                                                   UPDATE 
    // ----------------------------------------------------------------------------------------------------------

    //ATUALIZAR PACIENTE
    function atualizarPaciente($conexao, $dataNascimento, $nome, $cpf, $sexo, $mae, $abo, $rh, $setor, $leito, $internacao, $num_sus,
        $prontuario, $observacao, $dataRequisicao, $nome_social, $rn, $registro, $numero_rt, $diagnostico){  

        $id_paciente = isset($_SESSION['id_paciente_atualiza']) ? (int) $_SESSION['id_paciente_atualiza'] : 0;
        
        //conta se há paciente com esse cpf
        $busca_cpf  = conecta_query($conexao, "SELECT cpf FROM sth_dados_paciente WHERE cpf = '$cpf' and id_paciente != $id_paciente");
        $valida_cpf = pg_num_rows($busca_cpf);

        //conta se ja existe paciente com esse prontuario
        $busca_prontuario  = conecta_query($conexao, "SELECT prontuario FROM sth_dados_paciente WHERE prontuario = '$prontuario' and id_paciente != $id_paciente");
        $valida_prontuario = pg_num_rows($busca_prontuario);

        //conta se ja existe paciente com esse registro
        $busca_registro  = conecta_query($conexao, "SELECT registro FROM sth_dados_paciente WHERE registro = '$registro' and id_paciente != $id_paciente");
        $valida_registro = pg_num_rows($busca_registro);

        // echo " cpf " . $valida_cpf . " pron " . $valida_prontuario . "p: $prontuario ________________";
        //ignoramos cpf 0 pois pode haver varios

        if($cpf == '000.000.000-00'){
            $valida_cpf = 0;
        }else if($prontuario == '0'){
            $valida_prontuario = 0;
        } 

        if($cpf == '000.000.000-00' && $prontuario == '0'){
            $valida_cpf = 1;
        }

        if($registro == $prontuario || $registro == $cpf){
            $valida_registro = 1;
        }

        //conta quantas linhas tem com esses cpf/prontuario. obs: não deve haver linhas
        $valida = $valida_cpf + $valida_prontuario + $valida_registro;

        if($valida == 0){
            //não esqueça de usar sempre o where no update
            //campos paciente
            $query = conecta_query($conexao, "UPDATE sth_dados_paciente SET 
            dt_nasc       = '$dataNascimento', 
            nome_completo = '$nome', 
            cpf           = '$cpf', 
            sexo          = '$sexo', 
            nome_mae      = '$mae', 
            abo           = '$abo', 
            rh_d          = '$rh', 
            id_setor      = '$setor',
            leito         = '$leito', 
            hospital      = '$internacao', 
            numero_sus    = '$num_sus', 
            prontuario    = '$prontuario', 
            observacao    = '$observacao', 
            dt_requisicao = '$dataRequisicao', 
            nome_social   = '$nome_social',
            rn            = '$rn',
            registro      = '$registro',
            numero_rt     = '$numero_rt',
            diagnostico   = '$diagnostico'
            WHERE id_paciente = $id_paciente");    

            $_SESSION['paciente_atualizado'] = 0;
        }else{
            $_SESSION['paciente_atualizado'] = $cpf == '000.000.000-00' && $prontuario == '0' ? 2 : 1;
            if ($valida_registro > 0) $_SESSION['paciente_atualizado'] = 3;
            if($registro == $prontuario || $registro == $cpf) $_SESSION['paciente_atualizado'] = 4;
            $query = true;
        } 

        redireciona($conexao, $query, "perfil_paciente.php?id_paciente=$id_paciente");
    }

     // ----------------------------------------------------------------------------------------------------------
    //ATUALIZAR BOLSA
    function atualizarBolsa($conexao, $notvisa, $shtnovo, $id_bolsa, $num_bolsa, $num_sus_bolsa, $id_hemocomponente, 
    $dt_transfusao, $dt_saida, $dt_validade, $horario_inicio, $reserva, $aliquota, $livro_setor, $observacao_bolsa, $id_paciente, $obito){  

        if(empty($livro_setor)){
            $livro_setor = 'null';
        }

        $query_sus = conecta_query($conexao, "SELECT num_bolsa, num_sus
        FROM sth_cadastro_bolsa
        WHERE num_bolsa != '$num_bolsa' AND num_sus = '$num_sus_bolsa' AND id_hemocomponente not in (20, 8) AND id_bolsa != $id_bolsa");

        if(pg_num_rows($query_sus) > 0){
            $_SESSION['bolsa_atualizada'] = 3; // num sus ja cadastrado em outra bolsa
            $query = true;

        }else{
            $query_busca = "SELECT num_bolsa, iniciais, dt_validade, id_hemocomponente, num_sus, aliquota
            FROM sth_cadastro_bolsa 
            WHERE num_bolsa = '$num_bolsa' AND num_sus = '$num_sus_bolsa' AND id_hemocomponente = $id_hemocomponente AND id_bolsa != $id_bolsa";

            $busca = conecta_query($conexao, $query_busca);

            if(pg_num_rows($busca) == 0){
                $query = conecta_query($conexao, "UPDATE sth_cadastro_bolsa SET 
                    notvisa           = '$notvisa', 
                    shtnovo           = '$shtnovo',
                    num_bolsa         = '$num_bolsa', 
                    dt_validade       =  $dt_validade, 
                    id_hemocomponente = '$id_hemocomponente',
                    observacao        = '$observacao_bolsa', 
                    reserva           = '$reserva', 
                    dt_saida          =  $dt_saida, 
                    num_sus           = '$num_sus_bolsa', 
                    data_transfusao   =  $dt_transfusao, 
                    horario_inicio    =  $horario_inicio, 
                    id_livro_setor    =  $livro_setor,
                    aliquota          = '$aliquota',
                    obito             = '$obito'
                    WHERE id_bolsa = $id_bolsa");

                    $_SESSION['bolsa_atualizada'] = 0;

            }else{
                $query_busca .= " AND aliquota = 'sim'";
                $busca        = conecta_query($conexao, $query_busca);
                $query        = $busca;

                $_SESSION['bolsa_atualizada'] = 2; // adicionado como nao aliquota antes

                if(pg_num_rows($busca) > 0){
                    $result_busca = pg_fetch_assoc($busca);
                    $query = conecta_query($conexao, "UPDATE sth_cadastro_bolsa SET 
                    notvisa           = '$notvisa', 
                    shtnovo           = '$shtnovo',
                    num_bolsa         = '$num_bolsa', 
                    dt_validade       =  $dt_validade, 
                    id_hemocomponente = '$id_hemocomponente',
                    observacao        = '$observacao_bolsa', 
                    reserva           = '$reserva', 
                    dt_saida          =  $dt_saida, 
                    num_sus           = '$num_sus_bolsa', 
                    data_transfusao   =  $dt_transfusao, 
                    horario_inicio    =  $horario_inicio, 
                    id_livro_setor    =  $livro_setor,
                    aliquota          = '$aliquota'
                    WHERE id_bolsa = $id_bolsa");

                    $_SESSION['bolsa_atualizada'] = 1; // bolsa adicionada como aliquota
                }
            }
        }
        
        redireciona($conexao, $query, "cadastrar_bolsa.php?id_paciente=$id_paciente");
    }

    //---------------------------------------------------------------------------------------
    //ATUALIZAR CONTROLE
    function atualizarControle($conexao, $dt_busca, $rt, $leito_t, $bolsa, 
    $pai, $fit, $setor_t, $observacao, $responsavel, $setor, $leito, $id_nao_conformidade){

        //seleciona paciente do controle para voltar a página de cadastro do controle
        $query_paciente  = "SELECT cb.id_paciente FROM sth_controle c
        INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = c.id_bolsa
        WHERE c.id_controle = $_GET[id_controle]";
        $result_paciente = conecta_query($conexao, $query_paciente);
        $row             = pg_fetch_array($result_paciente);
        
        if(empty($setor_t)){
            $setor_t = 'null';
        }

        //não esqueça de usar sempre o where no update
        $query = conecta_query($conexao, "UPDATE sth_controle SET 
            dt_busca_ativa       = '$dt_busca',
            id_rt                = '$rt',
            leito_transferido    = '$leito_t',
            id_bolsa             = '$bolsa',
            observacao           = '$observacao',
            pai_positivo         = '$pai',
            fit                  = '$fit',
            id_setor_transferido =  $setor_t,
            id_responsavel       = '$responsavel',
            id_setor             = '$setor',
            leito                = '$leito'
        WHERE id_controle = $_GET[id_controle]");    

        //exclui todas as não conformidades deste controle
        conecta_query($conexao, "DELETE FROM sth_controle_nao_conformidade WHERE id_controle = $_GET[id_controle]");

        foreach($id_nao_conformidade as $value){
            // echo $value[0]. " ";
            conecta_query($conexao, "INSERT INTO sth_controle_nao_conformidade(id_controle, id_nao_conformidade) VALUES ($_GET[id_controle], $value[0])");
        }
            
        $_SESSION['validado_controle_editar'] =  0;
        redireciona($conexao, $query, "controle.php?id_paciente_selecionado=$row[id_paciente]");
    }

    //---------------------------------------------------------------------------------------
    //ATUALIZAR SETORES
    function atualizarSetor($conexao, $nome_setor, $status, $id_setor) {
        $query = conecta_query($conexao, "UPDATE sth_setores SET 
            nome_setor = '$nome_setor',
            status     = '$status'
            WHERE id_setor = $id_setor");

        $_SESSION['validado_setor_editar'] = 0;
        redireciona($conexao, $query, "cadastrar_setor.php");
    }

    //---------------------------------------------------------------------------------------
    //ATUALIZAR REASPONSAVEIS
    function atualizarResponsavel($conexao, $nome_responsavel, $status, $id_responsavel){
        $query = conecta_query($conexao, "UPDATE sth_responsavel SET 
            nome   = '$nome_responsavel',
            status = '$status'
            WHERE id_responsavel = $id_responsavel");

        $_SESSION['validado_responsavel_editar'] = 0;
        redireciona($conexao, $query, "cadastrar_responsavel.php");
    }

    //---------------------------------------------------------------------------------------
    //ATUALIZAR NÃO CONFORMIDADE
    function atualizaNaoConformidade($conexao, $nao_conformidade, $tipo, $status, $id_nao_conformidade){
        $query = conecta_query($conexao, "UPDATE sth_nao_conformidade SET 
        nao_conformidade = '$nao_conformidade',
        tipo             = '$tipo',
        status           = '$status'
        WHERE id_nao_conformidade = $id_nao_conformidade");

        $_SESSION['validado_nao_conformidade_editar'] = 0;
        redireciona($conexao, $query, "cadastrar_nao_conformidade.php");
    }

    //---------------------------------------------------------------------------------------
    //ATUALIZAR REACOES TRANSFUSIONAIS
    function atualizaReacaoTransfusional($conexao, $reacaoId, $paciente, $hora, $data, $observacao, $id_bolsa, $notificacao){
        $query = conecta_query($conexao, "UPDATE sth_reacoes_transfusionais SET 
        tipo_reacao     =  $reacaoId,
        data            = '$data',
        hora            = '$hora',
        observacao      = '$observacao',
        id_bolsa        =  $id_bolsa,
        num_notificacao = '$notificacao'
        WHERE id_transfusionais = $_GET[id_reacao]");

        $_SESSION['validado_reacao_editar'] = 0;
        redireciona($conexao, $query, "reacao_transfusional.php?id_paciente=$paciente");
    }

    //---------------------------------------------------------------------------------------
    //ATUALIZAR BOLSA DEVOLVIDA
    function atualizaBolsaDevolvida($conexao, $data_reg, $data_dev, $obs, $motivo, $bolsa, $paciente){
        $query = conecta_query($conexao, "UPDATE sth_bolsas_devolvidas SET 
        dt_registro  = '$data_reg',
        dt_devolucao = '$data_dev',
        observacao   = '$obs',
        motivo       = '$motivo',
        id_bolsa     =  $bolsa
        WHERE id_bolsas_devolvidas = $_GET[id_bolsa_devolvida]");

        $_SESSION['validado_bolsa_devolvida_editar'] = 0;
        redireciona($conexao, $query, "bolsas_devolvidas.php?id_paciente=$paciente");
    }

    // ----------------------------------------------------------------------------------------------------------
    //                                                   DELETE 
    // ----------------------------------------------------------------------------------------------------------

    // EXCLUIR DADOS PACIENTE NA UNIFICAÇÃO
    function excluirPaciente($conexao, $id_paciente, $qtd_bolsas_selecionado, $id_bolsas, $qtd_bolsas, $motivo) {

        // EXCLUIR PACIENTE
        function deletePaciente($conexao, $id_paciente, $motivo){
            $row             = pg_fetch_assoc(conecta_query($conexao, "SELECT nome_completo, cpf, prontuario, registro FROM sth_dados_paciente WHERE id_paciente = $id_paciente"));
            $linhas_afetadas = busca_linhas_afetadas($conexao, "DELETE FROM sth_dados_paciente WHERE id_paciente = $id_paciente");

            if($linhas_afetadas > 0){
                gravarExclusao($conexao, $motivo, "Paciente", $row["cpf"], $row["nome_completo"], "null", $row["prontuario"], "");
            }
        }
    
        if ($qtd_bolsas == 0) {
            deletePaciente($conexao, $id_paciente, $motivo);  
        } else {
            if (empty($id_bolsas)) {
                $id_bolsas = implode(", ", gerarListaIds($conexao, "SELECT id_bolsa FROM sth_cadastro_bolsa WHERE id_paciente = $id_paciente", "id_bolsa"));
            }
    
            $id_controle = implode(", ", gerarListaIds($conexao, "SELECT id_controle FROM sth_controle WHERE id_bolsa IN ($id_bolsas)", "id_controle"));
            $id_reacoes  = implode(", ", gerarListaIds($conexao, "SELECT id_transfusionais FROM sth_reacoes_transfusionais WHERE id_bolsa IN ($id_bolsas)", "id_transfusionais"));

            $queries = ["DELETE FROM sth_controle_nao_conformidade WHERE id_controle IN ($id_controle)" => "controle_nao_conformidade"];

            foreach ($queries as $consulta => $tipo) {
                if(($tipo == "controle_nao_conformidade" && !empty($id_controle))){
                    conecta_query($conexao, $consulta);
                }
            }

            if(!empty($id_reacoes)){
                excluiReacaoTransfusional($conexao, $id_reacoes, null, $motivo);
            }

            excluiBolsaDevolvida($conexao, $id_bolsas, $id_paciente, "unificar", $motivo);
            excluiBolsaTransfundida($conexao, $id_bolsas, $id_paciente, "unificar", $motivo);
            deletePaciente($conexao, $id_paciente, $motivo);
        }
    }    

    //---------------------------------------------------------------------------------------
    //EXCLUIR REAÇÃO TRANSFUSIONAL
    function excluiReacaoTransfusional($conexao, $reacao_id, $location, $motivo){
        $query_registro = conecta_query($conexao, "SELECT cb.num_bolsa, rt.num_notificacao, dp.prontuario 
        FROM sth_reacoes_transfusionais rt
        INNER JOIN sth_cadastro_bolsa cb on cb.id_bolsa = rt.id_bolsa
        INNER JOIN sth_dados_paciente dp on dp.id_paciente = cb.id_paciente
        WHERE rt.id_transfusionais IN ($reacao_id)");

        while($result_registro = pg_fetch_assoc($query_registro)){
            gravarExclusao($conexao, $motivo, "Reação", $result_registro["num_notificacao"], $result_registro["num_bolsa"], 'null', $result_registro["prontuario"], "");
        }

        conecta_query($conexao, "DELETE FROM sth_reacoes_transfusionais WHERE id_transfusionais IN ($reacao_id)");
        $_SESSION['validado_reacao_excluir'] = $location == "reacao" ? 0 : -1;
    }

    //---------------------------------------------------------------------------------------
    //EXCLUIR BOLSA TRANSFUNDIDA / NÃO DEVOLVIDA
    function excluiBolsaTransfundida($conexao, $id_bolsa, $id_paciente, $location, $motivo){
        $query_registro = conecta_query($conexao, "SELECT cb.num_bolsa, cb.num_sus, cb.id_bolsa, h.sigla, dp.prontuario 
        FROM sth_cadastro_bolsa cb
        INNER JOIN sth_hemocomponentes h on h.id_hemocomponente = cb.id_hemocomponente
        INNER JOIN sth_dados_paciente dp on dp.id_paciente = cb.id_paciente
        WHERE cb.id_bolsa IN ($id_bolsa) and cb.id_paciente = $id_paciente");

        if($location != "bolsa_devolvida"){
            while($result_registro = pg_fetch_assoc($query_registro)){

                if($location == "bolsa"){
                    $_SESSION['validado_bolsa_excluir'] = 0; // Excluida com sucesso
        
                    $query_id_reacao   = conecta_query($conexao, "SELECT id_transfusionais FROM sth_reacoes_transfusionais where id_bolsa = $result_registro[id_bolsa]");
                    if(pg_num_rows($query_id_reacao) > 0){
                        $_SESSION['validado_bolsa_excluir'] = 2; // Bolsa possui reacoes vinculadas
                        redireciona($conexao, true, "cadastrar_bolsa.php?id_paciente=$id_paciente");
                        exit;
                    }
                }
                
                $query_id_controle = conecta_query($conexao, "SELECT id_controle FROM sth_controle where id_bolsa = $result_registro[id_bolsa]");
    
                if(pg_num_rows($query_id_controle) > 0){
                    conecta_query($conexao, "DELETE FROM sth_controle WHERE id_bolsa = $result_registro[id_bolsa]");
                }
    
                gravarExclusao($conexao, $motivo, "Bolsa", $result_registro["num_bolsa"], $result_registro["num_sus"], 'null', $result_registro["prontuario"], $result_registro["sigla"]);
            }
        }
        // echo "Bolsa: $id_bolsa";
        conecta_query($conexao, "DELETE FROM sth_cadastro_bolsa WHERE id_bolsa IN ($id_bolsa) and id_paciente = $id_paciente");
        // redireciona($conexao, $query, "cadastrar_bolsa.php?id_paciente=$id_paciente");
    }

    //---------------------------------------------------------------------------------------
    //EXCLUIR BOLSA DEVOLVIDA
    function excluiBolsaDevolvida($conexao, $id_bolsa, $id_paciente, $location, $motivo){

        $query_registro = conecta_query($conexao, "SELECT bd.dt_devolucao, motivo, cb.num_bolsa, h.sigla, dp.prontuario  
        FROM sth_bolsas_devolvidas bd
        INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = bd.id_bolsa 
        INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
        INNER JOIN sth_dados_paciente dp on dp.id_paciente = cb.id_paciente
        WHERE cb.id_bolsa IN ($id_bolsa)");

        if(pg_num_rows($query_registro) > 0){
            
            while($result_registro = pg_fetch_assoc($query_registro)){
                gravarExclusao($conexao, $motivo, "Bolsa devolvida", $result_registro["num_bolsa"], $result_registro["sigla"], 'null', $result_registro["prontuario"], $result_registro["motivo"]);
            }
    
            $id_bolsa_devolvida = implode(", ", gerarListaIds($conexao, "SELECT id_bolsas_devolvidas FROM sth_bolsas_devolvidas WHERE id_bolsa IN ($id_bolsa)", "id_bolsas_devolvidas"));
            $id_bolsa           = implode(", ", gerarListaIds($conexao, "SELECT id_bolsa FROM sth_bolsas_devolvidas WHERE id_bolsas_devolvidas IN ($id_bolsa_devolvida)", "id_bolsa"));
    
            conecta_query($conexao, "DELETE FROM sth_bolsas_devolvidas WHERE id_bolsas_devolvidas IN ($id_bolsa_devolvida)");
            excluiBolsaTransfundida($conexao, $id_bolsa, $id_paciente, "bolsa_devolvida", $motivo);
    
            $_SESSION['validado_bolsa_devolvida_excluir'] = $location == "bolsa_devolvida" ? 0 : -1;
        }
    }

     // ----------------------------------------------------------------------------------------------------------
     //                                                   UNIFICAR
     // ----------------------------------------------------------------------------------------------------------
     function unificar($conexao, $id_paciente_novo, $id_paciente_antigo, $id_bolsas){
        $query = conecta_query($conexao, "UPDATE sth_cadastro_bolsa SET 
        id_paciente = $id_paciente_novo
        WHERE id_paciente = $id_paciente_antigo and id_bolsa in ($id_bolsas)");
    }