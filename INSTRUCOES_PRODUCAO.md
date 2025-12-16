# 🚀 INSTRUÇÕES PARA CORRIGIR ERRO 500 EM PRODUÇÃO

## ❌ Problema Atual
Ao acessar `http://186.233.152.78/transfusional/crud_paciente.php` você recebe:
```
Erro 500 Internal Server Error
```

## 🔍 Causa do Problema
O arquivo **`database.php`** não existe no servidor de produção porque:
- Ele está listado no `.gitignore` (linha 15)
- Quando você fez upload/git pull, este arquivo não foi copiado
- Sem este arquivo, o PHP não consegue conectar ao banco de dados

## ✅ SOLUÇÃO - Passo a Passo

### Opção 1: Criar o arquivo manualmente no servidor (RECOMENDADO)

1. **Acesse o servidor de produção** via FTP, SSH ou painel de controle

2. **Navegue até a pasta da aplicação**:
   ```
   /transfusional/
   ```

3. **Crie um novo arquivo chamado `database.php`**

4. **Cole o seguinte conteúdo** (copie EXATAMENTE como está):

```php
<?php
/**
 * Arquivo de Configuração do Banco de Dados - PRODUÇÃO
 * 
 * Este arquivo gerencia a conexão com o banco de dados PostgreSQL,
 * detectando automaticamente o ambiente (Desenvolvimento ou Produção)
 */

// Se as constantes de ambiente não estiverem definidas, define valores padrão
if (!defined('DB_HOST')) {
    // Detecta o ambiente baseado no IP do servidor
    $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
    $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
    
    $is_desenvolvimento = false;
    
    // Verifica se está em ambiente de desenvolvimento
    if (
        strpos($server_addr, '10.15.0.35') !== false || 
        strpos($server_name, 'localhost') !== false ||
        strpos($server_name, '127.0.0.1') !== false ||
        strpos($server_addr, '127.0.0.1') !== false
    ) {
        $is_desenvolvimento = true;
    }
    
    // Define constantes de ambiente
    if ($is_desenvolvimento) {
        define('AMBIENTE', 'DESENVOLVIMENTO');
        define('DB_HOST', '10.15.0.35');
    } else {
        define('AMBIENTE', 'PRODUCAO');
        define('DB_HOST', '10.15.1.77'); // IP principal de produção
        // Fallback: 186.233.152.78
    }
    
    // Configurações comuns do banco de dados
    define('DB_NAME', 'shiteste');
    define('DB_USER', 'postgres');
    define('DB_PASS', 'systemhum');
}

// String de conexão
$connection_string = sprintf(
    "host=%s dbname=%s user=%s password=%s",
    DB_HOST,
    DB_NAME,
    DB_USER,
    DB_PASS
);

// Tenta estabelecer a conexão
$conexao = @pg_connect($connection_string);

// Verifica se a conexão foi bem-sucedida
if (!$conexao) {
    // Em desenvolvimento, mostra erro detalhado
    if (AMBIENTE === 'DESENVOLVIMENTO') {
        $error_msg = "Erro ao conectar ao banco de dados PostgreSQL.<br>";
        $error_msg .= "Host: " . DB_HOST . "<br>";
        $error_msg .= "Database: " . DB_NAME . "<br>";
        $error_msg .= "Ambiente: " . AMBIENTE . "<br>";
        $error_msg .= "Erro: " . pg_last_error();
        
        die("<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
                <h3>Erro de Conexão com o Banco de Dados</h3>
                <p>{$error_msg}</p>
            </div>");
    } else {
        // Em produção, mostra erro genérico
        die("<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>
                <h3>Erro de Conexão</h3>
                <p>Não foi possível conectar ao banco de dados. Entre em contato com o administrador do sistema.</p>
            </div>");
    }
}

// Define o charset da conexão para UTF-8
pg_set_client_encoding($conexao, 'UTF8');

// Opcional: Log de conexão bem-sucedida (apenas em desenvolvimento)
if (AMBIENTE === 'DESENVOLVIMENTO' && isset($_GET['debug_db'])) {
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px;'>
            Conexão estabelecida com sucesso!<br>
            Ambiente: " . AMBIENTE . "<br>
            Host: " . DB_HOST . "<br>
            Database: " . DB_NAME . "
          </div>";
}
```

5. **Salve o arquivo** com encoding **UTF-8 sem BOM**

6. **Teste novamente**:
   ```
   http://186.233.152.78/transfusional/crud_paciente.php
   ```

### Opção 2: Copiar do template (Alternativa)

Se você tiver acesso via linha de comando no servidor:

```bash
cd /caminho/para/transfusional
cp database.php.template database.php
```

---

## 🔧 Verificações Adicionais

Se após criar o `database.php` ainda houver erro, verifique:

### 1. Permissões do arquivo
```bash
chmod 644 database.php
```

### 2. Logs de erro do Apache/PHP
Procure por arquivos de log em:
- `/var/log/apache2/error.log`
- `/var/log/php/error.log`
- Ou use o painel de controle do servidor

### 3. Teste a conexão com o banco
Crie um arquivo `teste_conexao.php` temporário:

```php
<?php
include 'database.php';

if ($conexao) {
    echo "✅ Conexão com banco de dados OK!<br>";
    echo "Ambiente: " . AMBIENTE . "<br>";
    echo "Host: " . DB_HOST . "<br>";
} else {
    echo "❌ Erro na conexão!<br>";
    echo pg_last_error();
}
?>
```

Acesse: `http://186.233.152.78/transfusional/teste_conexao.php`

**IMPORTANTE**: Delete este arquivo após o teste!

---

## 📋 Checklist Final

- [ ] Arquivo `database.php` criado no servidor
- [ ] Conteúdo copiado corretamente
- [ ] Arquivo salvo com UTF-8
- [ ] Permissões corretas (644)
- [ ] Testado `crud_paciente.php`
- [ ] Erro 500 resolvido ✅

---

## 🆘 Se ainda não funcionar

Me informe qual mensagem de erro aparece e podemos investigar:
1. Logs de erro do servidor
2. Configuração do PHP
3. Extensão PostgreSQL habilitada
4. Firewall/conexão com banco de dados

---

## 📝 Notas Importantes

- ⚠️ **NUNCA** faça commit do arquivo `database.php` no Git (ele já está no .gitignore)
- ✅ Este arquivo contém credenciais sensíveis
- ✅ Cada ambiente (dev/produção) deve ter seu próprio `database.php`
- ✅ O arquivo `database.php.template` serve apenas como modelo
