<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

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
            $this->SetFont('Arial', 'I', 7); // Fonte em itálico, tamanho 8
            $this->Cell(80, 10, iconv('utf-8', 'iso-8859-1', 'Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');

            // Número da página
            $this->Cell(0, 10, iconv('utf-8', 'iso-8859-1', 'Página ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        }
    }

    function ConstruirTablePDFReacao($pdf, $query_reacao_base, $conexao, $query_descricao_base) {
        $total = 0;

        for ($cont = 1; $cont < 13; $cont++) {

            // Pega a quantidade e as descrições de reações por mês
            $query_reacao    = "$query_reacao_base AND extract(MONTH from rt.data) = $cont GROUP BY mes";
            $query_descricao = "$query_descricao_base AND extract(MONTH from rt.data) = $cont";

            $resultado_reacao    = conecta_query($conexao, $query_reacao);
            $resultado_descricao = conecta_query($conexao, $query_descricao);

            // Verifica se há resultados de quantidade
            $quantidade_reacao = 0;

            if ($row_qtd = pg_fetch_assoc($resultado_reacao)) {
                $quantidade_reacao = $row_qtd['qtd'];
                $total += $quantidade_reacao;
            }

            // Coleta as descrições
            $descricao = "";

            while ($row_descr = pg_fetch_assoc($resultado_descricao)) {
                $descricao .= $row_descr['descricao'] . "\n"; 
            }

            // Adiciona dados ao PDF
            $mes = nomeia_mes($cont);
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(200);
            $pdf->Cell(250, 10, iconv('utf-8', 'iso-8859-1', $mes), 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(220);
            $pdf->Cell(250, 10, iconv('utf-8', 'iso-8859-1', "Notvisa HUM - quantidade de reações transfusionais neste mês: $quantidade_reacao"), 1, 1, 'C', true);
            $pdf->MultiCell(250, 10, iconv('utf-8', 'iso-8859-1', $descricao), 1, 'L');

            pg_free_result($resultado_reacao);
            pg_free_result($resultado_descricao);
        }

        if ($total == 0) {
            $_SESSION['validado_relatorio_vazio'] = 0;
            header("Location: relatorio.php");
            exit();
        }

        $pdf->Cell(250, 10, iconv('utf-8', 'iso-8859-1', "TOTAL: $total"), 1, 0, 'L', true);
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L');
    $pdf->SetDrawColor(50);

    $query_reacao = "SELECT count(tp.descricao) AS qtd, extract(MONTH FROM rt.data) AS mes 
        FROM sth_tipos_reacoes tp
        INNER JOIN sth_reacoes_transfusionais rt ON rt.tipo_reacao = tp.id_reacao";

    $query_descricao = "SELECT tp.descricao, rt.data FROM sth_tipos_reacoes tp
        INNER JOIN sth_reacoes_transfusionais rt ON rt.tipo_reacao = tp.id_reacao";

    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {
        $query_reacao    .= " WHERE rt.data BETWEEN '$data_inicio' AND '$data_fim'";
        $query_descricao .= " WHERE rt.data BETWEEN '$data_inicio' AND '$data_fim'";

        $intervalo = (date('d/m', strtotime($data_inicio)) == "01/01" && 
        date('d/m', strtotime($data_fim)) == "31/12" && 
        date('Y', strtotime($data_inicio)) == date('Y', strtotime($data_fim)))
            ? date('Y', strtotime($data_inicio))
            : $intervalo_selecionado;

    } else {
        $query_reacao    .= " WHERE 1=1";
        $query_descricao .= " WHERE 1=1";
        $intervalo        = 'Intervalo não selecionado'; 
    }

    $pdf->Image('img/hum_relatorio.png', 10, 10, 50);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(280, 10, iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE INDICADOR DE REAÇÕES TRANSFUSIONAIS HUM'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->ln();
    $pdf->Cell(250, 10, iconv('utf-8', 'iso-8859-1', "Intervalo selecionado: $intervalo"), 0, 1, '');

    ConstruirTablePDFReacao($pdf, $query_reacao, $conexao, $query_descricao);
    $pdf->Output('relatorio_reacoes_transfusionais.pdf', 'D');