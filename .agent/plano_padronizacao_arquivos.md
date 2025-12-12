# Plano de Padronização de Arquivos do Sistema Transfusional

## Objetivo
Renomear e reorganizar os arquivos do sistema para facilitar a identificação de suas funções através de prefixos padronizados.

## Fase 1: Arquivos de Relatório PDF (ATUAL)

### Arquivos Renomeados ✅
Todos os arquivos `relatorio_*.php` da pasta `relatorios/` foram renomeados para `pdf_*.php` e movidos para a raiz do projeto.

| Arquivo Original | Novo Nome | Status |
|-----------------|-----------|--------|
| relatorios/relatorio_bolsa_devolvida.php | pdf_bolsa_devolvida.php | ✅ Concluído |
| relatorios/relatorio_bolsa_repetida.php | pdf_bolsa_repetida.php | ✅ Concluído |
| relatorios/relatorio_bolsa_reserva.php | pdf_bolsa_reserva.php | ✅ Concluído |
| relatorios/relatorio_bolsas_transfundidas.php | pdf_bolsas_transfundidas.php | ✅ Concluído |
| relatorios/relatorio_hemocomponente.php | pdf_hemocomponente.php | ✅ Concluído |
| relatorios/relatorio_importa_arquivo.php | pdf_importa_arquivo.php | ✅ Concluído |
| relatorios/relatorio_indicador_bolsa_devolvida.php | pdf_indicador_bolsa_devolvida.php | ✅ Concluído |
| relatorios/relatorio_indicador_nao_conformidade.php | pdf_indicador_nao_conformidade.php | ✅ Concluído |
| relatorios/relatorio_indicador_reacao.php | pdf_indicador_reacao.php | ✅ Concluído |
| relatorios/relatorio_indicador_reserva.php | pdf_indicador_reserva.php | ✅ Concluído |
| relatorios/relatorio_nao_conformidade.php | pdf_nao_conformidade.php | ✅ Concluído |
| relatorios/relatorio_paciente.php | pdf_paciente.php | ✅ Concluído |
| relatorios/relatorio_paciente_sem_registro.php | pdf_paciente_sem_registro.php | ✅ Concluído |
| relatorios/relatorio_reacao.php | pdf_reacao.php | ✅ Concluído |
| relatorios/relatorio_reacao_paciente.php | pdf_reacao_paciente.php | ✅ Concluído |
| relatorios/relatorio_setor.php | pdf_setor.php | ✅ Concluído |
| relatorios/relatorio_tipo_sanguineo.php | pdf_tipo_sanguineo.php | ✅ Concluído |

**Arquivos Remanescentes na Pasta `relatorios/`:**
- `exemplo.php` - Arquivo de exemplo (pode ser removido)
- `relatorio_nao_conformidadeBKUP.php` - Backup antigo (pode ser removido)
- `teste_limpo.php` - Arquivo de teste (pode ser removido)

### Arquivos que Referenciam os Relatórios

#### 1. gerar_relatorio.php (Arquivo Principal - JÁ ATUALIZADO)
Este arquivo já está centralizado e usa um sistema de switch/case. Não precisa de alterações pois já é o controlador principal.

#### 2. relatorio.php
- **Linha 418:** Formulário que chama `gerar_relatorio.php`
- **Ação:** Nenhuma alteração necessária (já usa o arquivo centralizado)

### Referências Internas nos Arquivos de Relatório
Alguns arquivos de relatório fazem referência aos seus próprios nomes na geração do PDF:
- `relatorio_bolsa_reserva.php` (linha 170): Nome do arquivo PDF gerado
- `relatorio_bolsa_devolvida.php` (linha 105): Nome do arquivo PDF gerado
- `relatorio_importa_arquivo.php` (linha 348): Nome do arquivo PDF gerado

**Ação:** Atualizar os nomes dos PDFs gerados para refletir a nova nomenclatura.

## Estratégia de Implementação

### Opção A: Migração Gradual (RECOMENDADA)
Como o sistema já possui o arquivo `gerar_relatorio.php` centralizado:
1. ✅ Manter o `gerar_relatorio.php` como está (já implementado)
2. ⏳ Mover os arquivos antigos da pasta `relatorios/` para uma pasta `relatorios/deprecated/`
3. ⏳ Atualizar qualquer referência direta aos arquivos antigos (se houver)
4. ⏳ Após validação completa, excluir a pasta `relatorios/deprecated/`

### Opção B: Renomeação Direta
1. Renomear todos os arquivos de uma vez
2. Atualizar todas as referências
3. Testar cada relatório

## Próximas Fases (Futuro)

### Fase 2: Outros Tipos de Arquivo
Após concluir a padronização dos relatórios, aplicar o mesmo conceito para:

- **ajax_*.php** → Arquivos que processam requisições AJAX
- **crud_*.php** → Arquivos de operações CRUD
- **form_*.php** → Arquivos de formulários
- **api_*.php** → Arquivos de API/endpoints
- **view_*.php** → Arquivos de visualização/páginas
- **helper_*.php** → Arquivos auxiliares/utilitários
- **config_*.php** → Arquivos de configuração

## Benefícios da Padronização

1. **Identificação Rápida:** Ao ver `pdf_*`, sabe-se imediatamente que é um gerador de PDF
2. **Organização:** Arquivos agrupados logicamente por função
3. **Manutenção:** Facilita localizar e modificar arquivos específicos
4. **Onboarding:** Novos desenvolvedores entendem a estrutura mais rapidamente
5. **Busca:** Mais fácil encontrar arquivos usando filtros de nome

## Notas Importantes

- ⚠️ **Backup:** Fazer backup completo antes de iniciar qualquer renomeação
- ⚠️ **Testes:** Testar cada relatório após a migração
- ⚠️ **Documentação:** Atualizar documentação do sistema com a nova nomenclatura
- ⚠️ **Versionamento:** Commitar as mudanças em etapas para facilitar rollback se necessário
