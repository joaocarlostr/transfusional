# 🔍 Relatório de Análise de Duplicações

**Data:** 12/12/2024  
**Objetivo:** Identificar páginas duplicadas que podem ser removidas

---

## 📊 Resumo Executivo

**Páginas analisadas:** 4 pares (legado vs CRUD)

**Resultado:**
- ✅ **3 páginas podem ser removidas** (com atualizações)
- ⚠️ **1 página precisa verificação adicional**

---

## 📋 Análise Detalhada

### 1. SETORES ✅

**Página Legada:**
- Arquivo: `cadastrar_setor.php`
- Linhas: 400
- Funcionalidades: Criar, Editar, Listar setores
- Usa: `insere.php` e `atualiza.php`

**Página Moderna:**
- Arquivo: `crud_setor.php`
- Linhas: 293
- Funcionalidades: Criar, Editar, Excluir, Listar setores
- Tudo em um arquivo (padrão CRUD)

**Referências encontradas:**
- `function.php` linha 405 - Redirecionamento após gravar
- `function.php` linha 711 - Redirecionamento após atualizar
- Menu: NÃO (usa `crud_setor.php`)

**Conclusão:** ✅ **PODE SER REMOVIDA**
- `crud_setor.php` tem TODAS as funcionalidades
- Inclusive tem função de EXCLUIR (que o legado não tem)
- Mais moderna e completa

**Ações necessárias:**
1. Atualizar `function.php` linhas 405 e 711
2. Mudar redirecionamentos para `crud_setor.php`
3. Excluir `cadastrar_setor.php`

---

### 2. RESPONSÁVEIS ✅

**Página Legada:**
- Arquivo: `cadastrar_responsavel.php`
- Linhas: 394
- Funcionalidades: Criar, Editar, Listar responsáveis
- Usa: `insere.php` e `atualiza.php`

**Página Moderna:**
- Arquivo: `crud_responsavel.php`
- Linhas: 290
- Funcionalidades: Criar, Editar, Excluir, Listar responsáveis
- Tudo em um arquivo (padrão CRUD)

**Referências encontradas:**
- `function.php` linha 416 - Redirecionamento após gravar
- `function.php` linha 723 - Redirecionamento após atualizar
- Menu: NÃO (usa `crud_responsavel.php`)

**Conclusão:** ✅ **PODE SER REMOVIDA**
- `crud_responsavel.php` tem TODAS as funcionalidades
- Inclusive tem função de EXCLUIR (que o legado não tem)
- Mais moderna e completa

**Ações necessárias:**
1. Atualizar `function.php` linhas 416 e 723
2. Mudar redirecionamentos para `crud_responsavel.php`
3. Excluir `cadastrar_responsavel.php`

---

### 3. NÃO CONFORMIDADES ⚠️

**Página Legada:**
- Arquivo: `cadastrar_nao_conformidade.php`
- Linhas: 394
- Funcionalidades: Criar, Editar, Listar não conformidades
- Usa: `insere.php` e `atualiza.php`

**Página Moderna:**
- Arquivo: `crud_nao_conformidade.php`
- Linhas: 313
- Funcionalidades: Criar, Editar, Excluir, Listar não conformidades
- Tudo em um arquivo (padrão CRUD)

**Referências encontradas:**
- `function.php` linha 427 - Redirecionamento após gravar
- `function.php` linha 736 - Redirecionamento após atualizar
- `grid_nao_conformidades.php` linha 86 - Link "Adicionar" ⚠️
- Menu: NÃO (usa `crud_nao_conformidade.php`)

**Conclusão:** ✅ **PODE SER REMOVIDA** (com cuidado)
- `crud_nao_conformidade.php` tem TODAS as funcionalidades
- **MAS** há um link em `grid_nao_conformidades.php` que aponta para a página legada

**Ações necessárias:**
1. Atualizar `function.php` linhas 427 e 736
2. Atualizar `grid_nao_conformidades.php` linha 86
3. Mudar todos os redirecionamentos para `crud_nao_conformidade.php`
4. Excluir `cadastrar_nao_conformidade.php`

---

### 4. PACIENTES ✅

