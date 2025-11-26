<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Pega as variáveis do request para garantir que existam
    $data_inicio = $_REQUEST['data_inicio'] ?? '';
    $data_fim = $_REQUEST['data_fim'] ?? '';
    $id_setor = $_REQUEST['id_setor'] ?? '';

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
            $this->Cell(80, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');

            // Número da página
            $this->Cell(0, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Página ' . $this->PageNo() . ' / {nb}'), 0, 0, 'R');
        }
    }

    function ConstruirTablePDFPaciente($pdf, $resultado_consulta, $conexao) {

        $pdf->SetFont('Arial', '', 9); // Definindo a fonte e o tamanho
        $pdf->SetFillColor(200); // Define a cor cinza 
    
        // Cabeçalho da tabela
        $pdf->Cell(10, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'N°'), 1, 0, 'C', true);
        $pdf->Cell(20, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Prontuário'), 1, 0, 'C', true);
        $pdf->Cell(70, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Paciente'), 1, 0, 'C', true);
        $pdf->Cell(28, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Bolsa'), 1, 0, 'C', true);
        $pdf->Cell(150, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Não Conformidade'), 1, 1, 'C', true);
    
        // Processar os resultados
        $cont = 0;

        while ($row = pg_fetch_assoc($resultado_consulta)) {
            $cont++;

            // se tiver nome social preenchido uso o social, se nao, uso o do documento
            $nome_paciente = !empty($row['nome_social']) ? $row['nome_social'] : $row['nome_completo'];
            $nome_paciente = (strlen($nome_paciente) > 40) ? substr($nome_paciente, 0, 40) . "..." : $nome_paciente;

            $nao_conformidade = $row['tipo'] . ' - ' . $row['nao_conformidade'];
            $nao_conformidade = (strlen($nao_conformidade) > 85) ? substr($nao_conformidade, 0, 85) . "..." : $nao_conformidade;
            

            $pdf->Cell(10, 8, str_pad($cont, 3, '0', STR_PAD_LEFT), 1, 0, 'C');
            $pdf->Cell(20, 8, iconv('utf-8', 'iso-8859-1//IGNORE', $row['prontuario']), 1, 0, 'C');
            $pdf->Cell(70, 8, iconv('utf-8', 'iso-8859-1//IGNORE', $nome_paciente), 1, 0, 'L');
            $pdf->Cell(28, 8, iconv('utf-8', 'iso-8859-1//IGNORE', $row['num_bolsa']), 1, 0, 'L');
            $pdf->Cell(150, 8, iconv('utf-8', 'iso-8859-1//IGNORE', $nao_conformidade), 1, 1, 'L');
        }
    }   

    // Cria um novo objeto FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('L'); // Adiciona uma página
    $pdf->SetDrawColor(150); // Define cor dos desenhos como retangulos, bordas...

    // Construa a parte da consulta SQL para o intervalo selecionado
    $query = "SELECT 
                dp.nome_completo, 
                dp.nome_social,
                dp.prontuario,
                cb.num_bolsa,
                nc.tipo,
                nc.nao_conformidade,
                cb.data_transfusao,
                cb.horario_inicio,
                CASE WHEN dp.nome_social IS NULL OR dp.nome_social = '' THEN dp.nome_completo ELSE dp.nome_social END AS nome
              FROM sth_controle_nao_conformidade cnc
              INNER JOIN sth_nao_conformidade nc ON nc.id_nao_conformidade = cnc.id_nao_conformidade
              INNER JOIN sth_controle c ON c.id_controle = cnc.id_controle
              INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa
              INNER JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente
              WHERE 1=1";

    // Verifique se as datas estão preenchidas e tem formato válido
    if (!empty($data_inicio) && !empty($data_fim)) {
        // Garante que a data esteja no formato Y-m-d para a consulta SQL
        $data_inicio_sql = date('Y-m-d', strtotime(str_replace('/', '-', $data_inicio)));
        $data_fim_sql = date('Y-m-d', strtotime(str_replace('/', '-', $data_fim)));

        // Adicione a condição do intervalo de datas à consulta
        $query .= " AND cb.data_transfusao BETWEEN '$data_inicio_sql' AND '$data_fim_sql'";
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

    $query .= " ORDER BY nome, cb.data_transfusao, cb.horario_inicio";

    // Execute a consulta
    $resultado_consulta  = conecta_query($conexao, $query);
    $quantidade_consulta = pg_num_rows($resultado_consulta);

    if($quantidade_consulta > 0){

        // Define o caminho absoluto para a imagem
        $img_path = 'C:/xampp/htdocs/transfusional/img/hum_relatorio.png';

        // Adiciona uma verificação robusta antes de tentar usar a imagem
        if (is_readable($img_path)) {
            // Tenta obter o tamanho da imagem para garantir que é um arquivo de imagem válido
            if (@getimagesize($img_path) !== false) {
                $pdf->Image($img_path, 10, 10, 50); // Ajuste as coordenadas e o tamanho conforme necessário
            } else {
                // Opcional: Logar ou mostrar um erro se a imagem for inválida
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(0, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Erro: Arquivo de imagem corrompido ou inválido.'), 0, 1);
            }
        } else {
            // Opcional: Logar ou mostrar um erro se a imagem não for encontrada ou não for legível
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 10, iconv('utf-8', 'iso-8859-1//IGNORE', 'Erro: Imagem do relatório não encontrada ou sem permissão de leitura.'), 0, 1);
        }

        // Construa o cabeçalho do relatório PDF
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1//IGNORE', 'RELATÓRIO DE NÃO CONFORMIDADE HUM'),0,1,'C');

        $pdf->SetFont('Arial', '', 12);
        $pdf->ln();
        
        //formatando data para mostrar no relatório
        if (!empty($data_inicio) && !empty($data_fim)) {
            $data_inicio_formatada = date('d/m/Y', strtotime(str_replace('/', '-', $data_inicio)));
            $data_fim_formatada = date('d/m/Y', strtotime(str_replace('/', '-', $data_fim)));
            $intervalo_selecionado = "de $data_inicio_formatada até $data_fim_formatada";
        } else {
            $intervalo_selecionado = "Período não informado";
        }

        $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1//IGNORE', "Período selecionado: $intervalo_selecionado"),0,1,'');

        if (!empty($id_setor)) {

            // Consulta para obter o nome do setor com base no id_setor
            $query_setor     = "SELECT nome_setor FROM sth_setores WHERE id_setor = $id_setor";
            $resultado_setor = conecta_query($conexao, $query_setor);

            if ($row_setor = pg_fetch_assoc($resultado_setor)) {
                $nome_setor = $row_setor['nome_setor'];
                $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1//IGNORE', "Setor: $nome_setor"),0,1,"");
                
            } else {
                $pdf->Cell(280,10,iconv('utf-8', 'iso-8859-1//IGNORE', "Setor não encontrado"),0,1,"");
            }

            // Liberar resultados da consulta do setor
            pg_free_result($resultado_setor);
        }

        // Construa o conteúdo do PDF
        ConstruirTablePDFPaciente($pdf, $resultado_consulta, $conexao);
        pg_free_result($resultado_consulta);

        // Limpa qualquer saída anterior para evitar corrupção do PDF
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Saída do PDF para o navegador (inline - abre no navegador)
        $pdf->Output('I', 'relatorio_nao_conformidade.pdf');
    }else{
        //mostra mensagem se o relatorio estiver vazio
        $_SESSION['validado_relatorio_vazio'] = 0;
        header("Location:relatorio.php");
    }