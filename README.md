```markdown
# Transfusional

Projeto: sistema de controle/transfusional (PHP)

Descrição
Este repositório contém o código-fonte do sistema "Transfusional" — uma aplicação em PHP para controle de bolsas, pacientes e relatórios.

Sumário
- Tecnologias: PHP, MySQL (ou outro SGBD), HTML, CSS, JavaScript
- Estrutura: arquivos PHP, pasta `arquivos` com PDFs, `arquivos-csv` com dados, pastas `css`, `js`, `img`, `includes`, `relatorios`, etc.

Atenção — credenciais e dados sensíveis
- Não armazene credenciais reais (usuário/senha do banco) no repositório público.
- Existe um arquivo local `database.php` — este arquivo NÃO deve ser versionado. Mantenha-o no seu disco local e adicione um arquivo de exemplo `database.php.example` no repositório com valores fictícios.

Instalação e configuração (local)
1. Copie o projeto para a pasta do servidor web (ex.: `C:\xampp\htdocs\transfusional`).
2. Crie o arquivo de configuração local:
   - Copie `database.php.example` (ou use o `.env`) para `database.php` e preencha com suas credenciais locais.
3. (Opcional) Instale dependências caso existam (composer, etc.):
   - composer install

Exemplo de database.php.example
```php
<?php
// database.php.example - Exemplo, NUNCA comite as credenciais reais
return [
    'host' => 'localhost',
    'dbname' => 'nome_do_banco',
    'user' => 'seu_usuario',
    'pass' => 'sua_senha',
    'charset' => 'utf8mb4',
];
```

Uso
- Abra no navegador: http://localhost/transfusional (ou conforme sua configuração XAMPP)
- Siga as rotinas de importação/relatórios conforme as páginas PHP disponíveis.

Boas práticas recomendadas
- Mantenha `database.php` no `.gitignore` (já adicionado).
- Crie `database.php.example` com placeholders para facilitar quem clonar o repositório.
- Para variáveis de ambiente, considere usar um `.env` (mantido fora do repositório) e carregar com um loader simples em PHP.
- Se os arquivos CSV/PDF forem sensíveis ou muito grandes, considere removê‑los do repositório e armazená‑los externamente (ex.: storage privado, S3).

Como contribuir / fluxo de trabalho simples
1. Crie uma branch para a feature/fix:
   git checkout -b feature/minha-alteracao
2. Faça commits claros e pequenos:
   git add <arquivos>
   git commit -m "Descrição curta do que foi feito"
3. Suba a branch e abra um Pull Request no GitHub:
   git push origin feature/minha-alteracao

Licença
- [Coloque aqui a licença do projeto, ex.: MIT] (se quiser, eu adiciono um LICENSE.md)

Contato
- Maintainer: joaocarlostr
- E-mail: joaocarlostr@gmail.com
```