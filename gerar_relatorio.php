<?php
    require  __DIR__ . '/libraries/fpdf.php';

    // Incluir o script de conexão ao banco de dados
    include "database.php";
    include "function.php";

    // // Adquirir o intervalo do parâmetro GET, data inicio e fim
    $intervalo_inicio      = isset($_POST['data_inicio']) && !empty($_POST['data_inicio']) ? date('d/m/Y', strtotime($_POST['data_inicio'])) : '';
    $intervalo_fim         = isset($_POST['data_fim'])    && !empty($_POST['data_fim'])    ? date('d/m/Y', strtotime($_POST['data_fim']))    : '';
    
    $intervalo_selecionado = !empty($intervalo_inicio)    && !empty($intervalo_fim)        ? "$intervalo_inicio a $intervalo_fim" : 'Intervalo não especificado';

    // // Verifique se o formulário foi enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Receba os dados do formulário
        $tipo            = isset($_POST["tipo"])             ? $_POST["tipo"]             : '';
        $data_inicio     = isset($_POST["data_inicio"])      ? $_POST["data_inicio"]      : '';
        $data_fim        = isset($_POST["data_fim"])         ? $_POST["data_fim"]         : '';
        $tipo_reacao     = isset($_POST["tipo_reacao"])      ? $_POST["tipo_reacao"]      : null;
        $id_setor        = isset($_POST["id_setor"])         ? $_POST["id_setor"]         : null;
        $bolsa           = isset($_POST["bolsa"])            ? $_POST["bolsa"]            : null;
        $hemocomponente  = isset($_POST["hemocomponente"])   ? $_POST["hemocomponente"]   : null;
        $importa_arquivo = isset($_FILES["importa_arquivo"]) ? $_FILES["importa_arquivo"] : null;
        $prontuario      = isset($_POST["prontuario"])       ? $_POST["prontuario"]       : null;
        
        if(((!empty($bolsa)) && ($tipo == "indi_nao_conformidade" || $tipo == "indi_bolsa_reserva" || $tipo == "indi_bolsa_devolvida" 
        || $tipo == "reacao_transfusional" || $tipo == "nao_conformidade" || $tipo == "tipo_setor" || empty($tipo))) 

        || ((!empty($id_setor)) && ($tipo == "indi_nao_conformidade" || $tipo == "indi_bolsa_reserva" || $tipo == "indi_bolsa_devolvida" 
        || $tipo == "reacao_transfusional" || $tipo == "bolsa_devolvida" || empty($tipo)))

        || ((!empty($importa_arquivo["name"])) && (!empty($bolsa) || !empty($id_setor) || !empty($tipo) || !empty($hemocomponente) || !empty($tipo_reacao)))
        || ((!empty($hemocomponente)) && (!empty($tipo) || !empty($bolsa) || !empty($id_setor) || !empty($tipo_reacao)))
        || ((!empty($tipo_reacao)) && (!empty($bolsa) || !empty($id_setor) || !empty($tipo)))
        || ((!empty($prontuario)) && ($tipo != "bolsa" && $tipo != "bolsa_repetida"))
        || ((!empty($data_inicio) && empty($data_fim)) || (empty($data_inicio) && !empty($data_fim)))
        // || ((!empty($data_inicio) && !empty($data_fim)) && (empty($tipo) && empty($tipo_reacao) && empty($hemocomponente)))
        || ($tipo == "bolsa_devolvida" && !empty($id_setor))){

            header("Location:relatorio.php");
            $_SESSION['validado_relatorio'] = 0;
        }else{
            if($tipo == "reacao_transfusional"){
                include_once "relatorios/relatorio_indicador_reacao.php";

            }else if ($tipo == "paciente") {
                // echo "valor post: " . $importa_arquivo["name"] . $importa_arquivo["type"] . $importa_arquivo;
                include_once "relatorios/relatorio_paciente.php";

            } else if ($tipo == "bolsa") {
                include_once "relatorios/relatorio_bolsa.php";

            }else if($tipo_reacao == "todas"){
                include_once "relatorios/relatorio_reacao.php";

            }else if($hemocomponente == "todos"){
                include_once "relatorios/relatorio_hemocomponente.php";

            }else if($tipo == "bolsa_devolvida"){
                include_once "relatorios/relatorio_bolsa_devolvida.php";

            }else if($tipo == "indi_nao_conformidade"){
                include_once "relatorios/relatorio_indicador_nao_conformidade.php";

            }else if($tipo == "nao_conformidade"){
                include_once "relatorios/relatorio_nao_conformidade.php";

            }else if($tipo == "indi_bolsa_reserva"){
                include_once "relatorios/relatorio_indicador_reserva.php";

            }else if($tipo == "indi_bolsa_devolvida"){
                include_once "relatorios/relatorio_indicador_bolsa_devolvida.php";

            }else if($tipo == "bolsa_reserva"){
                include_once "relatorios/relatorio_bolsa_reserva.php";

            }else if($tipo == "tipo_sanguineo"){
                include_once "relatorios/relatorio_tipo_sanguineo.php";

            }else if($tipo == "tipo_setor"){
                include_once "relatorios/relatorio_setor.php";

            }else if($tipo == "bolsa_repetida"){
                include_once "relatorios/relatorio_bolsa_repetida.php";

            }else if($tipo == "paciente_sem_registro"){
                include_once "relatorios/relatorio_paciente_sem_registro.php";

            }else if($tipo == "tipo_reacao_paciente"){
                include_once "relatorios/relatorio_reacao_paciente.php"; 

            }else{
                // echo "valor post: " . $importa_arquivo["name"] . $importa_arquivo["type"];
                include_once "relatorios/relatorio_importa_arquivo.php";
            }
        } 
    }

    // Feche a conexão com o banco de dados
    pg_close($conexao);