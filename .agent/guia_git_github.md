# 📘 Guia Rápido: Git e GitHub para Iniciantes

## 🎯 O que o Git faz?

O Git rastreia 3 tipos de mudanças:
1. **Arquivos Novos** (Untracked) - Arquivos que você criou
2. **Arquivos Modificados** (Modified) - Arquivos que você editou
3. **Arquivos Deletados** (Deleted) - Arquivos que você removeu/renomeou

---

## 📊 Status Atual do Seu Projeto

### ✅ Arquivos já preparados (staged):
- README.md (novo)
- database.php.example (novo)
- relatorio.php (modificado)

### 📝 Arquivos modificados (não preparados):
- cadastrar_nao_conformidade.php
- cadastrar_responsavel.php
- cadastrar_setor.php
- cadastro_paciente.php
- css/style.css
- exclusoes.php
- extrair_estrutura.php
- function.php
- gerar_relatorio.php
- includes/header.php
- index.php
- info_gerais.php
- libraries/fpdf.php
- relatorio.php

### ❌ Arquivos deletados (da pasta relatorios/):
- relatorio_bolsa.php
- relatorio_bolsa_devolvida.php
- relatorio_bolsa_repetida.php
- relatorio_bolsa_reserva.php
- relatorio_hemocomponente.php
- relatorio_importa_arquivo.php
- relatorio_indicador_bolsa_devolvida.php
- relatorio_indicador_nao_conformidade.php
- relatorio_indicador_reacao.php
- relatorio_indicador_reserva.php
- relatorio_nao_conformidade.php
- relatorio_nao_conformidadeBKUP.php
- relatorio_nao_conformidade_NOVO.php
- relatorio_paciente.php
- relatorio_paciente_sem_registro.php
- relatorio_reacao.php
- relatorio_reacao_paciente.php
- relatorio_setor.php
- relatorio_tipo_sanguineo.php

### ✨ Arquivos novos (não rastreados):
- .agent/ (pasta com documentação)
- ajax_search.php
- crud_*.php (vários arquivos)
- css/flatpickr-custom.css
- debug_ajax.php
- editar_nao_conformidade.php
- excluir_nao_conformidade.php
- grid_nao_conformidades.php
- js/flatpickr-init.js
- **pdf_*.php (17 arquivos renomeados!)** ⭐

---

## 🚀 Comandos Git - Passo a Passo

### **Passo 1: Adicionar TODAS as mudanças**
```bash
git add .
```
**O que faz:** Prepara TODOS os arquivos (novos, modificados e deletados) para o commit

**OU** adicionar arquivos específicos:
```bash
git add pdf_*.php                    # Adiciona todos os PDFs
git add .agent/                      # Adiciona a pasta de documentação
git add relatorios/                  # Registra as deleções
```

---

### **Passo 2: Verificar o que será commitado**
```bash
git status
```
**O que faz:** Mostra quais arquivos estão prontos para commit (em verde)

---

### **Passo 3: Fazer o Commit (salvar localmente)**
```bash
git commit -m "Padronização: Renomear arquivos relatorio_*.php para pdf_*.php"
```
**O que faz:** Salva as mudanças localmente com uma mensagem descritiva

**Dica:** Mensagens de commit devem ser:
- Claras e descritivas
- Em português ou inglês (seja consistente)
- Começar com verbo no infinitivo ou imperativo
- Exemplos:
  - ✅ "Adicionar funcionalidade de busca AJAX"
  - ✅ "Corrigir erro no relatório de bolsas"
  - ✅ "Refatorar: Padronizar nomenclatura de arquivos PDF"
  - ❌ "mudanças" (muito vago)
  - ❌ "asdfasdf" (sem sentido)

---

### **Passo 4: Enviar para o GitHub**
```bash
git push origin main
```
**O que faz:** Envia suas mudanças locais para o repositório remoto no GitHub

**Nota:** Se você estiver na branch `master` ao invés de `main`, use:
```bash
git push origin master
```

---

## 🔄 Fluxo Completo (Resumo)

```bash
# 1. Ver o que mudou
git status

# 2. Adicionar tudo
git add .

# 3. Commitar com mensagem
git commit -m "Sua mensagem aqui"

# 4. Enviar para GitHub
git push origin main
```

---

## 📋 Comandos Úteis Extras

### Ver histórico de commits:
```bash
git log
git log --oneline  # Versão resumida
```

### Desfazer mudanças não commitadas:
```bash
git restore arquivo.php  # Desfaz mudanças em um arquivo específico
git restore .            # Desfaz TODAS as mudanças não commitadas
```

### Ver diferenças:
```bash
git diff                 # Ver mudanças não preparadas
git diff --staged        # Ver mudanças preparadas para commit
```

### Remover arquivo do staging (antes do commit):
```bash
git restore --staged arquivo.php
```

### Ver branches:
```bash
git branch              # Ver branches locais
git branch -a           # Ver todas as branches (local + remoto)
```

---

## 🎯 Para o Seu Caso Específico

Como você renomeou arquivos (deletou os antigos e criou novos), o Git precisa saber disso:

```bash
# Opção 1: Adicionar tudo de uma vez (RECOMENDADO)
git add .
git commit -m "Refatoração: Padronizar nomenclatura de arquivos de relatório

- Renomear relatorio_*.php para pdf_*.php
- Mover arquivos da pasta relatorios/ para raiz
- Atualizar nomes dos PDFs gerados internamente
- Adicionar documentação de padronização em .agent/
- Total: 17 arquivos renomeados e reorganizados"

git push origin main
```

```bash
# Opção 2: Adicionar por etapas (mais controle)
# Etapa 1: Registrar deleções
git add relatorios/

# Etapa 2: Adicionar novos arquivos PDF
git add pdf_*.php

# Etapa 3: Adicionar documentação
git add .agent/

# Etapa 4: Adicionar outros arquivos modificados
git add gerar_relatorio.php relatorio.php

# Etapa 5: Commit
git commit -m "Padronização de arquivos de relatório"

# Etapa 6: Push
git push origin main
```

---

## ⚠️ Dicas Importantes

1. **Sempre faça `git status` antes de commitar** para ver o que será enviado
2. **Commits pequenos e frequentes** são melhores que commits gigantes
3. **Mensagens descritivas** ajudam você e sua equipe no futuro
4. **Teste antes de fazer push** - certifique-se que o código funciona
5. **Pull antes de Push** - se trabalha em equipe, sempre faça `git pull` antes de `git push`

---

## 🆘 Problemas Comuns

### "Your branch is behind..."
```bash
git pull origin main  # Baixa mudanças do GitHub
git push origin main  # Depois envia suas mudanças
```

### "Merge conflict"
```bash
# Abra os arquivos em conflito
# Resolva manualmente (escolha qual versão manter)
git add .
git commit -m "Resolver conflitos de merge"
git push origin main
```

### Esqueci de adicionar um arquivo no último commit
```bash
git add arquivo_esquecido.php
git commit --amend --no-edit  # Adiciona ao último commit
git push origin main --force  # Força o push (cuidado!)
```

---

## 📚 Recursos para Aprender Mais

- [Git - Guia Prático](https://rogerdudler.github.io/git-guide/index.pt_BR.html)
- [GitHub Docs em Português](https://docs.github.com/pt)
- [Git Cheat Sheet](https://training.github.com/downloads/pt_BR/github-git-cheat-sheet/)

---

**Lembre-se:** Git parece complicado no início, mas com a prática fica natural! 🚀
