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
            $this->SetFont('Arial', 'I', 7); // Fonte em itálico, tamanho 8
            $this->Cell(80, 10, iconv('utf-8', 'iso-8859-1', 'Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');

            // Número da página
            $this->Cell(0, 10, iconv('utf-8', 'iso-8859-1', 'Página ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        }
    }

    function ConstruirTablePDFPaciente($pdf, $resultado_consulta) {

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetFillColor(200); // Define a cor cinza 
    
        // Cabeçalho da tabela
        $pdf->Cell(10, 6, iconv('utf-8', 'iso-8859-1', 'N°'), 1, 0, 'C', true);
        $pdf->Cell(50, 6, iconv('utf-8', 'iso-8859-1', 'Paciente'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'Dt nascimento'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'ABO'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'Rh'), 1, 0, 'C', true);
        $pdf->Cell(13, 6, iconv('utf-8', 'iso-8859-1', 'Prontuário'), 1, 0, 'C', true);
        $pdf->Cell(23, 6, iconv('utf-8', 'iso-8859-1', 'Registro'), 1, 1, 'C', true);
        // Processar os resultados
        $cont = 0;

        while ($row = pg_fetch_assoc($resultado_consulta)) {  

            // se tiver nome social preenchido uso o social, se nao, uso o do documento
            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
            $nome_paciente = (strlen($nome_paciente) > 29) ? substr($nome_paciente, 0, 29) . "..." : $nome_paciente;

            $cont++;
            $pdf->Cell(10, 6, $cont, 1, 0, 'C');
            $pdf->Cell(50, 6, iconv('utf-8', 'iso-8859-1', $nome_paciente), 1, 0, 'L');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', date('d/m/Y', strtotime($row['dt_nasc']))), 1, 0, 'C');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', $row['abo']), 1, 0, 'C');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', $row['rh_d']), 1, 0, 'C');
            $pdf->Cell(13, 6, iconv('utf-8', 'iso-8859-1', $row['prontuario']), 1, 0, 'C');
            $pdf->Cell(23, 6, "", 1, 1, 'C');
        }
    }   

    // Cria um novo objeto FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P'); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query = "SELECT nome_completo, id_paciente, nome_social, dt_nasc, abo, rh_d, cpf, prontuario, 
    CASE WHEN nome_social IS NULL OR nome_social = '' THEN nome_completo ELSE nome_social END AS nome
    FROM sth_dados_paciente 
    WHERE registro IS NULL OR registro = '' 
    ORDER BY nome";

    // Execute a consulta
    $resultado_consulta  = conecta_query($conexao, $query);
    $quantidade_consulta = pg_num_rows($resultado_consulta);

    if($quantidade_consulta > 0){
        
        // Adicione a imagem ao lado do título
        $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

        // Construa o cabeçalho do relatório PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(230,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE PACIENTES SEM NÚMERO DE REGISTRO HUM'),0,1,'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->ln();
        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', "Período selecionado: $intervalo_selecionado"),0,1,'');

        // Construa o conteúdo do PDF
        ConstruirTablePDFPaciente($pdf, $resultado_consulta);
        pg_free_result($resultado_consulta);

        // Saí­da do PDF para o navegador
        $pdf->Output('relatorio_pacientes_sem_registro.pdf', 'D');
    }else{
        //mostra mensagem se o relatorio estiver vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location:relatorio.php");
        exit();
    }