<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Definindo uma classe que estende FPDF
    class PDF extends FPDF {      
        function Footer() { // Rodapé personalizado
            
            $this->SetY(-15); // Posição: a 15 mm do fim

            // Desenha uma linha separadora
            $this->SetDrawColor(128, 128, 128); // Define a cor da linha como preto
            $this->SetLineWidth(0.2); // Define a largura da linha

            $marginLeft  = $this->lMargin; // Margem esquerda
            $marginRight = $this->w - $this->rMargin; // Largura total da página - margem direita

            $this->Line($marginLeft, $this->GetY(), $marginRight, $this->GetY()); // Desenha a linha
            $this->SetY(-12); // Move para a posição do contador de páginas
            $this->SetFont('Arial', 'I', 8); // Fonte em itálico, tamanho 8
            $this->Cell(80, 10, iconv('utf-8', 'iso-8859-1', 'Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');

            // Número da página
            $this->Cell(0, 10, iconv('utf-8', 'iso-8859-1', 'Página ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        }
    }

    // Função para construir o conteúdo da tabela PDF de indicadores de hemocomponentes
    function ConstruirTablePDFIndicadorReacao($pdf, $query_bolsa_devolvida, $conexao, $query_bolsa) {

        $pdf->SetFont('Arial', '', 9); // Definindo a fonte e o tamanho
        $pdf->SetFillColor(200); // Define a cor cinza para o cabeçalho da tabela
        $pdf->SetTextColor(0); // Define a cor do texto como preto

        // Cabeçalho da tabela
        $pdf->Cell(35, 10, 'OBJETIVO', 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv('utf-8', 'iso-8859-1', 'Monitorar a porcentagem de bolsas devolvidas perante o total de transfusões realizadas'), 1, 1, 'C', FALSE);

        $pdf->Cell(35, 10, 'Periodicidade', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Mensal', 1, 0, 'C', false);
        $pdf->Cell(35, 10, 'Unidade de medida', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Porcentagem', 1, 0, 'C', false);
        $pdf->Cell(35, 10, 'Meta', 1, 0, 'C', true);
        $pdf->Cell(29, 10, '<= 0,30%', 1, 0, 'C', false);
        $pdf->Cell(35, 10, 'Origem dos dados', 1, 0, 'C', true);
        $pdf->Cell(45, 10, 'FIT/Sistema transfusional', 1, 1, 'C', false);

        $pdf->Cell(35, 10, iconv('utf-8', 'iso-8859-1', 'Fórmula'), 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv('utf-8', 'iso-8859-1', '(N° de bolsas devolvidas / N° de transfusões) X 100'), 1, 1, 'C', FALSE);
        $pdf->ln();

        $pdf->Cell(70, 10, 'MESES', 1, 0, 'C', true);

        for($cont = 1; $cont < 13; $cont++){
            $pdf->Cell(15, 10, nomeia_mes_sigla($cont), 1, 0, 'C', true);
        }

        $pdf->Cell(22, 10, 'TOTAL', 1, 1, 'C', true);
        $pdf->Cell(70, 10,  iconv('utf-8', 'iso-8859-1', 'N° de bolsas devolvidas (HUM)'), 1, 0, 'C', true);
        
        $total_bolsa_devolvida = $total_bolsa = $flag = 0;

        //pegando os dados
        for($cont = 1; $cont < 14; $cont++){

            $quantidade_bolsa = $quantidade_bolsa_devolvida = 0;

            $query_bolsa_linha           = $query_bolsa;
            $query_bolsa_devolvida_linha = $query_bolsa_devolvida;

            //pega qtd de bolsa transfundidas por mes
            $query_bolsa_linha .= " AND extract(MONTH FROM cb.data_transfusao) = $cont GROUP BY mes";

            $resultado_bolsa            = conecta_query($conexao, $query_bolsa_linha);
            $resultado_quantidade_bolsa = conecta_query($conexao, $query_bolsa_linha);
            $row_quantidade_bolsa       = pg_fetch_row($resultado_quantidade_bolsa);

            //pega qtd mensal e calcula total
            if($row_quantidade_bolsa > 0){
                $row_quantidade   = pg_fetch_assoc($resultado_bolsa);
                $quantidade_bolsa = $row_quantidade['qtd'];
                $total_bolsa += $quantidade_bolsa;
            }

            $query_bolsa_devolvida_linha .= " AND extract(MONTH FROM dt_devolucao) = $cont GROUP BY mes";

            $resultado_bolsa                      = conecta_query($conexao, $query_bolsa_devolvida_linha);
            $resultado_quantidade_bolsa_devolvida = conecta_query($conexao, $query_bolsa_devolvida_linha);
            $row_quantidade_bolsa_devolvida       = pg_fetch_row($resultado_quantidade_bolsa_devolvida);

            if($row_quantidade_bolsa_devolvida > 0){
                $row_quantidade             = pg_fetch_assoc($resultado_bolsa);
                $quantidade_bolsa_devolvida = $row_quantidade['qtd'];
                $total_bolsa_devolvida += $quantidade_bolsa_devolvida;
            }

            if($cont == 13){

                if($flag == 0){
                    $total = $total_bolsa_devolvida;

                }else if($flag == 1){
                    $total = $total_bolsa;

                }else if($flag == 2){

                    if($total_bolsa != 0){
                        $total  = ($total_bolsa_devolvida / $total_bolsa) * 100;
                        $total  = number_format($total, 2, ',', '');
                        $total .= "%";
                    }else{
                        $total = "0,00%";
                    }
                }

                $pdf->Cell(22, 10, $total, 1, 1, 'C', false);
                $flag++;

                if($flag > 2){

                    if($total_bolsa != 0){
                        $total  = (($total_bolsa_devolvida / $total_bolsa) * 100) / 12;
                        $total  = number_format($total, 2, ',', '');
                        $total .= "%";
                    }else{
                        $total = "0,00%";
                    }

                    $pdf->Cell(70, 10,  iconv('utf-8', 'iso-8859-1', 'Média mensal'), 1, 0, 'C', true);
                    $pdf->Cell(202, 10, $total, 1, 1, 'C', false);
                    break;

                }else if($flag == 1){
                    $pdf->Cell(70, 10,  iconv('utf-8', 'iso-8859-1', 'N° de transfusões (HUM)'), 1, 0, 'C', true);

                }else if($flag == 2){

                    if ($total_bolsa == 0 && $total_bolsa_devolvida == 0){
                        //mostra mensagem se o relatorio estiver vazio
                        $_SESSION['validado_relatorio_vazio'] = 0;
                        header("Location:relatorio.php");
                        break;
                    }

                    $pdf->Cell(70, 10, 'Realizado HUM', 1, 0, 'C', true);
                }

                $cont = $total_bolsa = $total_bolsa_devolvida = 0;
            }else{
                $quantidade = $quantidade_bolsa_devolvida;

                if($flag == 1){
                    $quantidade = $quantidade_bolsa;

                }else if($flag == 2){

                    if($quantidade_bolsa != 0){
                        $quantidade  = ($quantidade_bolsa_devolvida / $quantidade_bolsa) * 100;
                        $quantidade  = number_format($quantidade, 2, ',', '');
                        $quantidade .= "%";
                    }else{
                        $quantidade = "0,00%";
                    }
                }
                $pdf->Cell(15, 10, $quantidade, 1, 0, 'C', false);
            }
            pg_free_result($resultado_bolsa);
        }
    }

    // Cria um novo objeto FPDF
    $pdf = new PDF('L', 'mm', 'A4'); //cria uma instancia paisagem
    $pdf->AliasNbPages();
    $pdf->AddPage(); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query_bolsa_devolvida = "SELECT count(id_bolsa) AS qtd, extract(MONTH FROM dt_devolucao) AS mes 
    FROM sth_bolsas_devolvidas";

    $query_bolsa = "SELECT count(cb.id_bolsa) AS qtd, extract(MONTH FROM cb.data_transfusao) AS mes 
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa";


    // Verifique se as datas estão preenchidas e têm formato válido
    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {
        
        // Adicione a condição do intervalo de datas à consulta
        $query_bolsa_devolvida .= " WHERE dt_devolucao BETWEEN '$data_inicio' AND '$data_fim'";
        $query_bolsa           .= " WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";

        if(date('d/m', strtotime($data_inicio)) == "01/01" && 
        date('d/m', strtotime($data_fim)) == "31/12" && 
        date('Y', strtotime($data_inicio)) == date('Y', strtotime($data_fim))){
            $intervalo_selecionado = date('Y', strtotime($data_inicio));
        }
        
    } else {
        // Se as datas não estiverem preenchidas ou não tiverem formato válido, remova a parte WHERE da consulta
        $query_bolsa_devolvida .= " WHERE 1=1";
        $query_bolsa           .= " WHERE 1=1";
    }

    // Construa o cabeçalho do relatório PDF
    $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE INDICADOR DE BOLSAS NÃO TRANSFUNDIDAS HUM'),0,1,'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->ln();
    $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', "Período selecionado: $intervalo_selecionado"),0,1,'');

    ConstruirTablePDFIndicadorReacao($pdf, $query_bolsa_devolvida, $conexao, $query_bolsa);

    // Saída para o navegador em uma nova guia para visualização
    $pdf->Output('relatorio_indicador_bolsa_nao_transfundida.pdf', 'D');