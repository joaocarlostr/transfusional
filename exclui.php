<?php
    include "database.php";
    include "function.php";
    ob_start();

    //---------------------------------------------------------------------------------------
    //EXCLUIR REAÇÃO TRANSFUSIONAL
    if (isset($_GET["arquivo"]) && isset($_GET["id_paciente"]) && $_GET["arquivo"] == "reacao") {
        // pega motivo da exclusao
        if(isset($_POST["motivo"])){
            $_SESSION["exclusao_motivo"] = $_POST["motivo"];
        }

        if(isset($_POST["id_reacao"])){
            $_SESSION["id_reacao"] = $_POST["id_reacao"];
        }

        $id_reacao   = (int) $_SESSION["id_reacao"];
        $id_paciente = (int) $_GET["id_paciente"];
        $motivo      = $_SESSION["exclusao_motivo"];

        if(isset($_GET["confirma"])){
            excluiReacaoTransfusional($conexao, $id_reacao, "reacao", $motivo);
            $location = "reacao_transfusional.php?id_paciente=$id_paciente";
        }else{
            $_SESSION['validado_reacao_excluir'] = 1;
            $location = "reacao_transfusional.php?id_paciente=$id_paciente&id_reacao=$id_reacao";
        }
        
        redireciona($conexao, true, $location);
    }

    //---------------------------------------------------------------------------------------
    //EXCLUIR BOLSA NÃO DEVOLVIDA / TRANSFUNDIDA
    if (isset($_GET["arquivo"]) && isset($_GET["id_paciente"]) && $_GET["arquivo"] == "bolsa") {
        // pega motivo da exclusao
        if(isset($_POST["motivo"])){
            $_SESSION["exclusao_motivo"] = $_POST["motivo"];
        }

        if(isset($_POST["id_bolsa"])){
            $_SESSION["id_bolsa"] = $_POST["id_bolsa"];
        }

        $id_bolsa    = (int) $_SESSION["id_bolsa"];
        $id_paciente = (int) $_GET["id_paciente"];
        $motivo      = $_SESSION["exclusao_motivo"];

        if(isset($_GET["confirma"])){
            excluiBolsaTransfundida($conexao, $id_bolsa, $id_paciente, "bolsa", $motivo);
            redireciona($conexao, true, "cadastrar_bolsa.php?id_paciente=$id_paciente");
        }else{
            $_SESSION['validado_bolsa_excluir'] = 1;
            redireciona($conexao, true, "cadastrar_bolsa.php?id_paciente=$id_paciente&id_bolsa=$id_bolsa");
        }
    }

    //---------------------------------------------------------------------------------------
    //EXCLUIR BOLSA DEVOLVIDA
    if (isset($_GET["arquivo"]) && isset($_GET["id_paciente"]) && $_GET["arquivo"] == "bolsa_devolvida") {
        // pega motivo da exclusao
        if(isset($_POST["motivo"])){
            $_SESSION["exclusao_motivo"] = $_POST["motivo"];
        }

        if(isset($_POST["id_bolsa"])){
            $_SESSION["id_bolsa_devolvida"] = $_POST["id_bolsa"];
        }

        $id_bolsa_devolvida = (int) $_SESSION["id_bolsa_devolvida"];
        $id_bolsa           = implode(", ", gerarListaIds($conexao, "SELECT id_bolsa FROM sth_bolsas_devolvidas WHERE id_bolsas_devolvidas IN ($id_bolsa_devolvida)", "id_bolsa"));
        $id_paciente        = (int) $_GET["id_paciente"];
        $motivo             = $_SESSION["exclusao_motivo"];

        if(isset($_GET["confirma"])){
            excluiBolsaDevolvida($conexao, $id_bolsa, $id_paciente, "bolsa_devolvida", $motivo);
            $location = "bolsas_devolvidas.php?id_paciente=$id_paciente";
        }else{
            $_SESSION['validado_bolsa_devolvida_excluir'] = 1;
            $location = "bolsas_devolvidas.php?id_paciente=$id_paciente&id_bolsa=$id_bolsa";
        }

        redireciona($conexao, true, $location);
    }