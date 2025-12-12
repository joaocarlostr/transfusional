<?php
// 1. DESABILITAR ERROS PARA NÃO CORROMPER PDF
error_reporting(0);
ini_set('display_errors', 0);

// 2. INCLUIR BIBLIOTECAS
require_once('libraries/fpdf.php');
include('database.php');

// Função helper para conversão segura de encoding
function iconv_safe($string) {
    if ($string === null || $string === '') return '';
    $result = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $string);
    return $result !== false ? $result : $string;
}

// 3. RECEBER DADOS DO POST
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'bolsa';
$data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : date('Y-m-d');
$data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : date('Y-m-d');
// Outros filtros
$id_setor = isset($_POST['id_setor']) ? $_POST['id_setor'] : '';
$bolsa_id = isset($_POST['bolsa']) ? $_POST['bolsa'] : '';
$prontuario_id = isset($_POST['prontuario']) ? $_POST['prontuario'] : '';

// Normalizar datas
if (strpos($data_inicio, '/') !== false) {
    $parts = explode('/', $data_inicio);
    if (count($parts) == 3) $data_inicio = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
}
if (strpos($data_fim, '/') !== false) {
    $parts = explode('/', $data_fim);
    if (count($parts) == 3) $data_fim = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
}

// 4. DEFINIR CLASSE PDF GENÉRICA
class PDF extends FPDF {
    protected $tituloRelatorio = 'RELATÓRIO';

    function setTitulo($titulo) {
        $this->tituloRelatorio = $titulo;
    }

    function Header() {
        if (file_exists('img/hum_relatorio.png')) {
            $this->Image('img/hum_relatorio.png', 10, 10, 50);
        }
        
        $this->SetFont('Arial', 'B', 14);
        $titulo = !empty($this->tituloRelatorio) ? $this->tituloRelatorio : 'RELATÓRIO';
        $this->Cell(0, 10, iconv_safe($titulo), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
        
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(100, 10, iconv_safe('Sistema do Serviço Transfusional do HUM'), 0, 0, 'L');
        $this->Cell(0, 10, iconv_safe('Página ') . $this->PageNo() . ' / {nb}', 0, 0, 'R');
    }
}

// 5. HELPER PARA CABEÇALHOS PADRÃO
function imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao) {
    $periodo_texto = "Período: " . date('d/m/Y', strtotime($data_inicio)) . " a " . date('d/m/Y', strtotime($data_fim));
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 8, iconv_safe($periodo_texto), 0, 1);

    if (!empty($id_setor)) {
        if ($id_setor == 'pa_geral') {
            $pdf->Cell(0, 6, iconv_safe("Filtro Setor: PA - GERAL"), 0, 1);
        } elseif (is_numeric($id_setor)) {
            $q_setor = "SELECT nome_setor FROM sth_setores WHERE id_setor = " . intval($id_setor);
            $res_setor = pg_query($conexao, $q_setor);
            if ($res_setor && $row_s = pg_fetch_assoc($res_setor)) {
                $pdf->Cell(0, 6, iconv_safe("Filtro Setor: " . $row_s['nome_setor']), 0, 1);
            }
        }
    }
    
    if (!empty($bolsa_id) && is_numeric($bolsa_id)) {
        $q_bolsa = "SELECT num_bolsa FROM sth_cadastro_bolsa WHERE id_bolsa = " . intval($bolsa_id);
        $res_bolsa = pg_query($conexao, $q_bolsa);
        if ($res_bolsa && $row_b = pg_fetch_assoc($res_bolsa)) {
            $pdf->Cell(0, 6, iconv_safe("Filtro Bolsa: " . $row_b['num_bolsa']), 0, 1);
        }
    }

    $pdf->Ln(3);
}

// 6. HELPER PARA NOMES DE MESES
function nomeia_mes_sigla($mes) {
    $meses = array(
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 
        9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
    );
    return isset($meses[$mes]) ? $meses[$mes] : '';
}

// 7. INICIALIZAR PDF
$pdf = new PDF();
$pdf->AliasNbPages();

