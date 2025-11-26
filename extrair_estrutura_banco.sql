-- Script para extrair estrutura de TODAS as tabelas do banco PostgreSQL
-- Execute este script no seu cliente PostgreSQL (pgAdmin, DBeaver, ou psql)
-- e copie o resultado para o Google Docs

-- ========================================
-- ESTRUTURA COMPLETA DO BANCO DE DADOS
-- ========================================

-- Listar todas as tabelas com suas colunas
SELECT 
    c.table_name as "TABELA",
    c.column_name as "COLUNA",
    c.data_type as "TIPO",
    CASE WHEN c.character_maximum_length IS NOT NULL 
         THEN c.data_type || '(' || c.character_maximum_length || ')' 
         ELSE c.data_type 
    END as "TIPO_COMPLETO",
    CASE WHEN c.is_nullable = 'YES' THEN 'SIM' ELSE 'NÃO' END as "NULO",
    COALESCE(c.column_default, '-') as "DEFAULT"
FROM 
    information_schema.columns c
WHERE 
    c.table_schema = 'public'
ORDER BY 
    c.table_name, c.ordinal_position;

-- ========================================
-- CHAVES PRIMÁRIAS
-- ========================================

SELECT DISTINCT
    t.tablename as "TABELA",
    a.attname as "COLUNA_CHAVE_PRIMÁRIA"
FROM 
    pg_tables t
    INNER JOIN pg_class c ON c.relname = t.tablename
    INNER JOIN pg_index i ON i.indrelid = c.oid
    INNER JOIN pg_attribute a ON a.attrelid = c.oid 
        AND a.attnum = ANY(i.indkey)
WHERE 
    t.schemaname = 'public'
    AND i.indisprimary
ORDER BY 
    t.tablename, a.attname;

-- ========================================
-- CHAVES ESTRANGEIRAS
-- ========================================

SELECT
    tc.table_name as "TABELA",
    kcu.column_name as "COLUNA",
    ccu.table_name as "TABELA_REFERENCIADA",
    ccu.column_name as "COLUNA_REFERENCIADA"
FROM 
    information_schema.table_constraints AS tc
    INNER JOIN information_schema.key_column_usage AS kcu 
        ON tc.constraint_name = kcu.constraint_name
    INNER JOIN information_schema.constraint_column_usage AS ccu 
        ON ccu.constraint_name = tc.constraint_name
WHERE 
    tc.constraint_type = 'FOREIGN KEY'
ORDER BY 
    tc.table_name;

-- ========================================
-- ÍNDICES
-- ========================================

SELECT
    t.tablename as "TABELA",
    i.relname as "ÍNDICE",
    a.attname as "COLUNA"
FROM 
    pg_tables t
    INNER JOIN pg_class c ON c.relname = t.tablename
    INNER JOIN pg_index idx ON idx.indrelid = c.oid
    INNER JOIN pg_class i ON i.oid = idx.indexrelid
    INNER JOIN pg_attribute a ON a.attrelid = c.oid 
        AND a.attnum = ANY(idx.indkey)
WHERE 
    t.schemaname = 'public'
    AND NOT idx.indisprimary
ORDER BY 
    t.tablename, i.relname;
