# Configuração de Caminhos Base - Transfusional

## Problema Resolvido

Quando a aplicação é movida para uma pasta diferente (ex: de `/transfusional` para `/transfusionalnovo`), os caminhos relativos quebram e causam **Erro 500**.

## Solução Implementada

Foram adicionadas **duas constantes** no arquivo `includes/header.php` que detectam automaticamente o caminho da aplicação:

### 1. `BASE_PATH`
- **Tipo**: Caminho absoluto do sistema de arquivos
- **Exemplo**: `C:\xampp\htdocs\transfusionalnovo`
- **Uso**: Para includes e requires de arquivos PHP

### 2. `BASE_URL`
- **Tipo**: URL completa da aplicação
- **Exemplo**: `http://186.233.152.78/transfusionalnovo/`
- **Uso**: Para links HTML, CSS, JS e imagens

## Como Usar

### Para Includes PHP
```php
// ❌ ERRADO (caminho relativo)
include "database.php";
include "function.php";

// ✅ CORRETO (usando BASE_PATH)
include BASE_PATH . "/database.php";
include BASE_PATH . "/function.php";
```

### Para Links CSS e JavaScript
```html
<!-- ❌ ERRADO (caminho relativo) -->
<link rel="stylesheet" href="css/style.css">
<script src="js/script.js"></script>

<!-- ✅ CORRETO (usando BASE_URL) -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
<script src="<?php echo BASE_URL; ?>js/script.js"></script>
```

### Para Imagens
```html
<!-- ❌ ERRADO -->
<img src="img/logo-HUM.png" alt="Logo">

<!-- ✅ CORRETO -->
<img src="<?php echo BASE_URL; ?>img/logo-HUM.png" alt="Logo">
```

### Para Links de Navegação
```html
<!-- ❌ ERRADO -->
<a href="crud_paciente.php">Pacientes</a>

<!-- ✅ CORRETO -->
<a href="<?php echo BASE_URL; ?>crud_paciente.php">Pacientes</a>
```

## Arquivos que Precisam ser Atualizados

Para que a aplicação funcione em qualquer pasta, os seguintes arquivos precisam ser atualizados:

1. **Todos os arquivos `.php` na raiz** que fazem `include` de:
   - `database.php`
   - `function.php`
   - `includes/header.php`
   - `includes/footer.php`

2. **Arquivos HTML/PHP com links para**:
   - CSS (`css/style.css`, etc.)
   - JavaScript (`js/script.js`, etc.)
   - Imagens (`img/...`)
   - Outros arquivos PHP

## Exemplo de Atualização Completa

### Antes (crud_paciente.php - linhas 1-10)
```php
<?php
include "database.php";
include "function.php";

// ... código ...
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
```

### Depois (crud_paciente.php - linhas 1-10)
```php
<?php
include __DIR__ . "/database.php";
include __DIR__ . "/function.php";

// ... código ...
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
```

## Verificação

Para verificar se as constantes estão funcionando, adicione temporariamente no início de qualquer arquivo PHP:

```php
<?php
include "includes/header.php";
echo "BASE_PATH: " . BASE_PATH . "<br>";
echo "BASE_URL: " . BASE_URL . "<br>";
exit;
?>
```

## Notas Importantes

1. **O `header.php` já define as constantes**, então qualquer arquivo que inclui o header automaticamente tem acesso a `BASE_PATH` e `BASE_URL`.

2. **Para arquivos que NÃO incluem o header** (como `database.php` ou `function.php`), use `__DIR__` para caminhos relativos ao próprio arquivo.

3. **A detecção é automática**, então a aplicação funcionará em:
   - `/transfusional`
   - `/transfusionalnovo`
   - Qualquer outra pasta
   - Localhost ou servidor de produção

## Status Atual

✅ Constantes criadas em `includes/header.php`
⚠️ Arquivos ainda precisam ser atualizados para usar as constantes
