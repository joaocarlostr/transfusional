<?php
// Prevent any implicit output
ob_start();

$term = isset($_GET['q']) ? trim($_GET['q']) : '';

// TESTE DE DIAGNÓSTICO ESTRUTURAL (Antes de qualquer include)
if (strtolower($term) === 'teste') {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'results' => [
            ['id' => 'debug_sc', 'text' => 'SUCESSO: Script backend acessível! (Include Database ignorado)']
        ]
    ]);
    exit;
}

ini_set('display_errors', 0); // Disable display errors to cleanly handle JSON
error_reporting(E_ALL);

$debug = [];
$conexao = null;

// Try to include database
if (file_exists("database.php")) {
    try {
        include __DIR__ . "/database.php";
        $debug[] = "Included database.php";
    } catch (Throwable $t) {
        $debug[] = "Exception including database.php: " . $t->getMessage();
    }
} else {
    $debug[] = "database.php NOT found";
}

// Check connection
if (isset($conexao) && $conexao) {
    // Check if it's a valid resource/object
    if (is_resource($conexao) || is_object($conexao)) {
         $debug[] = "Connection variable IS set and valid.";
    } else {
         $debug[] = "Connection variable IS set but NOT a resource/object.";
    }
} else {
    $debug[] = "Connection variable \$conexao is NULL or NOT set.";
}

// Clean buffer before outputting JSON
ob_clean();
header('Content-Type: application/json');

$response = ['results' => []];
$term = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Function to add debug as a result
function returnDebug($msgs, $response) {
    $response['results'][] = [
        'id' => 'debug',
        'text' => 'DEBUG: ' . implode(" | ", $msgs)
    ];
    return json_encode($response);
}

if (!isset($conexao) || !$conexao) {
    echo returnDebug($debug, $response);
    exit;
}

try {
    if (empty($term)) {
        echo json_encode($response);
        exit;
    }

    $termSafe = pg_escape_string($conexao, $term);

    // Prontuario
    if ($type === 'prontuario') {
        $query = "SELECT id_paciente, prontuario, nome_completo 
                  FROM sth_dados_paciente 
                  WHERE prontuario ILIKE '%$termSafe%' OR nome_completo ILIKE '%$termSafe%' 
                  ORDER BY nome_completo ASC LIMIT 20";
        
        $result = @pg_query($conexao, $query);
        
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $response['results'][] = [
                    'id' => $row['id_paciente'], 
                    'text' => $row['prontuario'] . ' - ' . $row['nome_completo']
                ];
            }
        } else {
             $debug[] = "Query Error: " . pg_last_error($conexao);
        }
    }
    // Bolsa
    elseif ($type === 'bolsa') {
        $query = "SELECT cb.id_bolsa, cb.num_bolsa, h.sigla 
                  FROM sth_Cadastro_Bolsa cb
                  LEFT JOIN sth_Hemocomponentes h ON cb.id_hemocomponente = h.id_hemocomponente
                  WHERE cb.num_bolsa ILIKE '%$termSafe%' 
                  ORDER BY cb.num_bolsa ASC LIMIT 20";
        
        $result = @pg_query($conexao, $query);
        
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $sigla = isset($row['sigla']) ? $row['sigla'] : 'N/A';
                $response['results'][] = [
                    'id' => $row['id_bolsa'],
                    'text' => $row['num_bolsa'] . ' (' . $sigla . ')'
                ];
            }
        } else {
            $debug[] = "Query Error: " . pg_last_error($conexao);
        }
    }

    // If no results, show debug info if there were errors
    if (empty($response['results']) && !empty($debug)) {
        // Only show debug if query failed or explicit issues
        // If simply not found, Select2 handles "No results"
        // But let's show debug for now to be sure
        if (strpos(implode($debug), "Query Error") !== false) {
             echo returnDebug($debug, $response);
             exit;
        }
    }

} catch (Exception $e) {
    $debug[] = "Exception: " . $e->getMessage();
    echo returnDebug($debug, $response);
    exit;
}

// Check for empty results again and valid JSON
echo json_encode($response);
exit;

