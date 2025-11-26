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

    function ConstruirTablePDFPaciente($pdf, $resultado_consulta, $conexao) {

        $pdf->SetFont('Arial', '', 9); // Definindo a fonte e o tamanho
        $pdf->SetFillColor(200); // Define a cor cinza 
    
        // Cabeçalho da tabela
        $pdf->Cell(15, 10, iconv('utf-8', 'iso-8859-1', 'N°'), 1, 0, 'C', true);
        $pdf->Cell(20, 10, iconv('utf-8', 'iso-8859-1', 'Dt transfusão'), 1, 0, 'C', true);
        $pdf->Cell(15, 10, iconv('utf-8', 'iso-8859-1', 'Horário'), 1, 0, 'C', true);
        $pdf->Cell(27, 10, iconv('utf-8', 'iso-8859-1', 'Bolsa'), 1, 0, 'C', true);
        $pdf->Cell(75, 10, iconv('utf-8', 'iso-8859-1', 'Paciente'), 1, 0, 'C', true);
        $pdf->Cell(22, 10, iconv('utf-8', 'iso-8859-1', 'ABO'), 1, 0, 'C', true);
        $pdf->Cell(22, 10, iconv('utf-8', 'iso-8859-1', 'Rh'), 1, 0, 'C', true);
        $pdf->Cell(20, 10, iconv('utf-8', 'iso-8859-1', 'Prontuário'), 1, 0, 'C', true);
        $pdf->Cell(8, 10, iconv('utf-8', 'iso-8859-1', 'FIT'), 1, 0, 'C', true);
        $pdf->Cell(60, 10, iconv('utf-8', 'iso-8859-1', 'Funcionário'), 1, 1, 'C', true);
    
        // Processar os resultados
        $cont = 0;

        while ($row = pg_fetch_assoc($resultado_consulta)) {

            // se tiver nome social preenchido uso o social, se nao, uso o do documento
            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
            $nome_paciente = (strlen($nome_paciente) > 35) ? substr($nome_paciente, 0, 35) . "..." : $nome_paciente;

            $query_paciente_reacao = "SELECT rt.id_transfusionais
            FROM sth_reacoes_transfusionais rt
            INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = rt.id_bolsa
            WHERE cb.id_paciente = $row[id_paciente] AND rt.id_bolsa = $row[id_bolsa] 
            LIMIT 5"; //limita 5 só para ver se tem

            $resultado_paciente_reacao = conecta_query($conexao, $query_paciente_reacao);
            $quantidade_reacao         = pg_num_rows($resultado_paciente_reacao);

            $fit = null;

            if($quantidade_reacao > 0){
                $fit = "sim";
            }
            
            $cont++;
            $pdf->Cell(15, 8, $cont, 1, 0, 'C');
            $pdf->Cell(20, 8, iconv('utf-8', 'iso-8859-1', date('d/m/Y', strtotime($row['data_transfusao']))), 1, 0, 'C');
            $pdf->Cell(15, 8, iconv('utf-8', 'iso-8859-1', $row['horario_inicio']), 1, 0, 'C');
            $pdf->Cell(27, 8, iconv('utf-8', 'iso-8859-1', $row["num_bolsa"]), 1, 0, 'C');
            $pdf->Cell(75, 8, iconv('utf-8', 'iso-8859-1', $nome_paciente), 1, 0, 'L');
            $pdf->Cell(22, 8, iconv('utf-8', 'iso-8859-1', $row['abo']), 1, 0, 'C');
            $pdf->Cell(22, 8, iconv('utf-8', 'iso-8859-1', $row['rh_d']), 1, 0, 'C');
            $pdf->Cell(20, 8, iconv('utf-8', 'iso-8859-1', $row['prontuario']), 1, 0, 'C');
            $pdf->Cell(8, 8, iconv('utf-8', 'iso-8859-1', $fit), 1, 0, 'C');
            $pdf->Cell(60, 8, '', 1, 1, 'C');

            $query_nao_conformidades = "SELECT nc.* 
            FROM sth_controle_nao_conformidade cnc
            INNER JOIN sth_nao_conformidade nc ON nc.id_nao_conformidade = cnc.id_nao_conformidade
            where cnc.id_controle = $row[id_controle]";

            $resultado_nao_conformidades  = conecta_query($conexao, $query_nao_conformidades);
            $quantidade_nao_conformidades = pg_num_rows($resultado_nao_conformidades);
            
            if($quantidade_nao_conformidades > 0){

                $descricao = '';
                $counter = 1;

                // Volta o ponteiro para o início, pois pg_num_rows pode avançá-lo
                pg_result_seek($resultado_nao_conformidades, 0);

                while($row_descricao = pg_fetch_assoc($resultado_nao_conformidades)){
                    $descricao .= "$counter. $row_descricao[tipo] - $row_descricao[nao_conformidade] \n"; 
                    $counter++;
                }

                if(!empty($row["observacao"])){
                    $descricao .= "Observação: $row[observacao] \n"; 
                }

                $pdf->multiCell(284, 10, iconv('utf-8', 'iso-8859-1', $descricao), 1, 'L');
            }
        }
    }   

    // Cria um novo objeto FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query = "SELECT dp.nome_completo, dp.id_paciente, dp.nome_social, dp.cpf, dp.abo, dp.rh_d, dp.prontuario, 
    cb.data_transfusao, cb.horario_inicio, cb.num_bolsa, cb.id_bolsa, c.id_controle, c.observacao,
    CASE WHEN dp.nome_social IS NULL OR dp.nome_social = '' THEN dp.nome_completo ELSE dp.nome_social END AS nome
    FROM sth_controle c
    INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa
    INNER JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente
    WHERE c.id_controle IN (SELECT cnc.id_controle FROM sth_controle_nao_conformidade cnc INNER JOIN sth_controle c ON c.id_controle = cnc.id_controle)";

    // Verifique se as datas estão preenchidas e tem formato válido
    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {
        // Adicione a condição do intervalo de datas à consulta
        $query .= " AND cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
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

    $query .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";

    // Execute a consulta
    $resultado_consulta  = conecta_query($conexao, $query);
    $quantidade_consulta = pg_num_rows($resultado_consulta);

    if($quantidade_consulta > 0){

        // Adicione a imagem ao lado do título
        $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

        // Construa o cabeçalho do relatório PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE NÃO CONFORMIDADE HUM'),0,1,'C');

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
        ConstruirTablePDFPaciente($pdf, $resultado_consulta, $conexao);
        pg_free_result($resultado_consulta);

        // Saí­da do PDF para o navegador
        $pdf->Output('relatorio_nao_conformidade.pdf', 'D');
    }else{
        //mostra mensagem se o relatorio estiver vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location:relatorio.php");
    }