// 7. ROTEAMENTO
switch ($tipo) {
    
    // --- CASO 1: BOLSAS TRANSFUNDIDAS ---
    case 'bolsa':
        $pdf->setTitulo('RELATÓRIO DE BOLSAS TRANSFUNDIDAS - HUM');
        $pdf->AddPage('L'); 
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT cb.num_bolsa, cb.num_sus, cb.data_transfusao, cb.horario_inicio, 
                dp.prontuario, dp.nome_completo, h.sigla
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
        
        if (!empty($bolsa_id) && is_numeric($bolsa_id)) $sql .= " AND cb.id_bolsa = $bolsa_id";
        if (!empty($prontuario_id) && is_numeric($prontuario_id)) $sql .= " AND cb.id_paciente = $prontuario_id";

        $sql .= " ORDER BY cb.data_transfusao";
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas e layout
        $w = array(10, 30, 25, 22, 18, 20, 90, 20);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Bolsa', 'SUS', 'Data', 'Horário', 'Prontuário', 'Paciente', 'Hemocomp.');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 7);
        $count = 0;
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            $pdf->Cell($w[0], 5, $count, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 5, $row['num_bolsa'], 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 5, $row['num_sus'], 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 5, date('d/m/Y', strtotime($row['data_transfusao'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 5, substr($row['horario_inicio'], 0, 5), 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 5, $row['prontuario'], 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 5, iconv_safe(substr($row['nome_completo'], 0, 50)), 1, 0, 'L', $fill);
            $pdf->Cell($w[7], 5, iconv_safe($row['sigla']), 1, 1, 'C', $fill);
        }
        break;

    // --- CASO 2: PACIENTES TRANSFUNDIDOS ---
    case 'paciente':
        $pdf->setTitulo('RELATÓRIO DE PACIENTES TRANSFUNDIDOS - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT dp.nome_completo, dp.id_paciente, dp.nome_social, dp.dt_nasc, dp.abo, dp.rh_d, dp.cpf, dp.prontuario, 
                cb.data_transfusao, cb.horario_inicio, cb.num_bolsa, cb.id_bolsa, cb.num_sus, c.id_controle, dp.numero_rt,
                CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome
                FROM sth_controle c
                INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa
                INNER JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente
                WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
        
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND c.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND c.id_setor = $id_setor";
            }
        }
        if (!empty($bolsa_id) && is_numeric($bolsa_id)) $sql .= " AND cb.id_bolsa = $bolsa_id";
        if (!empty($prontuario_id) && is_numeric($prontuario_id)) $sql .= " AND cb.id_paciente = $prontuario_id";
        
        $sql .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";
        $resultado = pg_query($conexao, $sql);

        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }
        
        $w = array(10, 18, 12, 20, 90, 18, 10, 10, 15, 18, 8, 8);
        $header = array('Nº', 'Dt Transf', 'Hora', 'SUS Bolsa', 'Paciente', 'Nascto', 'ABO', 'Rh', 'Pront.', 'Nº RT', 'FIT', 'NC');
        
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 7);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            // Subqueries
            $fit_val = ''; $nc_val = '';
            
            $q_fit = "SELECT rt.id_transfusionais FROM sth_reacoes_transfusionais rt 
                      INNER JOIN sth_cadastro_bolsa cb ON cb.id_bolsa = rt.id_bolsa
                      WHERE cb.id_paciente = {$row['id_paciente']} AND rt.id_bolsa = {$row['id_bolsa']} LIMIT 1";
            $r_fit = pg_query($conexao, $q_fit);
            if(pg_num_rows($r_fit) > 0) $fit_val = 'Sim';
            
            $q_nc = "SELECT c.id_controle FROM sth_controle c
                     INNER JOIN sth_controle_nao_conformidade cnc ON cnc.id_controle = c.id_controle
                     WHERE c.id_controle = {$row['id_controle']} LIMIT 1";
            $r_nc = pg_query($conexao, $q_nc);
            if(pg_num_rows($r_nc) > 0) $nc_val = 'Sim';

            $pdf->Cell($w[0], 5, $count, 1, 0, 'C', $fill); 
            $pdf->Cell($w[1], 5, date('d/m/Y', strtotime($row['data_transfusao'])), 1, 0, 'C', $fill); 
            $pdf->Cell($w[2], 5, substr($row['horario_inicio'], 0, 5), 1, 0, 'C', $fill); 
            $pdf->Cell($w[3], 5, $row['num_sus'], 1, 0, 'C', $fill); 
            $pdf->Cell($w[4], 5, iconv_safe(substr($row['nome'], 0, 55)), 1, 0, 'L', $fill); 
            $pdf->Cell($w[5], 5, date('d/m/Y', strtotime($row['dt_nasc'])), 1, 0, 'C', $fill); 
            $pdf->Cell($w[6], 5, iconv_safe($row['abo']), 1, 0, 'C', $fill); 
            $pdf->Cell($w[7], 5, iconv_safe($row['rh_d']), 1, 0, 'C', $fill); 
            $pdf->Cell($w[8], 5, $row['prontuario'], 1, 0, 'C', $fill); 
            $pdf->Cell($w[9], 5, $row['numero_rt'], 1, 0, 'C', $fill); 
            $pdf->Cell($w[10], 5, iconv_safe($fit_val), 1, 0, 'C', $fill); 
            $pdf->Cell($w[11], 5, iconv_safe($nc_val), 1, 1, 'C', $fill); 
        }
        break;

    // --- CASO 3: REAÇÕES POR PACIENTE ---
    case 'tipo_reacao_paciente':
        $pdf->setTitulo('RELATÓRIO DE REAÇÕES TRANSFUSIONAIS POR PACIENTE');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT DISTINCT cb.id_bolsa, cb.num_bolsa, cb.num_sus, cb.data_transfusao, cb.horario_inicio, 
                dp.prontuario, dp.nome_completo, dp.nome_social, h.sigla,
                CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                INNER JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa
                WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
        
        if (!empty($bolsa_id) && is_numeric($bolsa_id)) $sql .= " AND cb.id_bolsa = $bolsa_id";
        if (!empty($prontuario_id) && is_numeric($prontuario_id)) $sql .= " AND cb.id_paciente = $prontuario_id";
        
        $sql .= " ORDER BY cb.data_transfusao, cb.horario_inicio";
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }
        
        $w = array(15, 20, 15, 27, 27, 27, 75, 20);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Dt Transf', 'Horário', 'Bolsa', 'Hemoc', 'SUS Bolsa', 'Paciente', 'Prontuário');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 8, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 8);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetX($margem_esquerda);
            
            $pdf->Cell($w[0], 8, $count, 1, 0, 'C');
            $pdf->Cell($w[1], 8, date('d/m/Y', strtotime($row['data_transfusao'])), 1, 0, 'C');
            $pdf->Cell($w[2], 8, substr($row['horario_inicio'], 0, 5), 1, 0, 'C');
            $pdf->Cell($w[3], 8, $row['num_bolsa'], 1, 0, 'C');
            $pdf->Cell($w[4], 8, iconv_safe($row['sigla']), 1, 0, 'L');
            $pdf->Cell($w[5], 8, $row['num_sus'], 1, 0, 'C');
            $pdf->Cell($w[6], 8, iconv_safe(substr($row['nome'], 0, 40)), 1, 0, 'L');
            $pdf->Cell($w[7], 8, $row['prontuario'], 1, 1, 'L');

            // Detalhes da Reação
            $q_det = "SELECT tp.nome, tp.descricao, rt.observacao 
                      FROM sth_tipos_reacoes tp
                      INNER JOIN sth_reacoes_transfusionais rt ON rt.tipo_reacao = tp.id_reacao
                      WHERE rt.id_bolsa = {$row['id_bolsa']}";
            $res_det = pg_query($conexao, $q_det);
            
            if (pg_num_rows($res_det) > 0) {
                $texto_reacao = "";
                while($rd = pg_fetch_assoc($res_det)) {
                    $texto_reacao .= "  - " . $rd['nome'] . " (" . $rd['descricao'] . ")";
                    if(!empty($rd['observacao'])) $texto_reacao .= " obs: " . $rd['observacao'];
                    $texto_reacao .= "\n";
                }
                
                $pdf->SetX($margem_esquerda);
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetFillColor(245, 245, 240);
                $pdf->MultiCell(array_sum($w), 6, iconv_safe(trim($texto_reacao)), 1, 'L', true);
                
                $pdf->SetFont('Arial', '', 8);
            }
        }
        break;

    // --- CASO 4: BOLSAS NÃO TRANSFUNDIDAS (DEVOLVIDAS) ---
    case 'bolsa_devolvida':
        $pdf->setTitulo('RELATÓRIO DE BOLSAS NÃO TRANSFUNDIDAS - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT cb.num_bolsa, cb.num_sus, bd.observacao, h.sigla, bd.dt_devolucao, bd.motivo
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                INNER JOIN sth_bolsas_devolvidas bd ON bd.id_bolsa = cb.id_bolsa
                WHERE bd.dt_devolucao BETWEEN '$data_inicio' AND '$data_fim'";
        
        $sql .= " ORDER BY bd.dt_devolucao";
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: N°(10), Data(20), Bolsa(22), SUS(20), Hemoc(30), Motivo(40), Obs(53). Total: 195
        $w = array(10, 20, 22, 20, 30, 40, 53);
        // Centralizar
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Dt Devolução', 'Bolsa', 'Nº SUS', 'Hemoc / Atributos', 'Motivo', 'Observação');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            $obs_curta = (strlen($row['observacao']) > 37) ? substr($row['observacao'], 0, 37) . "..." : $row['observacao'];

            $pdf->Cell($w[0], 6, $count, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, date('d/m/Y', strtotime($row['dt_devolucao'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 6, $row['num_bolsa'], 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 6, $row['num_sus'], 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 6, iconv_safe($row['sigla']), 1, 0, 'L', $fill);
            $pdf->Cell($w[5], 6, iconv_safe($row['motivo']), 1, 0, 'L', $fill);
            $pdf->Cell($w[6], 6, iconv_safe($obs_curta), 1, 1, 'L', $fill);
        }
        break;

    // --- CASO 5: BOLSAS RESERVAS ---
    case 'bolsa_reserva':
        $pdf->setTitulo('RELATÓRIO DE BOLSAS RESERVAS - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT cb.num_bolsa, cb.num_sus, h.sigla, cb.data_transfusao, cb.horario_inicio, 
                dp.nome_completo, dp.nome_social,
                CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim' AND reserva = 'sim'";
        
        // Filtro Setor (usando coluna id_livro_setor conforme original)
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND cb.id_livro_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND cb.id_livro_setor = $id_setor";
            }
        }
        
        if (!empty($bolsa_id) && is_numeric($bolsa_id)) {
            $sql .= " AND cb.num_bolsa = (SELECT num_bolsa FROM sth_cadastro_bolsa WHERE id_bolsa = $bolsa_id)";
        }
        
        $sql .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: N°(10), Dt transfusão(20), Horário(15), Paciente(70), Bolsa(20), SUS(20), Hemocomp(35). Total 190
        $w = array(10, 20, 15, 70, 20, 20, 35);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Dt Transfusão', 'Horário', 'Paciente', 'Bolsa', 'Nº SUS', 'Hemocomp / Atributos');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            $nome_paciente = (strlen($row['nome']) > 38) ? substr($row['nome'], 0, 38) . "..." : $row['nome'];

            $pdf->Cell($w[0], 6, $count, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, date('d/m/Y', strtotime($row['data_transfusao'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 6, $row['horario_inicio'], 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 6, iconv_safe($nome_paciente), 1, 0, 'L', $fill);
            $pdf->Cell($w[4], 6, $row['num_bolsa'], 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 6, $row['num_sus'], 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 6, iconv_safe($row['sigla']), 1, 1, 'L', $fill);
        }
        break;

    // --- CASO 6: BOLSAS REPETIDAS ---
    case 'bolsa_repetida':
        $pdf->setTitulo('RELATÓRIO DE BOLSAS REPETIDAS - HUM');
        $pdf->AddPage('P');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT cb.num_bolsa, cb.num_sus, cb.data_transfusao, cb.horario_inicio, 
                dp.nome_completo, dp.nome_social, h.sigla, c.dt_busca_ativa, cb.notvisa, cb.shtnovo,
                CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
                WHERE cb.num_bolsa IN (SELECT num_bolsa FROM sth_cadastro_bolsa GROUP BY num_bolsa HAVING count(*) > 1)";
        
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND c.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND c.id_setor = $id_setor";
            }
        }
        
        if (!empty($bolsa_id) && is_numeric($bolsa_id)) {
            $q_num_bolsa = "SELECT num_bolsa FROM sth_cadastro_bolsa WHERE id_bolsa = $bolsa_id";
            $r_num_bolsa = pg_query($conexao, $q_num_bolsa);
            if ($r_num_bolsa && $row_nb = pg_fetch_assoc($r_num_bolsa)) {
                $sql .= " AND cb.num_bolsa = '{$row_nb['num_bolsa']}'";
            }
        }
        
        if (!empty($prontuario_id) && is_numeric($prontuario_id)) {
            $sql .= " AND cb.id_paciente = $prontuario_id";
        }
        
        $sql .= " ORDER BY cb.num_bolsa, cb.num_sus";
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: N°(10), Dt(20), Hora(15), Paciente(50), Bolsa(20), SUS(20), Hemoc(30), Notivisa(10), Shtnovo(10), HEM(10). Total: 195
        $w = array(10, 20, 15, 50, 20, 20, 30, 10, 10, 10);
        $margem_esquerda = (210 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Dt Transfusão', 'Horário', 'Paciente', 'Bolsa', 'Nº SUS', 'Hemoc / Atributos', 'Notivisa', 'Shtnovo', 'HEM');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            $nome_paciente = (strlen($row['nome']) > 38) ? substr($row['nome'], 0, 38) . "..." : $row['nome'];

            $pdf->Cell($w[0], 6, $count, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, date('d/m/Y', strtotime($row['data_transfusao'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 6, $row['horario_inicio'], 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 6, iconv_safe($nome_paciente), 1, 0, 'L', $fill);
            $pdf->Cell($w[4], 6, $row['num_bolsa'], 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 6, $row['num_sus'], 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 6, iconv_safe($row['sigla']), 1, 0, 'L', $fill);
            $pdf->Cell($w[7], 6, iconv_safe($row['notvisa']), 1, 0, 'C', $fill);
            $pdf->Cell($w[8], 6, iconv_safe($row['shtnovo']), 1, 0, 'C', $fill);
            $pdf->Cell($w[9], 6, " ", 1, 1, 'C', $fill);
        }
        break;

    // --- CASO 7: PACIENTES SEM REGISTRO ---
    case 'paciente_sem_registro':
        $pdf->setTitulo('RELATÓRIO DE PACIENTES SEM NÚMERO DE REGISTRO - HUM');
        $pdf->AddPage('P');
        
        // Cabeçalho simplificado (sem filtros de data/setor pois não são usados neste relatório)
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 8, iconv_safe("Listagem de pacientes sem número de registro"), 0, 1);
        $pdf->Ln(3);
        
        $sql = "SELECT nome_completo, id_paciente, nome_social, dt_nasc, abo, rh_d, cpf, prontuario, 
                CASE WHEN nome_social IS NULL OR nome_social = '' THEN nome_completo ELSE nome_social END AS nome
                FROM sth_dados_paciente 
                WHERE registro IS NULL OR registro = '' 
                ORDER BY nome";
        
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: N°(10), Paciente(50), Dt nasc(18), ABO(18), Rh(18), Prontuário(13), Registro(23). Total: 150
        $w = array(10, 50, 18, 18, 18, 13, 23);
        $margem_esquerda = (210 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Paciente', 'Dt Nascimento', 'ABO', 'Rh', 'Prontuário', 'Registro');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 7);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            $nome_paciente = (strlen($row['nome']) > 29) ? substr($row['nome'], 0, 29) . "..." : $row['nome'];

            $pdf->Cell($w[0], 6, $count, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, iconv_safe($nome_paciente), 1, 0, 'L', $fill);
            $pdf->Cell($w[2], 6, date('d/m/Y', strtotime($row['dt_nasc'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 6, iconv_safe($row['abo']), 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 6, iconv_safe($row['rh_d']), 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 6, $row['prontuario'], 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 6, "", 1, 1, 'C', $fill);
        }
        break;

    // --- CASO 8: TIPO SANGUÍNEO ---
    case 'tipo_sanguineo':
        $pdf->setTitulo('RELATÓRIO TIPO SANGUÍNEO DOS PACIENTES TRANSFUNDIDOS - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        // Função para construir a matriz ABO x Rh
        $tipos_rh = ["Positivo", "Negativo", "Outro", "Desconhecido"];
        $tipos_abo = ['A', 'B', 'AB', 'O', 'Outro', 'Desconhecido'];
        
        // Cabeçalho da tabela
        $w_label = 70;
        $w_cell = 25;
        $margem_esquerda = (297 - ($w_label + (count($tipos_abo) * $w_cell))) / 2;
        $pdf->SetX($margem_esquerda);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell($w_label, 10, '', 1, 0, 'C', true);
        foreach ($tipos_abo as $abo) {
            $pdf->Cell($w_cell, 10, iconv_safe($abo), 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        $pdf->SetFont('Arial', '', 10);
        foreach ($tipos_rh as $rh) {
            $pdf->SetX($margem_esquerda);
            $pdf->Cell($w_label, 10, iconv_safe($rh), 1, 0, 'C');
            
            foreach ($tipos_abo as $abo) {
                $sql_count = "SELECT count(dp.abo) AS qtd
                              FROM sth_dados_paciente dp
                              WHERE dp.id_paciente IN (
                                  SELECT id_paciente FROM sth_cadastro_bolsa WHERE id_bolsa IN (
                                      SELECT c.id_bolsa FROM sth_controle c 
                                      INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa
                                      WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'
                                  )
                              )
                              AND rh_d = '$rh' AND abo = '$abo'
                              GROUP BY abo, rh_d ORDER BY abo";
                
                $res_count = pg_query($conexao, $sql_count);
                $qtd = 0;
                if ($res_count && $row_count = pg_fetch_assoc($res_count)) {
                    $qtd = $row_count['qtd'];
                }
                
                $pdf->Cell($w_cell, 10, $qtd, 1, 0, 'C');
            }
            $pdf->Ln();
        }
        break;

    // --- CASO 9: SETORES ---
    case 'tipo_setor':
        $pdf->setTitulo('RELATÓRIO DE QUANTIDADE DE BOLSAS TRANSFUNDIDAS POR SETOR - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT s.nome_setor, 
                COUNT(DISTINCT c.id_bolsa) AS qtd_bolsa, 
                COUNT(DISTINCT rt.id_transfusionais) AS qtd_rt,
                COUNT(DISTINCT cnc.id_controle_nao_conformidade) AS qtd_nao_conformidade
                FROM sth_setores s 
                LEFT JOIN sth_controle c ON s.id_setor = c.id_setor
                LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = c.id_bolsa
                LEFT JOIN sth_controle_nao_conformidade cnc ON cnc.id_controle = c.id_controle
                WHERE c.dt_busca_ativa BETWEEN '$data_inicio' AND '$data_fim'";
        
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND c.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND c.id_setor = $id_setor";
            }
        }
        
        $sql .= " GROUP BY s.nome_setor, s.status
                  HAVING COUNT(DISTINCT c.id_setor) > 0 OR s.status = 'ativo'
                  ORDER BY s.nome_setor";
        
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: Setores(100), Transfusões(40), FIT(40), Não conformidade(40). Total: 220
        $w = array(100, 40, 40, 40);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Setores', 'Transfusões', 'FIT', 'Não conformidade');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 10, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);
        
        while ($row = pg_fetch_assoc($resultado)) {
            $pdf->SetX($margem_esquerda);
            $pdf->Cell($w[0], 8, iconv_safe($row['nome_setor']), 1, 0, 'L');
            $pdf->Cell($w[1], 8, $row['qtd_bolsa'], 1, 0, 'C');
            $pdf->Cell($w[2], 8, $row['qtd_rt'], 1, 0, 'C');
            $pdf->Cell($w[3], 8, $row['qtd_nao_conformidade'], 1, 1, 'C');
        }
        break;

    // --- CASO 10: NÃO CONFORMIDADE ---
    case 'nao_conformidade':
        $pdf->setTitulo('RELATÓRIO DE NÃO CONFORMIDADES - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT 
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
              WHERE cb.data_transfusao BETWEEN '$data_inicio' AND '$data_fim'";
        
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND c.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND c.id_setor = $id_setor";
            }
        }
        
        $sql .= " ORDER BY nome, cb.data_transfusao, cb.horario_inicio";
        $resultado = pg_query($conexao, $sql);
        
        // CHECK SEM DADOS
        if (pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: N°(10), Prontuário(20), Paciente(70), Bolsa(28), Não Conformidade(150). Total: 278
        $w = array(10, 20, 70, 28, 150);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Prontuário', 'Paciente', 'Bolsa', 'Não Conformidade');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 10, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $pdf->SetX($margem_esquerda);
            
            $nome_paciente = (strlen($row['nome']) > 40) ? substr($row['nome'], 0, 40) . "..." : $row['nome'];
            $nao_conformidade = $row['tipo'] . ' - ' . $row['nao_conformidade'];
            $nao_conformidade = (strlen($nao_conformidade) > 85) ? substr($nao_conformidade, 0, 85) . "..." : $nao_conformidade;

            $pdf->Cell($w[0], 8, str_pad($count, 3, '0', STR_PAD_LEFT), 1, 0, 'C');
            $pdf->Cell($w[1], 8, $row['prontuario'], 1, 0, 'C');
            $pdf->Cell($w[2], 8, iconv_safe($nome_paciente), 1, 0, 'L');
            $pdf->Cell($w[3], 8, $row['num_bolsa'], 1, 0, 'L');
            $pdf->Cell($w[4], 8, iconv_safe($nao_conformidade), 1, 1, 'L');
        }
        break;

    // --- CASO 11: INDICADOR DE NÃO CONFORMIDADE ---
    case 'indi_nao_conformidade':
        $pdf->setTitulo('INDICADOR DE NÃO CONFORMIDADES - HUM');
        $pdf->AddPage('L');
        
        // Extrair ano das datas
        $ano = date('Y', strtotime($data_inicio));
        
        // Cabeçalho do indicador
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(35, 10, 'OBJETIVO', 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('Monitorar a porcentagem de não conformidades perante o total de transfusões realizadas'), 1, 1, 'C');
        
        $pdf->Cell(35, 10, 'Periodicidade', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Mensal', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Unidade de medida', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Porcentagem', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Meta', 1, 0, 'C', true);
        $pdf->Cell(29, 10, '<= 0,30%', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Origem dos dados', 1, 0, 'C', true);
        $pdf->Cell(45, 10, 'FIT/Sistema transfusional', 1, 1, 'C');
        
        $pdf->Cell(35, 10, iconv_safe('Fórmula'), 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('(Nº de não conformidades / Nº de transfusões) X 100'), 1, 1, 'C');
        $pdf->Ln();
        
        // Tabela mensal
        $pdf->Cell(70, 10, 'MESES', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, nomeia_mes_sigla($m), 1, 0, 'C', true);
        $pdf->Cell(22, 10, 'TOTAL', 1, 1, 'C', true);
        
        // Linha 1: Não conformidades
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(70, 10, iconv_safe('Nº de não conformidades'), 1, 0, 'C', true);
        $total_nc = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_nc = "SELECT COUNT(*) as qtd FROM sth_controle_nao_conformidade cnc
                       INNER JOIN sth_controle c ON c.id_controle = cnc.id_controle
                       WHERE EXTRACT(MONTH FROM c.dt_busca_ativa) = $m AND EXTRACT(YEAR FROM c.dt_busca_ativa) = $ano";
            $res_nc = pg_query($conexao, $sql_nc);
            $qtd_nc = ($res_nc && $row = pg_fetch_assoc($res_nc)) ? $row['qtd'] : 0;
            $total_nc += $qtd_nc;
            $pdf->Cell(15, 10, $qtd_nc, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_nc, 1, 1, 'C');
        
        // Linha 2: Transfusões
        $pdf->Cell(70, 10, iconv_safe('Nº de transfusões'), 1, 0, 'C', true);
        $total_transf = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_transf = "SELECT COUNT(DISTINCT cb.id_bolsa) as qtd FROM sth_cadastro_bolsa cb
                           WHERE EXTRACT(MONTH FROM cb.data_transfusao) = $m AND EXTRACT(YEAR FROM cb.data_transfusao) = $ano";
            $res_transf = pg_query($conexao, $sql_transf);
            $qtd_transf = ($res_transf && $row = pg_fetch_assoc($res_transf)) ? $row['qtd'] : 0;
            $total_transf += $qtd_transf;
            $pdf->Cell(15, 10, $qtd_transf, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_transf, 1, 1, 'C');
        
        // Linha 3: Porcentagem
        $pdf->Cell(70, 10, 'Porcentagem (%)', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) {
            $pdf->Cell(15, 10, '-', 1, 0, 'C');
        }
        $perc_total = $total_transf > 0 ? number_format(($total_nc / $total_transf) * 100, 2, ',', '') . '%' : '0,00%';
        $pdf->Cell(22, 10, $perc_total, 1, 1, 'C');
        break;

    // --- CASO 12: INDICADOR DE REAÇÃO TRANSFUSIONAL ---
    case 'reacao_transfusional':
        $pdf->setTitulo('INDICADOR DE REAÇÕES TRANSFUSIONAIS - HUM');
        $pdf->AddPage('L');
        
        $ano = date('Y', strtotime($data_inicio));
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(35, 10, 'OBJETIVO', 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('Monitorar a porcentagem de reações transfusionais perante o total de transfusões realizadas'), 1, 1, 'C');
        
        $pdf->Cell(35, 10, 'Periodicidade', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Mensal', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Unidade de medida', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Porcentagem', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Meta', 1, 0, 'C', true);
        $pdf->Cell(29, 10, '<= 0,30%', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Origem dos dados', 1, 0, 'C', true);
        $pdf->Cell(45, 10, 'FIT/Sistema transfusional', 1, 1, 'C');
        
        $pdf->Cell(35, 10, iconv_safe('Fórmula'), 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('(Nº de reações transfusionais / Nº de transfusões) X 100'), 1, 1, 'C');
        $pdf->Ln();
        
        $pdf->Cell(70, 10, 'MESES', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, nomeia_mes_sigla($m), 1, 0, 'C', true);
        $pdf->Cell(22, 10, 'TOTAL', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(70, 10, iconv_safe('Nº de reações transfusionais'), 1, 0, 'C', true);
        $total_reac = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_reac = "SELECT COUNT(*) as qtd FROM sth_reacoes_transfusionais rt
                         WHERE EXTRACT(MONTH FROM rt.data) = $m AND EXTRACT(YEAR FROM rt.data) = $ano";
            $res_reac = pg_query($conexao, $sql_reac);
            $qtd_reac = ($res_reac && $row = pg_fetch_assoc($res_reac)) ? $row['qtd'] : 0;
            $total_reac += $qtd_reac;
            $pdf->Cell(15, 10, $qtd_reac, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_reac, 1, 1, 'C');
        
        $pdf->Cell(70, 10, iconv_safe('Nº de transfusões'), 1, 0, 'C', true);
        $total_transf = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_transf = "SELECT COUNT(DISTINCT cb.id_bolsa) as qtd FROM sth_cadastro_bolsa cb
                           WHERE EXTRACT(MONTH FROM cb.data_transfusao) = $m AND EXTRACT(YEAR FROM cb.data_transfusao) = $ano";
            $res_transf = pg_query($conexao, $sql_transf);
            $qtd_transf = ($res_transf && $row = pg_fetch_assoc($res_transf)) ? $row['qtd'] : 0;
            $total_transf += $qtd_transf;
            $pdf->Cell(15, 10, $qtd_transf, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_transf, 1, 1, 'C');
        
        $pdf->Cell(70, 10, 'Porcentagem (%)', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, '-', 1, 0, 'C');
        $perc_total = $total_transf > 0 ? number_format(($total_reac / $total_transf) * 100, 2, ',', '') . '%' : '0,00%';
        $pdf->Cell(22, 10, $perc_total, 1, 1, 'C');
        break;

    // --- CASO 13: INDICADOR DE BOLSAS RESERVA ---
    case 'indi_bolsa_reserva':
        $pdf->setTitulo('INDICADOR DE BOLSAS RESERVA - HUM');
        $pdf->AddPage('L');
        
        $ano = date('Y', strtotime($data_inicio));
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(35, 10, 'OBJETIVO', 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('Monitorar a porcentagem de bolsas reserva perante o total de transfusões realizadas'), 1, 1, 'C');
        
        $pdf->Cell(35, 10, 'Periodicidade', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Mensal', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Unidade de medida', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Porcentagem', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Meta', 1, 0, 'C', true);
        $pdf->Cell(29, 10, '<= 10%', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Origem dos dados', 1, 0, 'C', true);
        $pdf->Cell(45, 10, 'Sistema transfusional', 1, 1, 'C');
        
        $pdf->Cell(35, 10, iconv_safe('Fórmula'), 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('(Nº de bolsas reserva / Nº de transfusões) X 100'), 1, 1, 'C');
        $pdf->Ln();
        
        $pdf->Cell(70, 10, 'MESES', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, nomeia_mes_sigla($m), 1, 0, 'C', true);
        $pdf->Cell(22, 10, 'TOTAL', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(70, 10, iconv_safe('Nº de bolsas reserva'), 1, 0, 'C', true);
        $total_reserva = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_reserva = "SELECT COUNT(*) as qtd FROM sth_cadastro_bolsa cb
                            WHERE cb.reserva = 'sim' AND EXTRACT(MONTH FROM cb.data_transfusao) = $m 
                            AND EXTRACT(YEAR FROM cb.data_transfusao) = $ano";
            $res_reserva = pg_query($conexao, $sql_reserva);
            $qtd_reserva = ($res_reserva && $row = pg_fetch_assoc($res_reserva)) ? $row['qtd'] : 0;
            $total_reserva += $qtd_reserva;
            $pdf->Cell(15, 10, $qtd_reserva, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_reserva, 1, 1, 'C');
        
        $pdf->Cell(70, 10, iconv_safe('Nº de transfusões'), 1, 0, 'C', true);
        $total_transf = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_transf = "SELECT COUNT(DISTINCT cb.id_bolsa) as qtd FROM sth_cadastro_bolsa cb
                           WHERE EXTRACT(MONTH FROM cb.data_transfusao) = $m AND EXTRACT(YEAR FROM cb.data_transfusao) = $ano";
            $res_transf = pg_query($conexao, $sql_transf);
            $qtd_transf = ($res_transf && $row = pg_fetch_assoc($res_transf)) ? $row['qtd'] : 0;
            $total_transf += $qtd_transf;
            $pdf->Cell(15, 10, $qtd_transf, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_transf, 1, 1, 'C');
        
        $pdf->Cell(70, 10, 'Porcentagem (%)', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, '-', 1, 0, 'C');
        $perc_total = $total_transf > 0 ? number_format(($total_reserva / $total_transf) * 100, 2, ',', '') . '%' : '0,00%';
        $pdf->Cell(22, 10, $perc_total, 1, 1, 'C');
        break;

    // --- CASO 14: INDICADOR DE BOLSAS DEVOLVIDAS ---
    case 'indi_bolsa_devolvida':
        $pdf->setTitulo('INDICADOR DE BOLSAS NÃO TRANSFUNDIDAS - HUM');
        $pdf->AddPage('L');
        
        $ano = date('Y', strtotime($data_inicio));
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(35, 10, 'OBJETIVO', 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('Monitorar a porcentagem de bolsas não transfundidas perante o total de bolsas solicitadas'), 1, 1, 'C');
        
        $pdf->Cell(35, 10, 'Periodicidade', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Mensal', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Unidade de medida', 1, 0, 'C', true);
        $pdf->Cell(29, 10, 'Porcentagem', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Meta', 1, 0, 'C', true);
        $pdf->Cell(29, 10, '<= 5%', 1, 0, 'C');
        $pdf->Cell(35, 10, 'Origem dos dados', 1, 0, 'C', true);
        $pdf->Cell(45, 10, 'Sistema transfusional', 1, 1, 'C');
        
        $pdf->Cell(35, 10, iconv_safe('Fórmula'), 1, 0, 'C', true);
        $pdf->Cell(237, 10, iconv_safe('(Nº de bolsas devolvidas / Nº total de bolsas) X 100'), 1, 1, 'C');
        $pdf->Ln();
        
        $pdf->Cell(70, 10, 'MESES', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, nomeia_mes_sigla($m), 1, 0, 'C', true);
        $pdf->Cell(22, 10, 'TOTAL', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(70, 10, iconv_safe('Nº de bolsas devolvidas'), 1, 0, 'C', true);
        $total_devol = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_devol = "SELECT COUNT(*) as qtd FROM sth_bolsas_devolvidas bd
                          WHERE EXTRACT(MONTH FROM bd.dt_devolucao) = $m AND EXTRACT(YEAR FROM bd.dt_devolucao) = $ano";
            $res_devol = pg_query($conexao, $sql_devol);
            $qtd_devol = ($res_devol && $row = pg_fetch_assoc($res_devol)) ? $row['qtd'] : 0;
            $total_devol += $qtd_devol;
            $pdf->Cell(15, 10, $qtd_devol, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_devol, 1, 1, 'C');
        
        $pdf->Cell(70, 10, iconv_safe('Nº total de bolsas'), 1, 0, 'C', true);
        $total_bolsas = 0;
        for($m = 1; $m <= 12; $m++) {
            $sql_bolsas = "SELECT COUNT(*) as qtd FROM sth_cadastro_bolsa cb
                           WHERE EXTRACT(MONTH FROM cb.data_transfusao) = $m AND EXTRACT(YEAR FROM cb.data_transfusao) = $ano";
            $res_bolsas = pg_query($conexao, $sql_bolsas);
            $qtd_bolsas = ($res_bolsas && $row = pg_fetch_assoc($res_bolsas)) ? $row['qtd'] : 0;
            $total_bolsas += $qtd_bolsas;
            $pdf->Cell(15, 10, $qtd_bolsas, 1, 0, 'C');
        }
        $pdf->Cell(22, 10, $total_bolsas, 1, 1, 'C');
        
        $pdf->Cell(70, 10, 'Porcentagem (%)', 1, 0, 'C', true);
        for($m = 1; $m <= 12; $m++) $pdf->Cell(15, 10, '-', 1, 0, 'C');
        $perc_total = $total_bolsas > 0 ? number_format(($total_devol / $total_bolsas) * 100, 2, ',', '') . '%' : '0,00%';
        $pdf->Cell(22, 10, $perc_total, 1, 1, 'C');
        break;

    default:
        $pdf->setTitulo('RELATÓRIO NÃO ENCONTRADO');
        $pdf->AddPage();
        $pdf->SetTextColor(200, 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 20, iconv_safe("Relatório '{$tipo}' ainda não migrado para o novo sistema."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, iconv_safe("Por favor, contate o suporte."), 0, 1, 'C');
        break;
}

// 8. GERAR OUTPUT
$pdf->Output();
?>
