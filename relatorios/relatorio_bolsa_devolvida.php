<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // RODAPÉ -------------------------------------------------------------------------------
    class PDF extends FPDF {
        function Footer() {
            $this->SetY(-15); // Posição: a 15 mm do fim

            // Desenha uma linha separadora
            $this->SetDrawColor(128, 128, 128); // Cor da linha
            $this->SetLineWidth(0.2); // Largura da linha

            $marginLeft  = $this->lMargin; // Margem esquerda
            $marginRight = $this->w - $this->rMargin; // Largura total da página - margem direita

            $this->Line($marginLeft, $this->GetY(), $marginRight, $this->GetY()); // Desenha a linha
            $this->SetY(-12); // Move para a posição do contador de páginas
            $this->SetFont('Arial', 'I', 7);
            $this->Cell(80, 10, iconv('utf-8', 'iso-8859-1', 'Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');

            // Número da página
            $this->Cell(0, 10, iconv('utf-8', 'iso-8859-1', 'Página ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        }
    }

    // CONSTRUÇÃO DA TABELA -------------------------------------------------------------------
    function ConstruirTablePDFBolsaDevolvida($pdf, $resultado_consulta) {
        $pdf->SetFont('Arial', '', 7); // Fonte e tamanho
        $pdf->SetFillColor(200); // Cor do cabeçalho da tabela
        $pdf->SetTextColor(0); // Cor do texto

        // Cabeçalho da tabela
        $pdf->Cell(10, 6, iconv('UTF-8', 'ISO-8859-1', 'N°'), 1, 0, 'C', true);
        $pdf->Cell(20, 6, iconv('UTF-8', 'ISO-8859-1', 'Data Devolução'), 1, 0, 'C', true);
        $pdf->Cell(22, 6, iconv('UTF-8', 'ISO-8859-1', 'Bolsa'), 1, 0, 'C', true);
        $pdf->Cell(20, 6, iconv('UTF-8', 'ISO-8859-1', 'Número SUS'), 1, 0, 'C', true);
        $pdf->Cell(30, 6, iconv('UTF-8', 'ISO-8859-1', 'Hemoc / Atributos'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, iconv('UTF-8', 'ISO-8859-1', 'Motivo'), 1, 0, 'C', true);
        $pdf->Cell(53, 6, iconv('UTF-8', 'ISO-8859-1', 'Observação'), 1, 1, 'C', true);

        // Processar os resultados
        $cont = 0;

        while ($row_resultado_consulta = pg_fetch_assoc($resultado_consulta)) {
            $observacao = $row_resultado_consulta["observacao"];
            $observacao = (strlen($observacao) > 37) ? substr($observacao, 0, 37) . "..." : $observacao;
            
            $cont++;
            $pdf->Cell(10, 6, $cont, 1, 0, 'C');
            $pdf->Cell(20, 6, date('d/m/Y', strtotime($row_resultado_consulta['dt_devolucao'])), 1, 0, 'C');
            $pdf->Cell(22, 6, $row_resultado_consulta["num_bolsa"], 1, 0, 'C');
            $pdf->Cell(20, 6, $row_resultado_consulta["num_sus"], 1, 0, 'C');
            $pdf->Cell(30, 6, iconv('UTF-8', 'ISO-8859-1', " $row_resultado_consulta[sigla]"), 1, 0, 'L');
            $pdf->Cell(40, 6, iconv('UTF-8', 'ISO-8859-1', " $row_resultado_consulta[motivo]"), 1, 0, 'L');
            $pdf->Cell(53, 6, iconv('UTF-8', 'ISO-8859-1', " $observacao"), 1, 1, 'L');
        }
    }

    // Cria um novo objeto FPDF -----------------------------------------------------------
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P'); // Adiciona uma página retrato
    $pdf->SetDrawColor(150); // Cor dos retângulos

    // Construa a parte da consulta SQL ---------------------------------------------------
    $query_bolsa = "SELECT cb.num_bolsa, cb.num_sus, bd.observacao, h.sigla, bd.dt_devolucao, motivo
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
    INNER JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa";

    // Verifique se as datas estão preenchidas e têm formato válido
    if (!empty($data_inicio) && !empty($data_fim) &&
        DateTime::createFromFormat('Y-m-d', $data_inicio) !== false &&
        DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {

        $query_bolsa .= " WHERE bd.dt_devolucao BETWEEN '$data_inicio' AND '$data_fim'";
    } else {
        $query_bolsa .= " WHERE 1=1"; // Para evitar erros se as datas não forem válidas
    }

    $query_bolsa .= " ORDER BY bd.dt_devolucao";

    // Execute a consulta
    $resultado_consulta  = conecta_query($conexao, $query_bolsa);
    $quantidade_consulta = pg_num_rows($resultado_consulta);

    // GERA RELATÓRIO --------------------------------------------------------------------------
    if ($quantidade_consulta > 0) {
        
        $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste conforme necessário

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(230, 10, iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE BOLSAS NÃO TRANSFUNDIDAS HUM'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->ln();
        $pdf->Cell(280, 10, iconv('utf-8', 'iso-8859-1', "Período selecionado: $intervalo_selecionado"), 0, 1, '');
        
        // Construa o conteúdo do PDF
        ConstruirTablePDFBolsaDevolvida($pdf, $resultado_consulta);
        pg_free_result($resultado_consulta);
        
        // Saída do PDF para o navegador
        $pdf->Output('relatorio_bolsas_nao_transfundidas.pdf', 'D');

    } else {
        // Mensagem se o relatório for vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location: relatorio.php");
        exit();
    }