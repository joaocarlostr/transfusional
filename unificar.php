<?php
    include "database.php";
    include "function.php";
    ob_start();

    // pega id do paciente antigo, paciente a ser excluido
    if(isset($_POST["id_paciente"])){
        $_SESSION["unificar_id_paciente_antigo"] = $_POST["id_paciente"];
    }

    // pega motivo da exclusao
    if(isset($_POST["motivo"])){
        $_SESSION["unificar_motivo"] = $_POST["motivo"];
    }

    //pega id do paciente novo, paciente que vai receber os dados ao unificar
    if(isset($_POST["unificar"])){
        $_SESSION["unificar_id_paciente_novo"] = $_POST["unificar"];
    }

    $motivo              = isset($_SESSION["unificar_motivo"])             ? $_SESSION["unificar_motivo"]                   : "";
    $id_paciente_antigo  = isset($_SESSION["unificar_id_paciente_antigo"]) ? (int) $_SESSION["unificar_id_paciente_antigo"] : 0;
    $id_paciente_novo    = isset($_SESSION["unificar_id_paciente_novo"])   ? (int) $_SESSION["unificar_id_paciente_novo"]   : 0;
    $qtd_paciente_antigo = isset($_SESSION["qtd_bolsa_paciente_antigo"])   ? (int) $_SESSION["qtd_bolsa_paciente_antigo"]   : 0;

    $id_bolsas         = null;
    $qtd_numeros_antes = $qtd_bolsa_selecionada = 0;

    //seleciona os valores da chave estrangeira
    $query = "SELECT dp.id_paciente, cb.id_bolsa, c.id_controle, rt.id_transfusionais, bd.id_bolsas_devolvidas FROM sth_dados_paciente dp
    LEFT JOIN sth_cadastro_bolsa cb ON cb.id_paciente = dp.id_paciente
    LEFT JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
    LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa
    LEFT JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa
    WHERE dp.id_paciente = $id_paciente_antigo";

    $result = conecta_query($conexao, $query);
    $row    = pg_fetch_assoc($result);

    if(empty($row["id_bolsa"]) && empty($row["id_controle"]) && empty($row["id_transfusionais"])){
        //paciente nao tem nenhuma chave estrangeira vinculada
        $_SESSION["unificar"] = 1; // vazio, pode excluir direto, mensagem para confirmar
        
        if(isset($_GET["unificar_resposta"]) && $_GET["unificar_resposta"] == 2){
            excluirPaciente($conexao, $id_paciente_antigo, null, null, $qtd_paciente_antigo, $motivo);
            $_SESSION["unificar"] = 4; // mensagem success exclusão
        }
    }else{
        //paciente possui chaves estrangeira vinculadas
        $_SESSION["unificar"] = 2;
        
        if(isset($_GET["unificar_resposta"])){
            if($_GET["unificar_resposta"] == 0){
                $_SESSION["unificar"] = 5; // alerta confirmar o paciente selecionado 

            }else if($_GET["unificar_resposta"] == 1){
                for($i=1; $i<=$qtd_paciente_antigo; $i++){
                    $bolsas_unificar[$i] = array( $i => (int) $_POST["bolsa_antigo_$i"], );

                    if(!empty($bolsas_unificar[$i][$i])){
                        if($qtd_numeros_antes > 0){
                            $id_bolsas .=  ", ";
                        }

                        $id_bolsas .= $bolsas_unificar[$i][$i];
                        $qtd_numeros_antes++;
                        $qtd_bolsa_selecionada++;
                    }
                }

                // echo "qtd: $qtd_paciente_antigo sele: $qtd_bolsa_selecionada bolsa: $id_bolsas";
                // var_dump($bolsas_unificar);

                $_SESSION["unificar"] = 4; // mensagem de sucesso - exclusão, nenhum dados selecionado para unificar
                if(!empty($qtd_bolsa_selecionada)){
                    unificar($conexao, $id_paciente_novo, $id_paciente_antigo, $id_bolsas);
                    $_SESSION["unificar"] = 3; // mensagem de sucesso - unificação
                }

                excluirPaciente($conexao, $id_paciente_antigo, $qtd_bolsa_selecionada, null, $qtd_paciente_antigo, $motivo);

            }else if($_GET["unificar_resposta"] == 2){
                excluirPaciente($conexao, $id_paciente_antigo, null, null, $qtd_paciente_antigo, $motivo);
                $_SESSION["unificar"] = 4; // mensagem success exclusão
            }
        }
    }
    redireciona($conexao, $query, "buscar_paciente.php");