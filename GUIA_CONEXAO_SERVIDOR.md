# 🚀 GUIA: Conectar Antigravity ao Servidor de Produção

Este guia mostra como conectar o Antigravity (ou VS Code/Cursor) diretamente ao servidor de produção para desenvolver remotamente.

---

## 📋 **Informações Necessárias**

Antes de começar, você precisa ter:

- 🌐 **IP do Servidor**: `186.233.152.78`
- 👤 **Usuário SSH**: (solicite ao administrador do servidor)
- 🔑 **Senha** ou **Chave SSH**: (solicite ao administrador)
- 📁 **Caminho da aplicação**: Provavelmente `/var/www/html/transfusional` ou `/home/usuario/transfusional`
- 🔌 **Porta SSH**: Geralmente `22` (padrão)

---

## 🎯 **Opção 1: Extensão SFTP para VS Code/Cursor (MAIS FÁCIL)**

### **Passo 1: Instalar a extensão**

1. Abra o VS Code ou Cursor
2. Vá em **Extensions** (Ctrl+Shift+X)
3. Procure por: **"SFTP"** (by Natizyskunk)
4. Clique em **Install**

### **Passo 2: Criar arquivo de configuração**

1. Pressione **Ctrl+Shift+P**
2. Digite: `SFTP: Config`
3. Será criado um arquivo `.vscode/sftp.json`
4. Cole a configuração abaixo:

```json
{
    "name": "Servidor Produção - Transfusional",
    "host": "186.233.152.78",
    "protocol": "sftp",
    "port": 22,
    "username": "SEU_USUARIO_SSH",
    "password": "SUA_SENHA_SSH",
    "remotePath": "/var/www/html/transfusional",
    "uploadOnSave": true,
    "useTempFile": false,
    "openSsh": false,
    "ignore": [
        ".vscode",
        ".git",
        ".DS_Store",
        "node_modules",
        "*.log"
    ],
    "watcher": {
        "files": "**/*",
        "autoUpload": true,
        "autoDelete": false
    }
}
```

### **Passo 3: Ajustar configurações**

Edite os seguintes campos:
- **`username`**: Seu usuário SSH (ex: `root`, `admin`, `usuario`)
- **`password`**: Sua senha SSH
- **`remotePath`**: Caminho correto da aplicação no servidor

### **Passo 4: Conectar e sincronizar**

1. Pressione **Ctrl+Shift+P**
2. Digite: `SFTP: Download Project`
3. Aguarde o download dos arquivos
4. Agora você pode editar e os arquivos serão enviados automaticamente ao salvar!

---

## 🎯 **Opção 2: Remote SSH (MAIS PROFISSIONAL)**

### **Passo 1: Instalar extensão**

1. Abra o VS Code ou Cursor
2. Vá em **Extensions** (Ctrl+Shift+X)
3. Procure por: **"Remote - SSH"** (by Microsoft)
4. Clique em **Install**

### **Passo 2: Configurar conexão SSH**

1. Pressione **Ctrl+Shift+P**
2. Digite: `Remote-SSH: Connect to Host...`
3. Clique em **+ Add New SSH Host**
4. Digite: `ssh usuario@186.233.152.78`
5. Selecione o arquivo de configuração (geralmente `C:\Users\SEU_USUARIO\.ssh\config`)

### **Passo 3: Conectar ao servidor**

1. Pressione **Ctrl+Shift+P**
2. Digite: `Remote-SSH: Connect to Host...`
3. Selecione `186.233.152.78`
4. Digite a senha quando solicitado
5. Aguarde a conexão

### **Passo 4: Abrir a pasta do projeto**

1. Após conectar, clique em **Open Folder**
2. Navegue até: `/var/www/html/transfusional`
3. Clique em **OK**
4. Agora você está editando diretamente no servidor! 🎉

---

## 🎯 **Opção 3: Usar Chave SSH (MAIS SEGURO)**

Se você quiser evitar digitar senha toda vez:

### **Passo 1: Gerar chave SSH (no seu PC)**

Abra o PowerShell e execute:

```powershell
ssh-keygen -t rsa -b 4096 -C "seu_email@exemplo.com"
```

Pressione Enter para aceitar o local padrão (`C:\Users\SEU_USUARIO\.ssh\id_rsa`)

### **Passo 2: Copiar chave pública para o servidor**

```powershell
type $env:USERPROFILE\.ssh\id_rsa.pub | ssh usuario@186.233.152.78 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```

Digite a senha quando solicitado.

### **Passo 3: Testar conexão**

```powershell
ssh usuario@186.233.152.78
```

Agora você deve conectar SEM precisar de senha!

### **Passo 4: Atualizar configuração SFTP**

No arquivo `.vscode/sftp.json`, remova a linha `"password"` e adicione:

```json
{
    "name": "Servidor Produção - Transfusional",
    "host": "186.233.152.78",
    "protocol": "sftp",
    "port": 22,
    "username": "SEU_USUARIO_SSH",
    "privateKeyPath": "C:\\Users\\SEU_USUARIO\\.ssh\\id_rsa",
    "remotePath": "/var/www/html/transfusional",
    "uploadOnSave": true
}
```

---

## 🎯 **Opção 4: FTP/SFTP Client (FileZilla)**

Se preferir usar um cliente FTP tradicional:

### **Configuração FileZilla**

