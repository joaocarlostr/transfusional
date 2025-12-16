# Implementação de Detecção Automática de Ambiente

## Resumo da Implementação

Foi implementada uma lógica de detecção automática de ambiente que identifica se o sistema está rodando em **Desenvolvimento** ou **Produção**, conectando automaticamente ao banco de dados apropriado.

## Alterações Realizadas

### 1. **header.php** (c:\xampp\htdocs\transfusional\includes\header.php)

Adicionada lógica de detecção automática no início do arquivo:

#### Detecção de Ambiente
```php
// Detecta o ambiente baseado no IP do servidor
$server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
$server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';

// Verifica se está em ambiente de desenvolvimento
if (
    strpos($server_addr, '10.15.0.35') !== false || 
    strpos($server_name, 'localhost') !== false ||
    strpos($server_name, '127.0.0.1') !== false ||
    strpos($server_addr, '127.0.0.1') !== false
) {
    $is_desenvolvimento = true;
}
```

#### Constantes Definidas

**Em Desenvolvimento:**
- `AMBIENTE` = 'DESENVOLVIMENTO'
- `AMBIENTE_ALERTA` = 'AMBIENTE DE DESENVOLVIMENTO - OPERANDO NA BASE: SHI DES (shiteste)'
- `DB_HOST` = '10.15.0.35'

**Em Produção:**
- `AMBIENTE` = 'PRODUCAO'
- `AMBIENTE_ALERTA` = **não definida** (não exibe aviso)
- `DB_HOST` = '10.15.1.77' (com fallback para 186.233.152.78)

**Comuns:**
- `DB_NAME` = 'shiteste'
- `DB_USER` = 'postgres'
- `DB_PASS` = 'systemhum'

### 2. **database.php.template** (Template criado)

Foi criado um arquivo template em `c:\xampp\htdocs\transfusional\database.php.template` que contém:

- Detecção automática de ambiente (caso as constantes não estejam definidas)
- Conexão ao banco de dados usando as constantes
- Tratamento de erros diferenciado por ambiente:
  - **Desenvolvimento**: Mostra detalhes completos do erro
  - **Produção**: Mostra mensagem genérica
- Configuração de charset UTF-8
- Debug opcional (adicione `?debug_db=1` na URL em desenvolvimento)

## Como Funciona

### Fluxo de Detecção

1. **Verifica o IP/hostname do servidor**
   - Se for `10.15.0.35`, `localhost` ou `127.0.0.1` → **Desenvolvimento**
   - Caso contrário → **Produção**

2. **Define as constantes apropriadas**
   - Em desenvolvimento: define `AMBIENTE_ALERTA` para exibir o aviso
   - Em produção: **não** define `AMBIENTE_ALERTA` (aviso não aparece)

3. **Conecta ao banco de dados correto**
   - Desenvolvimento: `10.15.0.35`
   - Produção: `10.15.1.77`

### Exibição do Aviso

No `header.php`, o aviso só é exibido se a constante `AMBIENTE_ALERTA` estiver definida:

```php
<?php if (defined('AMBIENTE_ALERTA')): ?>
    <div style="background-color: #4f54d9ff; color: white; padding: 2px 10px; font-weight: bold; font-size: 12px; border-radius: 4px; margin-left: 20px; white-space: nowrap;">
        <?php echo AMBIENTE_ALERTA; ?>
    </div>
<?php endif; ?>
```

**Resultado:**
- ✅ **Desenvolvimento**: Exibe "AMBIENTE DE DESENVOLVIMENTO - OPERANDO NA BASE: SHI DES (shiteste)"
- ✅ **Produção**: Não exibe nada

## Instruções de Uso

### Para o arquivo database.php

Como o arquivo `database.php` está no `.gitignore`, você precisa:

1. **Copiar o template:**
   ```bash
   copy database.php.template database.php
   ```

2. **Ou criar manualmente** o arquivo `database.php` com o conteúdo do template

3. **Ajustar credenciais** se necessário (já estão configuradas corretamente)

### Testando a Implementação

**Em Desenvolvimento (localhost):**
```
http://localhost/transfusional/
```
- Deve conectar em `10.15.0.35`
- Deve exibir o aviso azul no header

**Em Produção (servidor):**
```
http://10.15.1.77/transfusional/
ou
http://186.233.152.78/transfusional/
```
- Deve conectar em `10.15.1.77`
- **NÃO** deve exibir o aviso

### Debug de Conexão

Para verificar a conexão em desenvolvimento, adicione `?debug_db=1` na URL:
```
http://localhost/transfusional/?debug_db=1
```

Isso exibirá uma mensagem verde confirmando:
- Ambiente detectado
- Host conectado
- Database utilizada

## Vantagens da Implementação

✅ **Automática**: Não precisa alterar código ao mover entre ambientes
✅ **Segura**: Credenciais centralizadas em constantes
✅ **Clara**: Aviso visual apenas em desenvolvimento
✅ **Flexível**: Fácil adicionar novos ambientes (staging, etc.)
✅ **Robusta**: Tratamento de erros diferenciado por ambiente

## Próximos Passos (Opcional)

1. **Adicionar ambiente de Staging** (se necessário)
2. **Implementar log de conexões** em arquivo
3. **Criar arquivo .env** para credenciais (mais seguro)
4. **Adicionar verificação de versão do PHP/PostgreSQL**

## Observações Importantes

- O arquivo `database.php` está no `.gitignore` por segurança (contém credenciais)
- Sempre use o template como base para criar o arquivo em novos ambientes
- Em produção, certifique-se de que o IP `10.15.1.77` está acessível
- Se houver problemas de conexão em produção, o sistema tentará o fallback `186.233.152.78`
