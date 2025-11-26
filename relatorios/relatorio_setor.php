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

    function ConstruirTablePDFSetor($pdf, $resultado_consulta) {

        $pdf->SetFont('Arial', '', 10); // Definindo a fonte e o tamanho
        $pdf->SetFillColor(200); // Define a cor cinza 
    
        // Cabeçalho da tabela
        $pdf->Cell(100, 10, iconv('utf-8', 'iso-8859-1', 'Setores'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, iconv('utf-8', 'iso-8859-1', 'Transfusões'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, iconv('utf-8', 'iso-8859-1', 'FIT'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, iconv('utf-8', 'iso-8859-1', 'Não conformidade'), 1, 1, 'C', true);
    
        while ($row = pg_fetch_assoc($resultado_consulta)) {    
            $pdf->Cell(100, 8, iconv('utf-8', 'iso-8859-1', " $row[nome_setor]"), 1, 0, 'L');
            $pdf->Cell(40, 8, iconv('utf-8', 'iso-8859-1', $row['qtd_bolsa']), 1, 0, 'C');
            $pdf->Cell(40, 8, iconv('utf-8', 'iso-8859-1', $row['qtd_rt']), 1, 0, 'C');
            $pdf->Cell(40, 8, iconv('utf-8', 'iso-8859-1', $row['qtd_nao_conformidade']), 1, 1, 'C');
        }
    }   

    // Cria um novo objeto FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    $query = "SELECT s.nome_setor, 
    COUNT(DISTINCT c.id_bolsa) AS qtd_bolsa, 
    COUNT(DISTINCT rt.id_transfusionais) AS qtd_rt,
    COUNT(DISTINCT cnc.id_controle_nao_conformidade) AS qtd_nao_conformidade
    FROM sth_setores s 
    LEFT JOIN sth_controle c ON s.id_setor = c.id_setor
    LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = c.id_bolsa
    LEFT JOIN sth_controle_nao_conformidade cnc ON cnc.id_controle = c.id_controle";

    // Verifique se as datas estão preenchidas e tem formato válido
    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {

        // Adicione a condição do intervalo de datas à consulta
        $query .= " WHERE c.dt_busca_ativa BETWEEN '$data_inicio' AND '$data_fim'";
    } else {
        // Se as datas não estiverem preenchidas ou não tiverem formato válido, remova a parte WHERE da consulta
        $query .= " WHERE 1=1";
    }

    if (!empty($id_setor)) {
        if ($id_setor == "pa_geral") {
            $id_setor_lista = [];
            
            $query_pa_geral = "SELECT s.id_setor 
                FROM sth_controle c 
                RIGHT JOIN sth_setores s ON s.id_setor = c.id_setor 
                WHERE SUBSTRING(s.nome_setor FROM 1 FOR 2) = 'PA' 
                ORDER BY s.nome_setor";
            
            $resultado_pa_geral = conecta_query($conexao, $query_pa_geral);

            while ($row_id_pa_geral = pg_fetch_assoc($resultado_pa_geral)) {
                $id_setor_lista[] = $row_id_pa_geral["id_setor"];
            }

            $id_setor = implode(", ", $id_setor_lista);
        }

        $query .= " AND c.id_setor IN ($id_setor)";
    }

    $query .= " GROUP BY s.nome_setor, s.status
    HAVING COUNT(DISTINCT c.id_setor) > 0 OR s.status = 'ativo'
    ORDER BY s.nome_setor";

    // Execute a consulta
    $resultado_consulta  = conecta_query($conexao, $query);
    $quantidade_consulta = pg_num_rows($resultado_consulta);

    if($quantidade_consulta > 0){
        
        // Adicione a imagem ao lado do título
        $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

        // Construa o cabeçalho do relatório PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE QUANTIDADE DE BOLSAS TRANSFUNDIDAS POR SETOR HUM'),0,1,'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->ln();
        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', "Período selecionado: $intervalo_selecionado"),0,1,'');

        if (!empty($id_setor)) {

            // Consulta para obter o nome do setor com base no id_setor
            $query_setor     = "SELECT nome_setor FROM sth_setores WHERE id_setor = $id_setor";
            $resultado_setor = conecta_query($conexao, $query_setor);

            if ($row_setor = pg_fetch_assoc($resultado_setor)) {
                $nome_setor = $row_setor['nome_setor'];
                $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', "Setor: $nome_setor"),0,1,"");
                
            } else {
                $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', "Setor não encontrado"),0,1,"");
            }

            // Liberar resultados da consulta do setor
            pg_free_result($resultado_setor);
        }

        // Construa o conteúdo do PDF
        ConstruirTablePDFSetor($pdf, $resultado_consulta);
        pg_free_result($resultado_consulta);

        // Saí­da do PDF para o navegador
        $pdf->Output('relatorio_quantidade_transfusoes_setor.pdf', 'D');
    }else{
        //mostra mensagem se o relatorio estiver vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location:relatorio.php");
        exit();
    }