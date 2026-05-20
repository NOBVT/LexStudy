<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script só pode ser executado pela linha de comandos.\n");
    exit(1);
}

$root = dirname(__DIR__);
$schemaPath = $root . '/database/schema.sql';

if (!is_readable($schemaPath)) {
    fwrite(STDERR, "Schema não encontrado em: {$schemaPath}\n");
    exit(1);
}

function envOrFail(string $key): string
{
    $value = getenv($key);
    if ($value === false || trim($value) === '') {
        fwrite(STDERR, "Variável obrigatória em falta: {$key}\n");
        exit(1);
    }

    return trim($value);
}

function splitSqlStatements(string $sql): array
{
    $statements = [];
    $current = '';
    $quote = null;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $sql[$i + 1] ?? '';

        if ($quote === null && $char === '-' && $next === '-') {
            while ($i < $length && $sql[$i] !== "\n") {
                $i++;
            }
            continue;
        }

        if ($quote === null && $char === '#') {
            while ($i < $length && $sql[$i] !== "\n") {
                $i++;
            }
            continue;
        }

        if ($quote === null && $char === '/' && $next === '*') {
            $i += 2;
            while ($i < $length && !($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) {
                $i++;
            }
            $i++;
            continue;
        }

        if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
            if ($quote === $char) {
                $quote = null;
            } elseif ($quote === null) {
                $quote = $char;
            }
        }

        if ($char === ';' && $quote === null) {
            $statement = trim($current);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $current = '';
            continue;
        }

        $current .= $char;
    }

    $statement = trim($current);
    if ($statement !== '') {
        $statements[] = $statement;
    }

    return $statements;
}

$host = envOrFail('DB_HOST');
$port = getenv('DB_PORT') ?: '3306';
$name = envOrFail('DB_NAME');
$user = envOrFail('DB_USER');
$pass = getenv('DB_PASS');
$pass = $pass === false ? '' : $pass;
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$sslMode = strtolower(getenv('DB_SSL_MODE') ?: '');
$sslCa = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';

$dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

if ($sslMode !== '' && $sslMode !== 'disabled') {
    if (defined('PDO::MYSQL_ATTR_SSL_CA') && $sslCa !== '') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
    }
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $sslMode === 'verify';
    }
}

$pdo = new PDO($dsn, $user, $pass, $options);

$sql = file_get_contents($schemaPath);
if ($sql === false) {
    fwrite(STDERR, "Não foi possível ler o schema.\n");
    exit(1);
}

$sql = preg_replace('/CREATE\s+DATABASE\s+IF\s+NOT\s+EXISTS\s+`?lexstudy`?.*?;/is', '', $sql);
$sql = preg_replace('/USE\s+`?lexstudy`?\s*;/i', '', $sql);

$count = 0;
foreach (splitSqlStatements($sql) as $statement) {
    $pdo->exec($statement);
    $count++;
}

fwrite(STDOUT, "Importação concluída. Statements executados: {$count}\n");
