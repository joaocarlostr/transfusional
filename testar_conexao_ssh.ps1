# Script de Teste de Conexão SSH
# Este script testa a conexão com o servidor de produção

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TESTE DE CONEXÃO SSH - SERVIDOR PRODUÇÃO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configurações
$servidor = "186.233.152.78"
$porta = 22

Write-Host "Servidor: $servidor" -ForegroundColor Yellow
Write-Host "Porta: $porta" -ForegroundColor Yellow
Write-Host ""

# Solicitar credenciais
Write-Host "Digite suas credenciais SSH:" -ForegroundColor Green
$usuario = Read-Host "Usuário SSH"

if ([string]::IsNullOrWhiteSpace($usuario)) {
    Write-Host "❌ Usuário não pode estar vazio!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Testando conexão com $usuario@$servidor..." -ForegroundColor Yellow
Write-Host ""

# Testar se o SSH está disponível
$sshCommand = Get-Command ssh -ErrorAction SilentlyContinue

if ($null -eq $sshCommand) {
    Write-Host "❌ ERRO: SSH não está instalado ou não está no PATH!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Para instalar o SSH no Windows:" -ForegroundColor Yellow
    Write-Host "1. Abra 'Configurações' > 'Aplicativos' > 'Recursos Opcionais'" -ForegroundColor White
    Write-Host "2. Clique em 'Adicionar um recurso'" -ForegroundColor White
    Write-Host "3. Procure por 'Cliente OpenSSH'" -ForegroundColor White
    Write-Host "4. Clique em 'Instalar'" -ForegroundColor White
    Write-Host ""
    Write-Host "Ou instale via PowerShell (como Administrador):" -ForegroundColor Yellow
    Write-Host "Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0" -ForegroundColor Cyan
    exit 1
}

Write-Host "✅ SSH está instalado!" -ForegroundColor Green
Write-Host ""

# Testar conectividade de rede
Write-Host "Testando conectividade de rede..." -ForegroundColor Yellow
$ping = Test-Connection -ComputerName $servidor -Count 2 -Quiet

if ($ping) {
    Write-Host "✅ Servidor está acessível!" -ForegroundColor Green
} else {
    Write-Host "⚠️  AVISO: Não foi possível fazer ping no servidor" -ForegroundColor Yellow
    Write-Host "   Isso pode ser normal se o servidor bloqueia ICMP" -ForegroundColor Gray
}

Write-Host ""

# Testar porta SSH
Write-Host "Testando porta SSH ($porta)..." -ForegroundColor Yellow
$tcpClient = New-Object System.Net.Sockets.TcpClient
try {
    $tcpClient.Connect($servidor, $porta)
    if ($tcpClient.Connected) {
        Write-Host "✅ Porta SSH está aberta!" -ForegroundColor Green
        $tcpClient.Close()
    }
} catch {
    Write-Host "❌ ERRO: Porta SSH não está acessível!" -ForegroundColor Red
    Write-Host "   Verifique se o firewall está bloqueando a conexão" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  TENTANDO CONECTAR VIA SSH" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Digite a senha quando solicitado..." -ForegroundColor Yellow
Write-Host "Pressione Ctrl+D ou digite 'exit' para sair após conectar" -ForegroundColor Gray
Write-Host ""

# Tentar conexão SSH
ssh "$usuario@$servidor"

$exitCode = $LASTEXITCODE

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan

if ($exitCode -eq 0) {
    Write-Host "✅ CONEXÃO SSH FUNCIONOU!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Próximos passos:" -ForegroundColor Yellow
    Write-Host "1. Configure o arquivo .vscode/sftp.json com suas credenciais" -ForegroundColor White
    Write-Host "2. Instale a extensão 'SFTP' no VS Code/Cursor" -ForegroundColor White
    Write-Host "3. Use Ctrl+Shift+P > 'SFTP: Download Project' para sincronizar" -ForegroundColor White
    Write-Host ""
    Write-Host "Consulte o arquivo GUIA_CONEXAO_SERVIDOR.md para mais detalhes" -ForegroundColor Cyan
} else {
    Write-Host "❌ ERRO NA CONEXÃO SSH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Possíveis causas:" -ForegroundColor Yellow
    Write-Host "- Usuário ou senha incorretos" -ForegroundColor White
    Write-Host "- Usuário não tem permissão SSH" -ForegroundColor White
    Write-Host "- Servidor não permite autenticação por senha" -ForegroundColor White
    Write-Host "- Firewall bloqueando a conexão" -ForegroundColor White
    Write-Host ""
    Write-Host "Entre em contato com o administrador do servidor" -ForegroundColor Cyan
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
