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

// 3. RECEBER E VALIDAR DADOS DO POST
// Validar tipo de relatório (whitelist)
$tipos_validos = array(
    'bolsa', 'paciente', 'tipo_reacao_paciente', 'bolsa_devolvida', 'bolsa_reserva',
    'bolsa_repetida', 'paciente_sem_registro', 'tipo_sanguineo', 'tipo_setor',
    'nao_conformidade', 'indi_nao_conformidade', 'reacao_transfusional',
    'indi_bolsa_reserva', 'indi_bolsa_devolvida'
);
$tipo = isset($_POST['tipo']) && in_array($_POST['tipo'], $tipos_validos) ? $_POST['tipo'] : 'bolsa';

// Validar e sanitizar datas
$data_inicio = isset($_POST['data_inicio']) ? filter_var($_POST['data_inicio'], FILTER_SANITIZE_STRING) : date('Y-m-d');
$data_fim = isset($_POST['data_fim']) ? filter_var($_POST['data_fim'], FILTER_SANITIZE_STRING) : date('Y-m-d');

// Validar e sanitizar outros filtros
$id_setor = '';
if (isset($_POST['id_setor']) && $_POST['id_setor'] !== '') {
    if ($_POST['id_setor'] === 'pa_geral') {
        $id_setor = 'pa_geral';
    } else {
        $id_setor = filter_var($_POST['id_setor'], FILTER_VALIDATE_INT);
        if ($id_setor === false) $id_setor = '';
    }
}

$bolsa_id = isset($_POST['bolsa']) ? filter_var($_POST['bolsa'], FILTER_VALIDATE_INT) : false;
if ($bolsa_id === false) $bolsa_id = '';

$prontuario_id = isset($_POST['prontuario']) ? filter_var($_POST['prontuario'], FILTER_VALIDATE_INT) : false;
if ($prontuario_id === false) $prontuario_id = '';

// Normalizar datas (formato brasileiro para ISO)
if (strpos($data_inicio, '/') !== false) {
    $parts = explode('/', $data_inicio);
    if (count($parts) == 3 && checkdate($parts[1], $parts[0], $parts[2])) {
        $data_inicio = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    } else {
        $data_inicio = date('Y-m-d'); // Fallback para data inválida
    }
}
if (strpos($data_fim, '/') !== false) {
    $parts = explode('/', $data_fim);
    if (count($parts) == 3 && checkdate($parts[1], $parts[0], $parts[2])) {
        $data_fim = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    } else {
        $data_fim = date('Y-m-d'); // Fallback para data inválida
    }
}

// Validar formato de data final (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio)) $data_inicio = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) $data_fim = date('Y-m-d');

