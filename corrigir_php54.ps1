# Script para corrigir sintaxe PHP 5.4 em todos os arquivos crud_*.php
# Corrige: ?? (null coalescing) e [] (array curto)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  CORREÇÃO AUTOMÁTICA - PHP 5.4" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$arquivos = Get-ChildItem -Path "." -Filter "crud_*.php"

$totalArquivos = $arquivos.Count
$arquivosCorrigidos = 0
$totalSubstituicoes = 0

foreach ($arquivo in $arquivos) {
    Write-Host "Processando: $($arquivo.Name)" -ForegroundColor Yellow
    
    $content = Get-Content $arquivo.FullName -Raw -Encoding UTF8
    $contentOriginal = $content
    $substituicoes = 0
    
    # Correção 1: ?? em código PHP (linhas de código)
    # Padrão: $variavel ?? 'valor'
    $pattern1 = '\$(\w+)\s*\?\?\s*([^;]+);'
    $replacement1 = 'isset($$$1) ? $$$1 : $2;'
    $content = $content -replace $pattern1, $replacement1
    $count1 = ([regex]::Matches($contentOriginal, $pattern1)).Count
    $substituicoes += $count1
    
    # Correção 2: ?? em arrays (ex: $_POST['campo'] ?? '')
    # Padrão: $_POST['campo'] ?? 'valor'
    $pattern2 = '\$_(POST|GET|REQUEST|SESSION)\[''(\w+)''\]\s*\?\?\s*([^;]+);'
    $replacement2 = 'isset($$$1[''$2'']) ? $$$1[''$2''] : $3;'
    $content = $content -replace $pattern2, $replacement2
    $count2 = ([regex]::Matches($contentOriginal, $pattern2)).Count
    $substituicoes += $count2
    
    # Correção 3: ?? em arrays associativos (ex: $edit_data['campo'] ?? '')
    # Padrão: $array['campo'] ?? 'valor'
    $pattern3 = '\$(\w+)\[''(\w+)''\]\s*\?\?\s*''''([^'']*)'''''
    $replacement3 = 'isset($$$1[''$2'']) ? $$$1[''$2''] : ''$3'''
    $content = $content -replace $pattern3, $replacement3
    $count3 = ([regex]::Matches($contentOriginal, $pattern3)).Count
    $substituicoes += $count3
    
    # Correção 4: ?? com null
    # Padrão: $variavel ?? null
    $pattern4 = '\$_(POST|GET|REQUEST|SESSION)\[''(\w+)''\]\s*\?\?\s*null'
    $replacement4 = 'isset($$$1[''$2'']) ? $$$1[''$2''] : null'
    $content = $content -replace $pattern4, $replacement4
    $count4 = ([regex]::Matches($contentOriginal, $pattern4)).Count
    $substituicoes += $count4
    
    # Correção 5: Array curto [] para array()
    # Padrão: $variavel = [];
    $pattern5 = '\$(\w+)\s*=\s*\[\];'
    $replacement5 = '$$$1 = array();'
    $content = $content -replace $pattern5, $replacement5
    $count5 = ([regex]::Matches($contentOriginal, $pattern5)).Count
    $substituicoes += $count5
    
    if ($content -ne $contentOriginal) {
        $content | Set-Content $arquivo.FullName -NoNewline -Encoding UTF8
        Write-Host "  ✅ Corrigido: $substituicoes substituições" -ForegroundColor Green
        $arquivosCorrigidos++
        $totalSubstituicoes += $substituicoes
    }
    else {
        Write-Host "  ℹ️  Nenhuma correção necessária" -ForegroundColor Gray
    }
    
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  RESUMO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Total de arquivos processados: $totalArquivos" -ForegroundColor White
Write-Host "Arquivos corrigidos: $arquivosCorrigidos" -ForegroundColor Green
Write-Host "Total de substituições: $totalSubstituicoes" -ForegroundColor Yellow
Write-Host ""
Write-Host "✅ Correção concluída!" -ForegroundColor Green
Write-Host "Os arquivos serão enviados automaticamente via SFTP." -ForegroundColor Cyan
Write-Host ""
