# Publicação do LexStudy

Este projeto é uma aplicação PHP com MySQL. Não é um site estático. Para publicar sem refazer a arquitetura, o alojamento precisa de:

- PHP 8.1 ou superior.
- MySQL ou MariaDB.
- Extensões PHP: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `json`.
- HTTPS.
- Possibilidade de configurar variáveis de ambiente.

## Caminho recomendado agora

Para a fase atual, usa alojamento PHP/MySQL tradicional com painel tipo cPanel/hPanel/Plesk. É o caminho mais simples porque o projeto já está feito em PHP puro.

Render também pode funcionar agora com Docker. A configuração está em `Dockerfile`, `render.yaml` e `RENDER_DEPLOYMENT.md`.

O ponto crítico é a base de dados: o projeto usa MySQL. No Render, mantém uma base MySQL externa e coloca as credenciais nas variáveis de ambiente do serviço.

## Antes de publicar

1. Revoga qualquer chave Gemini que já tenha sido exposta em conversas, prints, `.htaccess` ou GitHub.
2. Gera uma chave nova no Google AI Studio.
3. Cria uma base de dados MySQL chamada `lexstudy` ou outro nome equivalente.
4. Importa `lexstudy.sql` para essa base de dados.
5. Define as variáveis de ambiente no painel do alojamento:

```text
APP_ENV=production
APP_URL=https://teu-dominio.pt
DB_HOST=host-da-base-de-dados
DB_NAME=nome-da-base-de-dados
DB_USER=utilizador-da-base-de-dados
DB_PASS=password-da-base-de-dados
DB_CHARSET=utf8mb4
GEMINI_API_KEY=nova-chave-gemini
GEMINI_MODEL=gemini-2.5-flash
```

Se fores ativar pagamentos:

```text
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PRICE_PLUS_MONTHLY=price_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Ficheiros a subir

Sobe os ficheiros PHP, `assets/`, `manifest.webmanifest`, `sw.js`, `new.css` e `.htaccess`.

Não publiques como ficheiro descarregável:

- `lexstudy.sql`
- `AI_SETUP.md`
- `DEPLOYMENT.md`
- `.htaccess.example`
- qualquer `.env`
- qualquer zip de deploy

O `.htaccess` atual já bloqueia estes ficheiros caso fiquem no servidor por engano, mas o melhor é nem os enviar.

## Teste depois de publicar

Abre:

```text
https://teu-dominio.pt/health.php
```

O ideal é responder:

```json
{
  "ok": true
}
```

Depois testa:

- criar conta;
- entrar e sair;
- abrir Assistente;
- fazer uma Revisão do Dia;
- fazer upload de PDF/TXT em Acórdãos;
- abrir no telemóvel;
- instalar como app no ecrã inicial.

## Pagamentos

Não atives cobrança real antes de:

- testar Stripe em modo test;
- confirmar webhook em `https://teu-dominio.pt/stripe_webhook.php`;
- escrever Termos, Política de Privacidade e aviso claro de que a IA não substitui advogado.

## Próximo passo técnico

Depois de estar online, o caminho profissional é:

1. Criar repositório Git privado.
2. Fazer deploy automático a partir do Git.
3. Separar ficheiros públicos de ficheiros internos.
4. Adicionar backups automáticos da base de dados.
5. Criar logs de erro e monitorização.
6. Preparar API para uma futura app móvel.