1. Baixe e instale: [FileZilla](https://filezilla-project.org/)
2. Abra o FileZilla
3. Vá em **File > Site Manager**
4. Clique em **New Site**
5. Configure:
   - **Protocol**: SFTP - SSH File Transfer Protocol
   - **Host**: `186.233.152.78`
   - **Port**: `22`
   - **Logon Type**: Normal
   - **User**: Seu usuário SSH
   - **Password**: Sua senha SSH
6. Clique em **Connect**
7. Navegue até `/var/www/html/transfusional`

Agora você pode arrastar e soltar arquivos entre seu PC e o servidor!

---

## ⚙️ **Configuração Recomendada para Desenvolvimento**

### **Arquivo `.vscode/sftp.json` completo**

Crie este arquivo na raiz do projeto:

```json
{
    "name": "Produção - Transfusional HUM",
    "host": "186.233.152.78",
    "protocol": "sftp",
    "port": 22,
    "username": "SEU_USUARIO",
    "password": "SUA_SENHA",
    "remotePath": "/var/www/html/transfusional",
    "uploadOnSave": true,
    "useTempFile": false,
    "openSsh": false,
    "downloadOnOpen": false,
    "ignore": [
        ".vscode/**",
        ".git/**",
        ".DS_Store",
        "node_modules/**",
        "*.log",
        "database.php"
    ],
    "watcher": {
        "files": "**/*.{php,css,js,html}",
        "autoUpload": true,
        "autoDelete": false
    },
    "syncOption": {
        "delete": false,
        "skipCreate": false,
        "ignoreExisting": false,
        "update": true
    }
}
```

**⚠️ IMPORTANTE**: Adicione `.vscode/sftp.json` ao `.gitignore` para não expor suas credenciais!

---

## 🔍 **Descobrir o Caminho Correto da Aplicação**

Se você não sabe onde está a aplicação no servidor:

### **Opção 1: Via SSH**

```bash
ssh usuario@186.233.152.78
find / -name "transfusional" -type d 2>/dev/null
```

### **Opção 2: Caminhos comuns**

Tente estes caminhos:
- `/var/www/html/transfusional`
- `/home/usuario/public_html/transfusional`
- `/usr/share/nginx/html/transfusional`
- `/opt/lampp/htdocs/transfusional`

---

## 🛡️ **Boas Práticas de Segurança**

1. ✅ **Use chave SSH** em vez de senha
2. ✅ **Adicione `.vscode/sftp.json` ao `.gitignore`**
3. ✅ **Não compartilhe suas credenciais**
4. ✅ **Faça backup antes de editar em produção**
5. ✅ **Teste em desenvolvimento primeiro**
6. ✅ **Use Git para versionamento**

---

## 🚨 **Troubleshooting**

### **Erro: "Connection refused"**
- Verifique se a porta SSH está correta (geralmente 22)
- Verifique se o firewall permite conexões SSH
- Confirme o IP do servidor

### **Erro: "Permission denied"**
- Verifique usuário e senha
- Confirme se o usuário tem permissão SSH
- Tente usar `sudo` se necessário

### **Erro: "No such file or directory"**
- Verifique o caminho remoto (`remotePath`)
- Use o comando `find` para localizar a pasta

### **Arquivos não estão sendo enviados**
- Verifique se `uploadOnSave` está `true`
- Pressione Ctrl+Shift+P e execute `SFTP: Upload`
- Verifique permissões de escrita no servidor

---

## 📝 **Comandos Úteis**

### **Comandos SFTP no VS Code**

- `Ctrl+Shift+P` → `SFTP: Upload` - Enviar arquivo atual
- `Ctrl+Shift+P` → `SFTP: Download` - Baixar arquivo atual
- `Ctrl+Shift+P` → `SFTP: Sync Local -> Remote` - Sincronizar tudo
- `Ctrl+Shift+P` → `SFTP: Sync Remote -> Local` - Baixar tudo
- `Ctrl+Shift+P` → `SFTP: List` - Ver arquivos remotos

---

## 🎯 **Workflow Recomendado**

1. **Desenvolvimento Local** (c:\xampp\htdocs\transfusional)
   - Desenvolva e teste localmente
   - Commit no Git

2. **Sincronizar com Produção**
   - Use SFTP para enviar arquivos
   - Ou use Git pull no servidor

3. **Testar em Produção**
   - Acesse http://186.233.152.78/transfusional/
   - Verifique se tudo funciona

---

## 📞 **Precisa de Ajuda?**

Se você não tem as credenciais SSH, entre em contato com:
- 👨‍💼 Administrador do servidor
- 🏢 Departamento de TI do HUM
- 🔧 Responsável pela infraestrutura

Eles podem fornecer:
- Usuário e senha SSH
- Caminho correto da aplicação
- Permissões necessárias

---

## ✅ **Checklist de Configuração**

- [ ] Extensão SFTP ou Remote-SSH instalada
- [ ] Credenciais SSH obtidas
- [ ] Arquivo `.vscode/sftp.json` configurado
- [ ] Caminho remoto correto identificado
- [ ] Conexão testada com sucesso
- [ ] Upload automático funcionando
- [ ] `.vscode/sftp.json` adicionado ao `.gitignore`

---

**Pronto!** Agora você pode desenvolver diretamente no servidor de produção! 🚀

Lembre-se: **Sempre faça backup antes de editar em produção!** ⚠️
