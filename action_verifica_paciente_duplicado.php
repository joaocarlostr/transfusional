<?php
include "database.php";
header('Content-Type: application/json');

// Receber dados via POST
$campo = $_POST['campo'] ?? '';
$valor = $_POST['valor'] ?? '';

// Validar entrada
if (empty($campo) || empty($valor)) {
    echo json_encode(['existe' => false, 'mensagem' => 'Dados inválidos']);
    exit;
}

// Campos permitidos para verificação
$campos_permitidos = ['prontuario', 'cpf', 'cns'];
if (!in_array($campo, $campos_permitidos)) {
    echo json_encode(['existe' => false, 'mensagem' => 'Campo inválido']);
    exit;
}

// Limpar valor (remover caracteres especiais se for CPF)
$valor_limpo = $valor;
if ($campo === 'cpf') {
    $valor_limpo = preg_replace('/[^0-9]/', '', $valor);
}

// Verificar se já existe no banco
$query = "SELECT id_paciente, nome, prontuario, cpf, cns 
          FROM sth_paciente 
          WHERE $campo = '$valor_limpo' 
          LIMIT 1";

$result = conecta_query($conexao, $query);

if (pg_num_rows($result) > 0) {
    $paciente = pg_fetch_assoc($result);
    
    // Formatar CPF para exibição
    $cpf_formatado = $paciente['cpf'];
    if (strlen($paciente['cpf']) == 11) {
        $cpf_formatado = substr($paciente['cpf'], 0, 3) . '.' . 
                        substr($paciente['cpf'], 3, 3) . '.' . 
                        substr($paciente['cpf'], 6, 3) . '-' . 
                        substr($paciente['cpf'], 9, 2);
    }
    
    echo json_encode([
        'existe' => true,
        'paciente' => [
            'id' => $paciente['id_paciente'],
            'nome' => $paciente['nome'],
            'prontuario' => $paciente['prontuario'],
            'cpf' => $cpf_formatado,
            'cns' => $paciente['cns']
        ],
        'campo' => $campo,
        'mensagem' => 'Paciente já cadastrado!'
    ]);
} else {
    echo json_encode([
        'existe' => false,
        'mensagem' => 'Disponível'
    ]);
}
?>
