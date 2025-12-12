<?php
// Configurações de erro para garantir JSON limpo
error_reporting(E_ALL); // Logar tudo
ini_set('display_errors', 0); // Mas não mostrar na tela
ini_set('log_errors', 1); // Gravar no log de erros do PHP

// Iniciar buffer
ob_start();

try {
    // Verificar se database.php existe
    if (!file_exists("database.php")) {
        throw new Exception("Arquivo database.php não encontrado");
    }

    require_once "database.php";

    // Iniciar sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar conexão
    if (!isset($conexao) || !$conexao) {
        throw new Exception("Falha na conexão com o banco de dados");
    }

    header('Content-Type: application/json');

    // Validar input
    $campo = $_POST['campo'] ?? '';
    $valor = $_POST['valor'] ?? '';

    if (empty($campo) || empty($valor)) {
        throw new Exception("Dados inválidos: Campo ou Valor vazios");
    }

    $campos_permitidos = ['prontuario', 'cpf', 'cns'];
    if (!in_array($campo, $campos_permitidos)) {
        throw new Exception("Campo inválido: $campo");
    }

    // Limpar valor
    $valor_limpo = pg_escape_string($conexao, $valor);
    if ($campo === 'cpf') {
        $valor_limpo = preg_replace('/[^0-9]/', '', $valor);
    }

    // Query
    $query = "SELECT id_paciente, nome, prontuario, cpf, cns 
              FROM sth_paciente 
              WHERE $campo = '$valor_limpo' 
              LIMIT 1";

    $result = @pg_query($conexao, $query);

    if ($result === false) {
        throw new Exception("Erro na consulta SQL: " . pg_last_error($conexao));
    }

    $response = [];

    if (pg_num_rows($result) > 0) {
        $paciente = pg_fetch_assoc($result);
        
        // Formatar CPF
        $cpf_formatado = $paciente['cpf'];
        if (strlen($paciente['cpf']) == 11) {
            $cpf_formatado = substr($paciente['cpf'], 0, 3) . '.' . 
                            substr($paciente['cpf'], 3, 3) . '.' . 
                            substr($paciente['cpf'], 6, 3) . '-' . 
                            substr($paciente['cpf'], 9, 2);
        }

        $response = [
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
        ];
    } else {
        $response = [
            'existe' => false,
            'mensagem' => 'Disponível'
        ];
    }

    // Limpar buffer antes de enviar JSON
    ob_end_clean();
    echo json_encode($response);

} catch (Throwable $e) { // Throwable pega Erros Fatais e Exceptions
    // Limpar buffer
    if (ob_get_length()) ob_end_clean();
    
    // Log do erro real no servidor
    error_log("ERRO AJAX PACIENTE: " . $e->getMessage());

    // Retorno JSON de erro
    header('Content-Type: application/json');
    echo json_encode([
        'existe' => false,
        'erro' => true,
        'mensagem' => 'Erro interno: ' . $e->getMessage() // Útil para debug agora (remover em prod)
    ]);
}
?>
