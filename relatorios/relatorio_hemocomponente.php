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

    function construir_tabela_pdf_hemocomponentes($pdf, $query_sht_novo, $conexao, $query_bolsa) {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(200);
        $pdf->SetTextColor(0);

        $pdf->Cell(70, 10, 'HEMOCOMPONENTES', 1, 0, 'C', true);

        for ($mes = 1; $mes <= 12; $mes++) {
            $pdf->Cell(13, 10, nomeia_mes_sigla($mes), 1, 0, 'C', true);
        }

        $pdf->Cell(25, 10, 'TOTAL', 1, 0, 'C', true);
        $pdf->Cell(20, 10, iconv('utf-8', 'iso-8859-1', "MÉDIA"), 1, 1, 'C', true);
        $pdf->Cell(70, 10, 'Hemocentro', 1, 0, 'C');

        $total_bolsas = $total_sht_novo = $contador_atual = 0;

        for ($mes = 1; $mes <= 14; $mes++) {
            $quantidade_bolsas = $quantidade_sht_novo = 0;

            $query_bolsa_base    = "$query_bolsa AND extract(MONTH from cb.data_transfusao) = $mes GROUP BY mes";
            $query_sht_novo_base = "$query_sht_novo AND extract(MONTH from cb.data_transfusao) = $mes AND shtnovo = 'ok' GROUP BY mes";

            $resultado_bolsa    = conecta_query($conexao, $query_bolsa_base);
            $resultado_sht_novo = conecta_query($conexao, $query_sht_novo_base);

            if (pg_num_rows($resultado_bolsa) > 0) {
                $row_quantidade_bolsas = pg_fetch_assoc($resultado_bolsa);
                $quantidade_bolsas     = $row_quantidade_bolsas['qtd'];
                $total_bolsas += $quantidade_bolsas;
            }

            if (pg_num_rows($resultado_sht_novo) > 0) {
                $row_quantidade_sht_novo = pg_fetch_assoc($resultado_sht_novo);
                $quantidade_sht_novo     = $row_quantidade_sht_novo['qtd'];
                $total_sht_novo += $quantidade_sht_novo;
            }

            if ($mes == 13) {
                $total = $contador_atual == 1 ? $total_bolsas : ($contador_atual == 2 ? $total_sht_novo : '');
                $media = $contador_atual == 1 
                ? number_format($total_bolsas / 12, 2, ',', '') 
                : ($contador_atual == 2 
                    ? number_format($total_sht_novo / 12, 2, ',', '') 
                    : '');

                $pdf->Cell(25, 10, $total, 1, 0, 'C');
                $pdf->Cell(20, 10, $media, 1, 1, 'C');
                $contador_atual++;

                if ($contador_atual > 3) {
                    if ($total_bolsas == 0 && $total_sht_novo == 0) {
                        $_SESSION['validado_relatorio_vazio'] = 0;
                        header("Location:relatorio.php");
                    }

                    break;
                } else {
                    $titulos = ['Serviço Transfusional', 'SHT NOVO', 'Diferença total de bolsas'];
                    $pdf->Cell(70, 10, iconv('utf-8', 'iso-8859-1', $titulos[$contador_atual - 1]), 1, 0, 'C');
                }

                $mes = $total_bolsas = $total_sht_novo = 0; // Reinicial contador
            } else {
                $quantidade = ($contador_atual == 1) ? $quantidade_bolsas : $quantidade_sht_novo;
                if ($contador_atual > 0 && $contador_atual != 3) {
                    $pdf->Cell(13, 10, $quantidade, 1, 0, 'C');
                } else {
                    $pdf->Cell(13, 10, '', 1, 0, 'C');
                }
            }

            pg_free_result($resultado_bolsa);
            pg_free_result($resultado_sht_novo);
        }
    }

    $pdf = new PDF('L', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetDrawColor(150);

    $query_sht_novo = "SELECT count(cb.id_bolsa) as qtd, extract(MONTH from cb.data_transfusao) as mes 
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa";

    $query_bolsa = "SELECT count(cb.id_bolsa) as qtd, extract(MONTH from cb.data_transfusao) as mes 
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa";

    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {
        $query_sht_novo .= " WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
        $query_bolsa    .= " WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";

        if (date('d/m', strtotime($data_inicio)) == "01/01" && 
        date('d/m', strtotime($data_fim)) == "31/12" && 
        date('Y', strtotime($data_inicio)) == date('Y', strtotime($data_fim))) {
            $intervalo_selecionado = date('Y', strtotime($data_inicio));
        }
    } else {
        $query_sht_novo .= " WHERE 1=1";
        $query_bolsa    .= " WHERE 1=1";
    }

    $pdf->Image('img/hum_relatorio.png', 10, 10, 50);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(280, 10, iconv('utf-8', 'iso-8859-1', 'RELATÓRIO FINAL/ANUAL DE HEMOCOMPONENTES TRANSFUNDIDOS HUM'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln();
    $pdf->Cell(280, 10, iconv('utf-8', 'iso-8859-1', "Período selecionado: $intervalo_selecionado"), 0, 1, '');

    construir_tabela_pdf_hemocomponentes($pdf, $query_sht_novo, $conexao, $query_bolsa);

    $pdf->Output('relatorio_final_hemocomponentes_transfundidos_hum.pdf', 'D');