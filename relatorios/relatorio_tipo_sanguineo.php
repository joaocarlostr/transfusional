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
    function ConstruirTablePDFHemocomponente($pdf, $query_abo, $conexao) {

        $pdf->SetFont('Arial', '', 10); // Definindo a fonte e o tamanho
        $pdf->SetFillColor(200); // Define a cor cinza para o cabeçalho da tabela
        $pdf->SetTextColor(0); // Define a cor do texto como preto

        // Cabeçalho da tabela
        $pdf->Cell(70, 10, '', 1, 0, 'C', true); // rh_d
        $cabecalhos = ['A', 'B', 'AB', 'O', 'Outro', 'Desconhecido'];

        foreach ($cabecalhos as $cabecalho) {
            $pdf->Cell(25, 10, $cabecalho, 1, 0, 'C', true);
        }

        $pdf->ln();

        $tipos_rh_d = ["Positivo", "Negativo", "Outro", "Desconhecido"];
        
        foreach ($tipos_rh_d as $rh_d) {
            $pdf->Cell(70, 10, $rh_d, 1, 0, 'C');

            // Tipos de ABO
            foreach (['A', 'B', 'AB', 'O', 'Outro', 'Desconhecido'] as $abo) {
                $query_abo_aux  = "$query_abo AND rh_d = '$rh_d' AND abo = '$abo' GROUP BY abo, rh_d ORDER BY abo";
                $resultado_abo  = conecta_query($conexao, $query_abo_aux);
                $row_abo        = pg_fetch_assoc($resultado_abo);
                $quantidade_abo = !empty($row_abo["qtd"]) ? $row_abo["qtd"] : 0;

                // Formatar e adicionar a célula
                $pdf->Cell(25, 10, iconv('utf-8', 'iso-8859-1', $quantidade_abo), 1, $abo === 'Desconhecido' ? 1 : 0, 'C');
                pg_free_result($resultado_abo);
            }
        }
    }

    // Cria um novo objeto FPDF
    $pdf = new PDF('L', 'mm', 'A4'); //cria uma instancia paisagem
    $pdf->AliasNbPages();
    $pdf->AddPage(); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query_abo = "SELECT count(dp.abo) AS qtd, abo, rh_d
    FROM sth_dados_paciente dp
    WHERE dp.id_paciente IN (SELECT id_paciente FROM sth_cadastro_bolsa WHERE id_bolsa IN 
    (SELECT c.id_bolsa FROM sth_controle c INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa";

    // Verifique se as datas estão preenchidas e tem formato válido
    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {

        // Adicione a condição do intervalo de datas à consulta
        $query_abo .= " where cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'))";
        
        if(date('d/m', strtotime($data_inicio)) == "01/01" && 
        date('d/m', strtotime($data_fim)) == "31/12" && 
        date('Y', strtotime($data_inicio)) == date('Y', strtotime($data_fim))){
            $intervalo_selecionado = date('Y', strtotime($data_inicio));
        }
    } else {
        // Se as datas não estiverem preenchidas ou não tiverem formato válido, remova a parte WHERE da consulta
        $query_abo .= "))";
    }

    // Construa o cabeçalho do relatório PDF
    $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO TIPO SANGUÍNEO DOS PACIENTES TRANSFUNDIDOS HUM'),0,1,'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->ln();
    $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', "Período selecionado: $intervalo_selecionado"),0,1,'');

    // Construa o conteúdo do PDF
    ConstruirTablePDFHemocomponente($pdf, $query_abo, $conexao);

    // Saída do PDF para o navegador
    $pdf->Output('relatorio_tipo_sanguineo_pacientes_transfundidos_hum.pdf', 'D');