// 3.1 FUNÇÃO HELPER PARA QUERIES SEGURAS
function executar_query_segura($conexao, $query, $params = array()) {
    // Se não há parâmetros, executar query direta (para queries sem placeholders)
    if (empty($params)) {
        return pg_query($conexao, $query);
    }
    
    // Gerar nome único para prepared statement
    $stmt_name = 'stmt_' . md5($query . microtime());
    
    // Preparar statement
    $prepared = pg_prepare($conexao, $stmt_name, $query);
    if (!$prepared) {
        error_log("Erro ao preparar query: " . pg_last_error($conexao));
        return false;
    }
    
    // Executar com parâmetros
    $result = pg_execute($conexao, $stmt_name, $params);
    if (!$result) {
        error_log("Erro ao executar query: " . pg_last_error($conexao));
        return false;
    }
    
    return $result;
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
    
    // Método para adicionar linha de totalizadores
    function addTotalizador($margem_esquerda, $widths, $label, $valor, $label_col_span = 1) {
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(220, 220, 220);
        $this->SetX($margem_esquerda);
        
        // Calcular largura total para o label
        $label_width = 0;
        for ($i = 0; $i < $label_col_span; $i++) {
            $label_width += $widths[$i];
        }
        
        $this->Cell($label_width, 6, iconv_safe($label), 1, 0, 'R', true);
        
        // Calcular largura restante para o valor
        $valor_width = 0;
        for ($i = $label_col_span; $i < count($widths); $i++) {
            $valor_width += $widths[$i];
        }
        
        $this->Cell($valor_width, 6, iconv_safe($valor), 1, 1, 'L', true);
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
        
        // Query com prepared statement
        $sql = "SELECT cb.num_bolsa, cb.num_sus, cb.data_transfusao, cb.horario_inicio, 
                dp.prontuario, dp.nome_completo, h.sigla
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                WHERE cb.data_transfusao BETWEEN $1 AND $2";
        
        $params = array($data_inicio, $data_fim);
        $param_count = 3;
        
        if (!empty($bolsa_id)) {
            $sql .= " AND cb.id_bolsa = $$param_count";
            $params[] = $bolsa_id;
            $param_count++;
        }
        if (!empty($prontuario_id)) {
            $sql .= " AND cb.id_paciente = $$param_count";
            $params[] = $prontuario_id;
            $param_count++;
        }

        $sql .= " ORDER BY cb.data_transfusao";
        $resultado = executar_query_segura($conexao, $sql, $params);
        
        // CHECK SEM DADOS
        if (!$resultado || pg_num_rows($resultado) == 0) {
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
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE BOLSAS TRANSFUNDIDAS:', $count, 7);
        
        break;

    // --- CASO 2: PACIENTES TRANSFUNDIDOS ---
    case 'paciente':
        $pdf->setTitulo('RELATÓRIO DE PACIENTES TRANSFUNDIDOS - HUM');
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        // Query otimizada com LEFT JOINs para evitar subqueries no loop
        $sql = "SELECT dp.nome_completo, dp.id_paciente, dp.nome_social, dp.dt_nasc, dp.abo, dp.rh_d, dp.cpf, dp.prontuario, 
                cb.data_transfusao, cb.horario_inicio, cb.num_bolsa, cb.id_bolsa, cb.num_sus, c.id_controle, dp.numero_rt,
                CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome,
                CASE WHEN rt.id_transfusionais IS NOT NULL THEN 'Sim' ELSE '' END as tem_fit,
                CASE WHEN cnc.id_controle_nao_conformidade IS NOT NULL THEN 'Sim' ELSE '' END as tem_nc
                FROM sth_controle c
                INNER JOIN sth_cadastro_bolsa cb ON c.id_bolsa = cb.id_bolsa
                INNER JOIN sth_dados_paciente dp ON cb.id_paciente = dp.id_paciente
                LEFT JOIN sth_reacoes_transfusionais rt ON rt.id_bolsa = cb.id_bolsa
                LEFT JOIN sth_controle_nao_conformidade cnc ON cnc.id_controle = c.id_controle
                WHERE cb.data_transfusao BETWEEN $1 AND $2";
        
        $params = array($data_inicio, $data_fim);
        $param_count = 3;
        
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND c.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND c.id_setor = $$param_count";
                $params[] = $id_setor;
                $param_count++;
            }
        }
        if (!empty($bolsa_id)) {
            $sql .= " AND cb.id_bolsa = $$param_count";
            $params[] = $bolsa_id;
            $param_count++;
        }
        if (!empty($prontuario_id)) {
            $sql .= " AND cb.id_paciente = $$param_count";
            $params[] = $prontuario_id;
            $param_count++;
        }
        
        $sql .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";
        $resultado = executar_query_segura($conexao, $sql, $params);

        // CHECK SEM DADOS
        if (!$resultado || pg_num_rows($resultado) == 0) {
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
            $pdf->Cell($w[10], 5, iconv_safe($row['tem_fit']), 1, 0, 'C', $fill); 
            $pdf->Cell($w[11], 5, iconv_safe($row['tem_nc']), 1, 1, 'C', $fill); 
        }
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE PACIENTES TRANSFUNDIDOS:', $count, 11);
        
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
                WHERE cb.data_transfusao BETWEEN $1 AND $2";
        
        $params = array($data_inicio, $data_fim);
        $param_count = 3;
        
        if (!empty($bolsa_id)) {
            $sql .= " AND cb.id_bolsa = $$param_count";
            $params[] = $bolsa_id;
            $param_count++;
        }
        if (!empty($prontuario_id)) {
            $sql .= " AND cb.id_paciente = $$param_count";
            $params[] = $prontuario_id;
            $param_count++;
        }
        
        $sql .= " ORDER BY cb.data_transfusao, cb.horario_inicio";
        $resultado = executar_query_segura($conexao, $sql, $params);
        
        // CHECK SEM DADOS
        if (!$resultado || pg_num_rows($resultado) == 0) {
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

            // Detalhes da Reação com prepared statement
            $q_det = "SELECT tp.nome, tp.descricao, rt.observacao 
                      FROM sth_tipos_reacoes tp
                      INNER JOIN sth_reacoes_transfusionais rt ON rt.tipo_reacao = tp.id_reacao
                      WHERE rt.id_bolsa = $1";
            $res_det = executar_query_segura($conexao, $q_det, array($row['id_bolsa']));
            
            if ($res_det && pg_num_rows($res_det) > 0) {
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
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE REAÇÕES TRANSFUSIONAIS:', $count, 7);
        
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
                WHERE bd.dt_devolucao BETWEEN $1 AND $2";
        
        $sql .= " ORDER BY bd.dt_devolucao";
        $resultado = executar_query_segura($conexao, $sql, array($data_inicio, $data_fim));
        
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
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE BOLSAS NÃO TRANSFUNDIDAS:', $count, 6);
        
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
                WHERE cb.data_transfusao BETWEEN $1 AND $2 AND reserva = 'sim'";
        
        $params = array($data_inicio, $data_fim);
        $param_count = 3;
        
        // Filtro Setor (usando coluna id_livro_setor conforme original)
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND cb.id_livro_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND cb.id_livro_setor = $$param_count";
                $params[] = $id_setor;
                $param_count++;
            }
        }
        
        if (!empty($bolsa_id)) {
            $sql .= " AND cb.num_bolsa = (SELECT num_bolsa FROM sth_cadastro_bolsa WHERE id_bolsa = $$param_count)";
            $params[] = $bolsa_id;
            $param_count++;
        }
        
        $sql .= " ORDER BY cb.data_transfusao, cb.horario_inicio, nome";
        $resultado = executar_query_segura($conexao, $sql, $params);
        
        // CHECK SEM DADOS
        if (!$resultado || pg_num_rows($resultado) == 0) {
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
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE BOLSAS RESERVAS:', $count, 6);
        
        break;

    // --- CASO 6: BOLSAS REPETIDAS ---
    case 'bolsa_repetida':
        $pdf->setTitulo('BOLSAS REPETIDAS - HUM'); // Título mais curto para não invadir logo
        $pdf->AddPage('P');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        $sql = "SELECT cb.num_bolsa, cb.num_sus, cb.data_transfusao, cb.horario_inicio, 
                dp.nome_completo, dp.nome_social, h.sigla, c.dt_busca_ativa, cb.notvisa, cb.shtnovo,
                CASE WHEN dp.nome_social is null or dp.nome_social = '' then dp.nome_completo ELSE dp.nome_social END as nome
                FROM sth_cadastro_bolsa cb
                INNER JOIN sth_dados_paciente dp ON dp.id_paciente = cb.id_paciente
                INNER JOIN sth_hemocomponentes h ON h.id_hemocomponente = cb.id_hemocomponente
                INNER JOIN sth_controle c ON c.id_bolsa = cb.id_bolsa
                WHERE cb.num_bolsa IN (SELECT num_bolsa FROM sth_cadastro_bolsa GROUP BY num_bolsa HAVING count(*) > 1)
                AND cb.data_transfusao BETWEEN $1 AND $2";
        
        $params = array($data_inicio, $data_fim);
        $param_count = 3;
        
        if (!empty($id_setor)) {
            if ($id_setor == "pa_geral") {
               $sql .= " AND c.id_setor IN (SELECT id_setor FROM sth_setores WHERE nome_setor LIKE 'PA%')"; 
            } elseif (is_numeric($id_setor)) {
                $sql .= " AND c.id_setor = $$param_count";
                $params[] = $id_setor;
                $param_count++;
            }
        }
        
        if (!empty($bolsa_id)) {
            $q_num_bolsa = "SELECT num_bolsa FROM sth_cadastro_bolsa WHERE id_bolsa = $$param_count";
            $r_num_bolsa = executar_query_segura($conexao, $q_num_bolsa, array($bolsa_id));
            if ($r_num_bolsa && $row_nb = pg_fetch_assoc($r_num_bolsa)) {
                $sql .= " AND cb.num_bolsa = $$param_count";
                $params[] = $row_nb['num_bolsa'];
                $param_count++;
            }
        }
        
        if (!empty($prontuario_id)) {
            $sql .= " AND cb.id_paciente = $$param_count";
            $params[] = $prontuario_id;
            $param_count++;
        }
        
        $sql .= " ORDER BY cb.num_bolsa, cb.num_sus";
        $resultado = executar_query_segura($conexao, $sql, $params);
        
        // CHECK SEM DADOS
        if (!$resultado || pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas ajustadas: reduzir Paciente para evitar invasão
        // N°(10), Dt(20), Hora(13), Paciente(45), Bolsa(20), SUS(20), Hemoc(25), Notiv(10), SHT(10), HEM(10). Total: 183
        $w = array(10, 20, 13, 45, 20, 20, 25, 10, 10, 10);
        $margem_esquerda = (210 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 7); // Fonte menor para cabeçalhos
        $pdf->SetFillColor(200, 200, 200);
        // Cabeçalhos abreviados para caber
        $header = array('Nº', 'Dt Transf', 'Horário', 'Paciente', 'Bolsa', 'Nº SUS', 'Hemoc/Atrib', 'Notiv', 'SHT', 'HEM');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 6, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 6.5); // Fonte menor para dados
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $fill = ($count % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->SetX($margem_esquerda);
            
            // Limitar nome para não invadir próxima coluna (máx 30 caracteres)
            $nome_paciente = (strlen($row['nome']) > 30) ? substr($row['nome'], 0, 30) . "..." : $row['nome'];

            $pdf->Cell($w[0], 6, $count, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, date('d/m/Y', strtotime($row['data_transfusao'])), 1, 0, 'C', $fill);
            $pdf->Cell($w[2], 6, substr($row['horario_inicio'], 0, 5), 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 6, iconv_safe($nome_paciente), 1, 0, 'L', $fill);
            $pdf->Cell($w[4], 6, $row['num_bolsa'], 1, 0, 'C', $fill);
            $pdf->Cell($w[5], 6, $row['num_sus'], 1, 0, 'C', $fill);
            $pdf->Cell($w[6], 6, iconv_safe($row['sigla']), 1, 0, 'L', $fill);
            $pdf->Cell($w[7], 6, iconv_safe($row['notvisa']), 1, 0, 'C', $fill);
            $pdf->Cell($w[8], 6, iconv_safe($row['shtnovo']), 1, 0, 'C', $fill);
            $pdf->Cell($w[9], 6, " ", 1, 1, 'C', $fill);
        }
        
        // TOTALIZADOR (sem 'bolsas')
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE BOLSAS REPETIDAS:', $count, 9);
        
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
        
        $resultado = executar_query_segura($conexao, $sql, array());
        
        // CHECK SEM DADOS
        if (!$resultado || pg_num_rows($resultado) == 0) {
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
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE PACIENTES SEM REGISTRO:', $count, 6);
        
        break;

    // --- CASO 8: TIPO SANGUÍNEO ---
    case 'tipo_sanguineo':
        $pdf->setTitulo('TIPO SANGUÍNEO - PACIENTES TRANSFUNDIDOS - HUM'); // Título mais curto
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        // Função para construir a matriz ABO x Rh (arrays compatíveis com PHP 5.4)
        $tipos_rh = array("Positivo", "Negativo", "Outro", "Desconhecido");
        $tipos_abo = array('A', 'B', 'AB', 'O', 'Outro', 'Desconhecido');
        
        // Array para armazenar totais
        $totais_colunas = array_fill(0, count($tipos_abo), 0);
        $totais_linhas = array();
        
        // Cabeçalho da tabela
        $w_label = 60;
        $w_cell = 22;
        $w_total = 25;
        $margem_esquerda = (297 - ($w_label + (count($tipos_abo) * $w_cell) + $w_total)) / 2;
        $pdf->SetX($margem_esquerda);
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell($w_label, 10, 'Rh / ABO', 1, 0, 'C', true);
        foreach ($tipos_abo as $abo) {
            $pdf->Cell($w_cell, 10, iconv_safe($abo), 1, 0, 'C', true);
        }
        $pdf->Cell($w_total, 10, 'Total', 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        foreach ($tipos_rh as $rh) {
            $pdf->SetX($margem_esquerda);
            $pdf->Cell($w_label, 10, iconv_safe($rh), 1, 0, 'C');
            
            $total_linha = 0;
            foreach ($tipos_abo as $idx => $abo) {
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
                $total_linha += $qtd;
                $totais_colunas[$idx] += $qtd;
            }
            
            // Total da linha
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($w_total, 10, $total_linha, 1, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
        }
        
        // Linha de totais
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetX($margem_esquerda);
        $pdf->Cell($w_label, 10, 'TOTAL', 1, 0, 'C', true);
        $total_geral = 0;
        foreach ($totais_colunas as $total_col) {
            $pdf->Cell($w_cell, 10, $total_col, 1, 0, 'C', true);
            $total_geral += $total_col;
        }
        $pdf->Cell($w_total, 10, $total_geral, 1, 1, 'C', true);
        
        break;

    // --- CASO 9: SETORES ---
    case 'tipo_setor':
        $pdf->setTitulo('BOLSAS TRANSFUNDIDAS POR SETOR - HUM'); // Título mais curto
        $pdf->AddPage('L');
        imprimirCabecalhoPadrao($pdf, $data_inicio, $data_fim, $id_setor, $bolsa_id, $conexao);
        
        // Query 100% ORIGINAL (como era antes de qualquer alteração)
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
        if (!$resultado || pg_num_rows($resultado) == 0) {
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(20);
            $pdf->Cell(0, 10, iconv_safe("Sem dados para serem exibidos neste relatório."), 0, 1, 'C');
            break; 
        }

        // Colunas: Setores(100), Transfusões(40), FIT(40), NC(40). Total: 220
        $w = array(100, 40, 40, 40);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Setores', 'Transfusões', 'FIT', 'NC');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 10, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);
        
        // Variáveis para totais
        $total_transfusoes = 0;
        $total_fit = 0;
        $total_nc = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $pdf->SetX($margem_esquerda);
            $pdf->Cell($w[0], 8, iconv_safe($row['nome_setor']), 1, 0, 'L');
            $pdf->Cell($w[1], 8, $row['qtd_bolsa'], 1, 0, 'C');
            $pdf->Cell($w[2], 8, $row['qtd_rt'], 1, 0, 'C');
            $pdf->Cell($w[3], 8, $row['qtd_nao_conformidade'], 1, 1, 'C');
            
            // Acumular totais
            $total_transfusoes += $row['qtd_bolsa'];
            $total_fit += $row['qtd_rt'];
            $total_nc += $row['qtd_nao_conformidade'];
        }
        
        // TOTALIZADOR GERAL
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetX($margem_esquerda);
        $pdf->Cell($w[0], 8, 'TOTAL GERAL', 1, 0, 'R', true);
        $pdf->Cell($w[1], 8, $total_transfusoes, 1, 0, 'C', true);
        $pdf->Cell($w[2], 8, $total_fit, 1, 0, 'C', true);
        $pdf->Cell($w[3], 8, $total_nc, 1, 1, 'C', true);
        
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
                cb.data_transfusao as data_nao_conformidade,
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

        // Colunas: N°(10), Prontuário(20), Paciente(60), Dt Não Conform.(25), Bolsa(25), Não Conformidade(138). Total: 278
        $w = array(10, 20, 60, 25, 25, 138);
        $margem_esquerda = (297 - array_sum($w)) / 2;
        $pdf->SetX($margem_esquerda);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $header = array('Nº', 'Prontuário', 'Paciente', 'Dt Não Conform.', 'Bolsa', 'Não Conformidade');
        foreach($header as $i => $h) $pdf->Cell($w[$i], 10, iconv_safe($h), 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);
        $count = 0;
        
        while ($row = pg_fetch_assoc($resultado)) {
            $count++;
            $pdf->SetX($margem_esquerda);
            
            $nome_paciente = (strlen($row['nome']) > 35) ? substr($row['nome'], 0, 35) . "..." : $row['nome'];
            $nao_conformidade = $row['tipo'] . ' - ' . $row['nao_conformidade'];
            $nao_conformidade = (strlen($nao_conformidade) > 78) ? substr($nao_conformidade, 0, 78) . "..." : $nao_conformidade;
            
            // Formata a data da não conformidade
            $data_nao_conf = !empty($row['data_nao_conformidade']) ? date('d/m/Y', strtotime($row['data_nao_conformidade'])) : '-';

            $pdf->Cell($w[0], 8, str_pad($count, 3, '0', STR_PAD_LEFT), 1, 0, 'C');
            $pdf->Cell($w[1], 8, $row['prontuario'], 1, 0, 'C');
            $pdf->Cell($w[2], 8, iconv_safe($nome_paciente), 1, 0, 'L');
            $pdf->Cell($w[3], 8, iconv_safe($data_nao_conf), 1, 0, 'C');
            $pdf->Cell($w[4], 8, $row['num_bolsa'], 1, 0, 'L');
            $pdf->Cell($w[5], 8, iconv_safe($nao_conformidade), 1, 1, 'L');
        }
        
        // TOTALIZADOR
        $pdf->addTotalizador($margem_esquerda, $w, 'TOTAL DE NÃO CONFORMIDADES:', $count, 5);
        
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
