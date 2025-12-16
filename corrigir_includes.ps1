# Script para corrigir todos os includes de database.php e function.php
# Atualiza caminhos relativos para caminhos absolutos usando __DIR__

$rootPath = "c:\xampp\htdocs\transfusional"

# Lista de arquivos PHP na raiz (excluindo subpastas)
$phpFiles = Get-ChildItem -Path $rootPath -Filter "*.php" -File

$totalFiles = 0
$updatedFiles = 0

Write-Host "=== Corrigindo includes em arquivos PHP ===" -ForegroundColor Cyan
Write-Host ""

foreach ($file in $phpFiles) {
    $totalFiles++
    $filePath = $file.FullName
    $fileName = $file.Name
    
    # Le o conteudo do arquivo
    $content = Get-Content -Path $filePath -Raw -Encoding UTF8
    $originalContent = $content
    
    # Substitui os includes relativos por absolutos
    $content = $content -replace 'include "database\.php";', 'include __DIR__ . "/database.php";'
    $content = $content -replace "include 'database\.php';", "include __DIR__ . '/database.php';"
    $content = $content -replace 'include "function\.php";', 'include __DIR__ . "/function.php";'
    $content = $content -replace "include 'function\.php';", "include __DIR__ . '/function.php';"
    
    # Verifica se houve mudancas
    if ($content -ne $originalContent) {
        # Salva o arquivo atualizado
        Set-Content -Path $filePath -Value $content -Encoding UTF8 -NoNewline
        Write-Host "[OK] Atualizado: $fileName" -ForegroundColor Green
        $updatedFiles++
    }
    else {
        Write-Host "[ ] Sem alteracoes: $fileName" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "=== Resumo ===" -ForegroundColor Cyan
Write-Host "Total de arquivos verificados: $totalFiles"
Write-Host "Arquivos atualizados: $updatedFiles" -ForegroundColor Green
Write-Host ""
Write-Host "Processo concluido!" -ForegroundColor Green
