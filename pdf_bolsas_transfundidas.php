<?php
    // Versão MÍNIMA para teste de integração
    // Assume que FPDF já foi incluído pelo action_gera_relatorio.php
    
    // Definindo uma classe que estende FPDF
    class PDF extends FPDF {     
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
        }
    }
    
    // Cria objeto PDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'TESTE DE INTEGRACAO OK', 0, 1, 'C');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    // Tenta mostrar uma variável que vem do action_gera_relatorio.php para confirmar
    $msg = isset($data_inicio) ? "Data Inicio: $data_inicio" : "Sem data";
    $pdf->Cell(0, 10, $msg, 0, 1);
    
    // Usa a função helper definida no action_gera_relatorio.php se existir, senão usa Output direto
    if (function_exists('output_pdf_safe')) {
        output_pdf_safe($pdf, 'teste_bolsas.pdf', 'D');
    } else {
        $pdf->Output('teste_bolsas.pdf', 'D');
    }
?>