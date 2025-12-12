# Análise de Arquivos Legados - CRUD

**Data:** 2025-12-12  
**Objetivo:** Identificar arquivos legados que podem ser removidos após implementação dos CRUDs modernos

---

## 📊 RESUMO EXECUTIVO

### Arquivos Encontrados:
- **cadastrar_*.php:** 4 arquivos
- **buscar_*.php:** 2 arquivos
- **editar_*.php:** 1 arquivo
- **exclui_*.php:** 0 arquivos

### Status:
- ✅ **PODEM SER REMOVIDOS:** 3 arquivos
- ⚠️ **AINDA EM USO:** 4 arquivos
- **TOTAL ANALISADO:** 7 arquivos

---

## ✅ ARQUIVOS QUE PODEM SER REMOVIDOS (3)

### 1. cadastrar_setor.php
- **Status:** ✅ PODE SER REMOVIDO
- **Substituído por:** crud_setor.php
- **Referências encontradas:** 0
- **Justificativa:** Nenhuma referência no código. CRUD moderno implementado e funcionando.

### 2. cadastrar_responsavel.php
- **Status:** ✅ PODE SER REMOVIDO
- **Substituído por:** crud_responsavel.php
- **Referências encontradas:** 0
- **Justificativa:** Nenhuma referência no código. CRUD moderno implementado e funcionando.

### 3. cadastrar_nao_conformidade.php
- **Status:** ✅ PODE SER REMOVIDO
- **Substituído por:** crud_nao_conformidade.php
- **Referências encontradas:** 0
- **Justificativa:** Nenhuma referência no código. CRUD moderno implementado e funcionando.

---

## ⚠️ ARQUIVOS AINDA EM USO (4)

### 1. editar_nao_conformidade.php
- **Status:** ⚠️ AINDA EM USO
- **Referências:** 1
- **Localização:** grid_nao_conformidades.php (linha 110)
- **Ação necessária:** Atualizar grid_nao_conformidades.php para usar crud_nao_conformidade.php
- **Código atual:**
```php
<a href='editar_nao_conformidade.php?id={$row['id_nao_conformidade']}' class='action-btn btn-edit'>
```
- **Deve ser alterado para:**
```php
<button onclick='openEditModal(...)' class='action-btn btn-edit'>
```

### 2. buscar_paciente.php
- **Status:** ⚠️ AINDA EM USO
- **Referências:** 5
- **Localizações:**
  - unificar.php (linha 88)
  - perfil_paciente.php (linha 289)
  - includes/header.php (linhas 107, 114)
  - buscar_paciente.php (linha 124 - auto-referência)
- **Observação:** Página de busca/listagem de pacientes. NÃO é CRUD, é funcionalidade diferente.
- **Ação:** MANTER - Não é redundante com crud_paciente.php

### 3. buscar_bolsa.php
- **Status:** ⚠️ AINDA EM USO
- **Referências:** 2
- **Localizações:**
  - includes/header.php (linha 115)
  - buscar_bolsa.php (linha 77 - auto-referência)
- **Observação:** Página de busca/listagem de bolsas.
- **Ação:** MANTER - Funcionalidade de busca, não CRUD

### 4. cadastrar_bolsa.php
- **Status:** ⚠️ AINDA EM USO
- **Referências:** 11
- **Localizações:**
  - perfil_paciente.php (linha 270)
  - function.php (linhas 344, 656, 850, 866)
  - exclui.php (linhas 51, 54)
  - controle.php (linha 16)
  - cadastrar_bolsa.php (linhas 27, 351)
  - bolsas_devolvidas.php (linha 34)
- **Observação:** Muito utilizado. Cadastro de bolsas é diferente de CRUD de entidades.
- **Ação:** MANTER - Funcionalidade específica de bolsas

---

## 📋 PLANO DE AÇÃO

### FASE 1 - Correção de Referência (IMEDIATO)
1. ✅ Atualizar grid_nao_conformidades.php
   - Remover link para editar_nao_conformidade.php
   - Implementar botão com onclick para modal

### FASE 2 - Remoção de Arquivos Legados (APÓS FASE 1)
1. ✅ Remover cadastrar_setor.php
2. ✅ Remover cadastrar_responsavel.php
3. ✅ Remover cadastrar_nao_conformidade.php
4. ✅ Remover editar_nao_conformidade.php (após correção)

### FASE 3 - Manter Arquivos Funcionais
1. ✅ MANTER buscar_paciente.php (funcionalidade de busca)
2. ✅ MANTER buscar_bolsa.php (funcionalidade de busca)
3. ✅ MANTER cadastrar_bolsa.php (funcionalidade específica)

---

## 📊 IMPACTO DA REMOÇÃO

### Arquivos a Remover: 4
- cadastrar_setor.php
- cadastrar_responsavel.php
- cadastrar_nao_conformidade.php
- editar_nao_conformidade.php

### Benefícios:
- ✅ Redução de 4 arquivos na estrutura
- ✅ Menos confusão sobre qual arquivo usar
- ✅ Código mais limpo e organizado
- ✅ Manutenção mais fácil

### Riscos:
- ⚠️ Baixo - Apenas 1 referência a ser corrigida
- ⚠️ Arquivos sem referências podem ser removidos com segurança

---

## ✅ RECOMENDAÇÃO FINAL

**PODE PROSSEGUIR COM A REMOÇÃO** após:
1. Corrigir grid_nao_conformidades.php (1 arquivo)
2. Testar a edição de não conformidades
3. Remover os 4 arquivos legados

**Arquivos a MANTER:**
- buscar_paciente.php (funcionalidade de busca)
- buscar_bolsa.php (funcionalidade de busca)
- cadastrar_bolsa.php (funcionalidade específica de bolsas)
