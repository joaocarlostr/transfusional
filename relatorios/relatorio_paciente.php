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

    function ConstruirTablePDFPaciente($pdf, $resultado_consulta, $conexao) {

        $pdf->SetFont('Arial', '', 7); // Definindo a fonte e o tamanho
        $pdf->SetFillColor(200); // Define a cor cinza 
    
        // Cabeçalho da tabela
        $pdf->Cell(10, 6, iconv('utf-8', 'iso-8859-1', 'N°'), 1, 0, 'C', true);
        $pdf->Cell(16, 6, iconv('utf-8', 'iso-8859-1', 'Dt transfusão'), 1, 0, 'C', true);
        $pdf->Cell(12, 6, iconv('utf-8', 'iso-8859-1', 'Horário'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'SUS da bolsa'), 1, 0, 'C', true);
        $pdf->Cell(45, 6, iconv('utf-8', 'iso-8859-1', 'Paciente'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'Dt nascimento'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'ABO'), 1, 0, 'C', true);
        $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', 'Rh'), 1, 0, 'C', true);
        $pdf->Cell(13, 6, iconv('utf-8', 'iso-8859-1', 'Prontuário'), 1, 0, 'C', true);
        $pdf->Cell(16, 6, iconv('utf-8', 'iso-8859-1', 'Número RT'), 1, 0, 'C', true);
        $pdf->Cell(6, 6, iconv('utf-8', 'iso-8859-1', 'FIT'), 1, 0, 'C', true);
        $pdf->Cell(6, 6, iconv('utf-8', 'iso-8859-1', 'NC'), 1, 1, 'C', true);
    
        // Processar os resultados
        $cont = 0;

        while ($row = pg_fetch_assoc($resultado_consulta)) {  

            // se tiver nome social preenchido uso o social, se nao, uso o do documento
            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
            $nome_paciente = (strlen($nome_paciente) > 23) ? substr($nome_paciente, 0, 23) . "..." : $nome_paciente;

            $query_paciente_reacao = "SELECT rt.id_transfusionais
            FROM sth_reacoes_transfusionais rt
            INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = rt.id_bolsa
            WHERE cb.id_paciente = $row[id_paciente] AND rt.id_bolsa = $row[id_bolsa] LIMIT 5";

            $resultado_paciente_reacao = conecta_query($conexao, $query_paciente_reacao);
            $quantidade_reacoes        = pg_num_rows($resultado_paciente_reacao);

            $fit = null;

            if($quantidade_reacoes > 0){
                $fit = "sim";
            }

            $query_nao_conformidade = "SELECT c.id_controle 
            FROM sth_controle c
            INNER JOIN sth_controle_nao_conformidade cnc ON cnc.id_controle = c.id_controle
            WHERE cnc.id_controle = $row[id_controle]";

            $resultado_nao_conformidade  = conecta_query($conexao, $query_nao_conformidade);
            $quantidade_nao_conformidade = pg_fetch_row($resultado_nao_conformidade);

            $nao_conformidade = null;

            if($quantidade_nao_conformidade > 0){
                $nao_conformidade = "sim";
            }
            
            $cont++;
            $pdf->Cell(10, 6, $cont, 1, 0, 'C');
            $pdf->Cell(16, 6, iconv('utf-8', 'iso-8859-1', date('d/m/Y', strtotime($row['data_transfusao']))), 1, 0, 'C');
            $pdf->Cell(12, 6, iconv('utf-8', 'iso-8859-1', $row['horario_inicio']), 1, 0, 'C');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', $row['num_sus']), 1, 0, 'C');
            $pdf->Cell(45, 6, iconv('utf-8', 'iso-8859-1', $nome_paciente), 1, 0, 'L');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', date('d/m/Y', strtotime($row['dt_nasc']))), 1, 0, 'C');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', $row['abo']), 1, 0, 'C');
            $pdf->Cell(18, 6, iconv('utf-8', 'iso-8859-1', $row['rh_d']), 1, 0, 'C');
            $pdf->Cell(13, 6, iconv('utf-8', 'iso-8859-1', $row['prontuario']), 1, 0, 'C');
            $pdf->Cell(16, 6, iconv('utf-8', 'iso-8859-1', $row['numero_rt']), 1, 0, 'C');
            $pdf->Cell(6, 6, iconv('utf-8', 'iso-8859-1', $fit), 1, 0, 'C');
            $pdf->Cell(6, 6, iconv('utf-8', 'iso-8859-1', $nao_conformidade), 1, 1, 'C');
        }
    }   

    // Cria um novo objeto FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P'); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query = "SELECT dp.nome_completo, dp.id_paciente, dp.nome_social, dp.dt_nasc, dp.abo, dp.rh_d, dp.cpf, dp.prontuario, 
    cb.data_transfusao, cb.horario_inicio, cb.num_bolsa, cb.id_bolsa, cb.num_sus, c.id_controle, dp.numero_rt,
    CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome
    FROM sth_controle c
    INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa
    INNER JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente";

    // Verifique se as datas estão preenchidas e tem formato válido
    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {

        // Adicione a condição do intervalo de datas à consulta
        $query .= " WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";

    } else {
        // Se as datas não estiverem preenchidas ou não tiverem formato válido, remova a parte WHERE da consulta
        $query .= " WHERE 1=1";
    }

    //se setor estiver preenchido
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

    //se bolsa estiver preenchido
    if (!empty($bolsa)) {
        $query_numero_bolsa     = "SELECT num_bolsa FROM sth_cadastro_bolsa WHERE id_bolsa = $bolsa";
        $resultado_numero_bolsa = conecta_query($conexao, $query_numero_bolsa);
        $row_numero_bolsa       = pg_fetch_assoc($resultado_numero_bolsa);
        $query_bolsa           .= " AND cb.num_bolsa = '$row_numero_bolsa[num_bolsa]'";
        pg_free_result($resultado_numero_bolsa);
    }

    $query .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";

    // echo $query;
    // Execute a consulta
    $resultado_consulta     = conecta_query($conexao, $query);
    $result_qtd = pg_num_rows($resultado_consulta);

    if($result_qtd > 0){
        
        // Adicione a imagem ao lado do título
        $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

        // Construa o cabeçalho do relatório PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(230,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE PACIENTES TRANSFUNDIDOS HUM'),0,1,'C');

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

        if (!empty($bolsa)) {
            $pdf->Cell(280, 10, iconv('utf-8', 'iso-8859-1', "Bolsa selecionada: $row_numero_bolsa[num_bolsa]"), 0, 1, '');
        }

        // Construa o conteúdo do PDF
        ConstruirTablePDFPaciente($pdf, $resultado_consulta, $conexao);
        pg_free_result($resultado_consulta);

        // Saí­da do PDF para o navegador
        $pdf->Output('relatorio_pacientes_transfundidos.pdf', 'D');
    }else{
        //mostra mensagem se o relatorio estiver vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location:relatorio.php");
    }