**Página Legada:**
- Arquivo: `cadastro_paciente.php`
- Linhas: 565
- Funcionalidades: Criar pacientes
- Usa: `insere.php`

**Página Moderna:**
- Arquivo: `crud_paciente.php`
- Linhas: 659
- Funcionalidades: Criar, Editar, Excluir, Listar, Buscar pacientes
- Tudo em um arquivo (padrão CRUD completo)

**Referências encontradas:**
- `function.php` linha 240 - Redirecionamento após gravar
- Menu: NÃO (usa `crud_paciente.php`)

**Conclusão:** ✅ **PODE SER REMOVIDA**
- `crud_paciente.php` é MUITO mais completo
- Tem busca, paginação, filtros
- Interface moderna
- Funcionalidades completas de CRUD

**Ações necessárias:**
1. Atualizar `function.php` linha 240
2. Mudar redirecionamento para `crud_paciente.php`
3. Excluir `cadastro_paciente.php`

---

## 📊 Resumo de Ações

### Arquivos a REMOVER (4):
1. ✅ `cadastrar_setor.php` (400 linhas)
2. ✅ `cadastrar_responsavel.php` (394 linhas)
3. ✅ `cadastrar_nao_conformidade.php` (394 linhas)
4. ✅ `cadastro_paciente.php` (565 linhas)

**Total de linhas a remover:** 1.753 linhas de código duplicado! 🎉

---

### Arquivos a ATUALIZAR (2):

**1. function.php (6 linhas)**
- Linha 240: `cadastro_paciente.php` → `crud_paciente.php`
- Linha 405: `cadastrar_setor.php` → `crud_setor.php`
- Linha 416: `cadastrar_responsavel.php` → `crud_responsavel.php`
- Linha 427: `cadastrar_nao_conformidade.php` → `crud_nao_conformidade.php`
- Linha 711: `cadastrar_setor.php` → `crud_setor.php`
- Linha 723: `cadastrar_responsavel.php` → `crud_responsavel.php`
- Linha 736: `cadastrar_nao_conformidade.php` → `crud_nao_conformidade.php`

**2. grid_nao_conformidades.php (1 linha)**
- Linha 86: `cadastrar_nao_conformidade.php` → `crud_nao_conformidade.php`

---

## 🎯 Plano de Execução

### Fase 1: Atualizar Referências
1. Atualizar `function.php` (7 alterações)
2. Atualizar `grid_nao_conformidades.php` (1 alteração)

### Fase 2: Testar
1. Testar criação de setor
2. Testar edição de setor
3. Testar criação de responsável
4. Testar edição de responsável
5. Testar criação de não conformidade
6. Testar edição de não conformidade
7. Testar criação de paciente

### Fase 3: Remover Arquivos Legados
1. Mover para pasta `deprecated/` (backup)
2. Testar sistema completo
3. Se tudo OK, excluir definitivamente

---

## 📈 Benefícios

**Redução de código:**
- ✅ 1.753 linhas removidas
- ✅ 4 arquivos a menos
- ✅ Menos duplicação

**Manutenção:**
- ✅ Uma única forma de fazer cada operação
- ✅ Código mais limpo
- ✅ Mais fácil de manter

**Consistência:**
- ✅ Todas as páginas CRUD seguem o mesmo padrão
- ✅ Interface uniforme
- ✅ Experiência do usuário consistente

---

## ⚠️ Riscos e Mitigações

**Risco 1:** Alguma funcionalidade específica pode estar apenas no legado
**Mitigação:** Testar extensivamente antes de remover

**Risco 2:** Pode haver links diretos que não encontramos
**Mitigação:** Fazer busca global antes de excluir

**Risco 3:** Usuários podem ter bookmarks das páginas antigas
**Mitigação:** Criar redirecionamentos temporários

---

## 🚀 Próximos Passos

**Quer que eu execute a Fase 1 agora?**

Vou atualizar as 8 referências nos arquivos `function.php` e `grid_nao_conformidades.php`.

Depois podemos testar e, se tudo estiver OK, remover os 4 arquivos legados.

**Isso vai:**
- ✅ Eliminar 1.753 linhas de código duplicado
- ✅ Simplificar a manutenção
- ✅ Padronizar o sistema

**Posso começar?**
