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
        $pdf->Cell(27, 10, iconv('utf-8', 'iso-8859-1', 'Hemoc'), 1, 0, 'C', true);
        $pdf->Cell(27, 10, iconv('utf-8', 'iso-8859-1', 'SUS da bolsa'), 1, 0, 'C', true);
        $pdf->Cell(75, 10, iconv('utf-8', 'iso-8859-1', 'Paciente'), 1, 0, 'C', true);
        $pdf->Cell(20, 10, iconv('utf-8', 'iso-8859-1', 'Prontuário'), 1, 1, 'C', true);
    
        // Processar os resultados
        $cont = 0;

        while ($row = pg_fetch_assoc($resultado_consulta)) {
            $reacoes = "";

            // se tiver nome social preenchido uso o social, se nao, uso o do documento
            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
            $nome_paciente = (strlen($nome_paciente) > 35) ? substr($nome_paciente, 0, 35) . "..." : $nome_paciente;
            
            $cont++;
            $pdf->Cell(15, 8, $cont, 1, 0, 'C');
            $pdf->Cell(20, 8, iconv('utf-8', 'iso-8859-1', date('d/m/Y', strtotime($row['data_transfusao']))), 1, 0, 'C');
            $pdf->Cell(15, 8, iconv('utf-8', 'iso-8859-1', $row['horario_inicio']), 1, 0, 'C');
            $pdf->Cell(27, 8, iconv('utf-8', 'iso-8859-1', $row["num_bolsa"]), 1, 0, 'C');
            $pdf->Cell(27, 8, iconv('utf-8', 'iso-8859-1', $row["sigla"]), 1, 0, 'L');
            $pdf->Cell(27, 8, iconv('utf-8', 'iso-8859-1', $row["num_sus"]), 1, 0, 'C');
            $pdf->Cell(75, 8, iconv('utf-8', 'iso-8859-1', $nome_paciente), 1, 0, 'L');
            $pdf->Cell(20, 8, iconv('utf-8', 'iso-8859-1', $row['prontuario']), 1, 1, 'L');

            // Mostra reações transfusionais
            $query_reacao_tranfusional = "SELECT tp.descricao, tp.nome, rt.observacao 
            FROM sth_tipos_reacoes tp
            INNER JOIN sth_reacoes_transfusionais rt ON rt.tipo_reacao = tp.id_reacao
            WHERE rt.id_bolsa = $row[id_bolsa]";

            $resultado_reacao_transfuisional  = conecta_query($conexao, $query_reacao_tranfusional);
            $quantidade_reacao_transfuisional = conecta_query($conexao, $query_reacao_tranfusional);
            
            if(pg_fetch_row($quantidade_reacao_transfuisional) > 0){

                while($row_reacao_transfuisional = pg_fetch_assoc($resultado_reacao_transfuisional)){
                    $reacoes .= "$row_reacao_transfuisional[nome] - $row_reacao_transfuisional[descricao] \n"; 

                    if(!empty($row_descr["observacao"])){
                        $reacoes .= "Observação: $row_reacao_transfuisional[observacao] \n"; 
                    }
                }

                $pdf->multiCell(226, 10, iconv('utf-8', 'iso-8859-1', $reacoes), 1, 'L');
            }
        }
    }   

    // Cria um novo objeto FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query = "SELECT cb.num_bolsa, cb.num_sus, cb.id_bolsa, h.sigla, cb.data_transfusao, cb.horario_inicio, dp.prontuario, dp.nome_completo, 
    nome_social, CASE WHEN dp.nome_social IS NULL OR dp.nome_social = '' THEN dp.nome_completo ELSE dp.nome_social END AS nome
    FROM sth_cadastro_bolsa cb
    INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
    INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
    INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
    INNER JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa";

    // Verifique se as datas estão preenchidas e tem formato válido
    if (!empty($data_inicio) && !empty($data_fim) && 
    DateTime::createFromFormat('Y-m-d', $data_inicio) !== false && 
    DateTime::createFromFormat('Y-m-d', $data_fim) !== false) {
        // Adicione a condição do intervalo de datas à consulta
        $query .= " cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
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

    $query .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";

    // Execute a consulta
    $resultado_consulta  = conecta_query($conexao, $query);
    $quantidade_consulta = pg_num_rows($resultado_consulta);

    if($quantidade_consulta > 0){

        // Adicione a imagem ao lado do título
        $pdf->Image('img/hum_relatorio.png', 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário

        // Construa o cabeçalho do relatório PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1', 'RELATÓRIO DE REAÇÕES TRANSFUSIONAIS POR PACIENTE HUM'),0,1,'C');

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
        $pdf->Output('relatorio_reacao_por_paciente.pdf', 'D');
    }else{
        //mostra mensagem se o relatorio estiver vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location:relatorio.php");
        exit();
    }
