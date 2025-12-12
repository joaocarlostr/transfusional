<?php
// Script para extrair a estrutura de todas as tabelas do banco de dados
// Tenta usar a conexão via PDO ou conexão do banco local

// Tenta conectar ao PostgreSQL usando diferentes métodos
$conexao = null;

// Método 1: Tentar com pg_connect (se PostgreSQL PHP extension estiver habilitada)
if (function_exists('pg_connect')) {
    $conexao = @pg_connect("host=186.226.56.128 dbname=shiteste user=postgres password=systemhum");
}

// Se não conseguir, tenta com local PostgreSQL
if (!$conexao) {
    $conexao = @pg_connect("host=localhost dbname=Transfusional user=postgres password=root");
}

// Se ainda não conseguir, exibe erro
if (!$conexao) {
    echo "Erro: PostgreSQL PHP extension não está habilitada ou banco não acessível.\n";
    echo "Solução: Habilitar pg_connect no php.ini do XAMPP\n";
    exit(1);
}

// Obter lista de todas as tabelas
$query_tabelas = "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename";
$resultado = pg_query($conexao, $query_tabelas);

if (!$resultado) {
    die("Erro ao listar tabelas: " . pg_last_error($conexao));
}

// Coletar dados para exibição formatada
$dados = [];

while ($row = pg_fetch_assoc($resultado)) {
    $tablename = $row['tablename'];
    
    $table_info = [
        'name' => $tablename,
        'columns' => [],
        'pk' => [],
        'fk' => []
    ];
    
    // Obter informações das colunas
    $query_colunas = "
        SELECT 
            column_name,
            data_type,
            is_nullable,
            column_default,
            character_maximum_length
        FROM information_schema.columns
        WHERE table_name = '{$tablename}'
        ORDER BY ordinal_position
    ";
    
    $resultado_colunas = pg_query($conexao, $query_colunas);
    
    if ($resultado_colunas) {
        while ($col = pg_fetch_assoc($resultado_colunas)) {
            $table_info['columns'][] = $col;
        }
    }
    
    // Obter chaves primárias (usando uma query mais simples)
    $query_pk = "
        SELECT a.attname
        FROM pg_index i
        JOIN pg_attribute a ON a.attrelid = i.indrelid
            AND a.attnum = ANY(i.indkey)
        WHERE i.indrelid = '{$tablename}'::regclass
            AND i.indisprimary
    ";
    
    $resultado_pk = @pg_query($conexao, $query_pk);
    if ($resultado_pk) {
        while ($pk = pg_fetch_assoc($resultado_pk)) {
            $table_info['pk'][] = $pk['attname'];
        }
    }
    
    $dados[] = $table_info;
}

pg_close($conexao);

// Exibir resultado formatado
echo "===============================================\n";
echo "          ESTRUTURA DE TODAS AS TABELAS\n";
echo "===============================================\n\n";

foreach ($dados as $table) {
    echo "📋 TABELA: " . strtoupper($table['name']) . "\n";
    echo str_repeat("-", 100) . "\n";
    
    // Exibir cabeçalho
    printf("%-30s | %-25s | %-10s | %-15s\n", "COLUNA", "TIPO", "NULO?", "DEFAULT");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($table['columns'] as $col) {
        $coluna = $col['column_name'];
        $tipo = $col['data_type'];
        
        // Adicionar tamanho máximo se for varchar/character varying
        if ($col['character_maximum_length']) {
            $tipo .= "(" . $col['character_maximum_length'] . ")";
        }
        
        $nulo = ($col['is_nullable'] === 'YES') ? "SIM" : "NÃO";
        $default = $col['column_default'] ? substr($col['column_default'], 0, 15) : "-";
        
        // Marcar chave primária
        $pk_marker = in_array($coluna, $table['pk']) ? " 🔑" : "";
        
        printf("%-30s | %-25s | %-10s | %-15s%s\n", $coluna, $tipo, $nulo, $default, $pk_marker);
    }
    
    // Exibir chaves primárias
    if (!empty($table['pk'])) {
        echo "\n   🔑 CHAVE PRIMÁRIA: " . implode(", ", $table['pk']) . "\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n\n";
}

echo "✅ Estrutura extraída com sucesso!\n";
?>