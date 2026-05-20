# Publicar o LexStudy no Render

Este projeto já está preparado para correr no Render com Docker. Continua a ser uma aplicação PHP + MySQL, por isso precisas de uma base de dados MySQL externa com o `lexstudy.sql` importado.

## O que foi preparado

- `Dockerfile` para correr PHP 8.3 com Apache.
- `.dockerignore` para não publicar `.env`, ficheiros SQL, zips e ficheiros locais.
- `render.yaml` para criar o serviço web no Render.
- `.env.example` como lista segura das variáveis necessárias.
- `render-health.php` para o Render verificar se o servidor está vivo.

## O que ainda tens de fazer

1. Criar um repositório Git privado no GitHub, GitLab ou Bitbucket.
2. Enviar este projeto para esse repositório.
3. Criar uma base MySQL externa.
4. Importar `lexstudy.sql` nessa base.
5. No Render, workspace `NOBVT`, criar um Blueprint a partir do repositório.
6. Preencher as variáveis de ambiente no Render.

Não publiques o `.env`. Copia os valores para o painel do Render, mas deixa o ficheiro local fora do Git.

## Criar o repositório

Na pasta do projeto:

```bash
git init
git add .
git commit -m "Prepare LexStudy for Render"
git branch -M main
git remote add origin https://github.com/TEU_UTILIZADOR/lexstudy.git
git push -u origin main
```

Antes do `git push`, confirma que `.env` e `lexstudy.sql` não aparecem no commit:

```bash
git status --short
```

Se aparecer `.env`, para imediatamente e corrige o `.gitignore`.

## Variáveis de ambiente no Render

Obrigatórias:

```text
APP_ENV=production
APP_URL=https://o-teu-servico.onrender.com
DB_HOST=host-da-base-mysql
DB_NAME=nome-da-base
DB_USER=utilizador
DB_PASS=password
DB_CHARSET=utf8mb4
GEMINI_API_KEY=chave-da-api
GEMINI_MODEL=gemini-2.5-flash
```

Pagamentos, se fores ativar Stripe:

```text
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PRICE_PLUS_MONTHLY=price_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Testes depois do deploy

Abre:

```text
https://o-teu-servico.onrender.com/render-health.php
```

Deve responder `ok: true`.

Depois abre:

```text
https://o-teu-servico.onrender.com/health.php
```

Este segundo teste só fica `ok: true` quando a base de dados estiver ligada e o schema estiver importado.

## Importar a base de dados

Depois de criares um MySQL acessível pelo serviço web, define no Render:

```text
DB_HOST=mysql:3306
DB_NAME=lexstudy
DB_USER=lexstudy
DB_PASS=password-segura
DB_CHARSET=utf8mb4
```

Depois abre a Shell do serviço `LexStudy` no Render e executa:

```bash
php scripts/import_database.php
```

O script importa `database/schema.sql` para a base indicada nas variáveis de ambiente. Não uses este comando sem confirmar que estás ligado à base de produção correta.

## Nota direta

O Render suporta MySQL como serviço privado com Docker e disco persistente, mas isso pode ter custo. O site PHP fica online no Render, mas os dados precisam desse MySQL configurado. Migrar para PostgreSQL seria outro projeto, porque o código usa SQL específico de MySQL.
