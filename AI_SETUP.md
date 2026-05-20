# Configurar Gemini no LexStudy

## Opção local rápida com XAMPP

1. Cria uma chave no Google AI Studio.
2. Copia `.htaccess.example` para `.htaccess`.
3. Substitui o valor de exemplo:

```apache
SetEnv GEMINI_API_KEY "A_TUA_CHAVE_REAL_AQUI"
SetEnv GEMINI_MODEL "gemini-2.5-flash"
```

4. Reinicia o Apache no painel do XAMPP.
5. Abre `http://localhost/Spector/assistant.php`.
6. No painel lateral, confirma:
   - Chave: aparece mascarada
   - cURL: ativo
   - Modelo: gemini-2.5-flash

## Se aparecer erro de cURL

Abre o `php.ini` usado pelo XAMPP e confirma que a extensão cURL está ativa.

Procura:

```ini
;extension=curl
```

Troca por:

```ini
extension=curl
```

Depois reinicia o Apache.

## Nota de segurança

Não escrevas uma chave real diretamente no `config.php`.
Se uma chave real for exposta numa conversa, num print, ou num repositório, o melhor é revogá-la no Google AI Studio e gerar outra.
