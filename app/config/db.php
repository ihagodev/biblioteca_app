<?php

function carregarConfig() {
    return parse_ini_file("config.ini", true)['DATABASE'];
}

function conectar() {

    $config = carregarConfig();

    $host = $config['host'];
    $port = $config['port'];
    $dbname = $config['dbname'];
    $user = $config['user'];
    $pass = $config['pass'];

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
            $user,
            $pass
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

// Conexão online agora é feita via REST API (app/config/supabase.php).
// PDO direto ao Supabase foi removido pois a porta TCP 5432 é bloqueada
// em ambientes de produção. Use sbRequest() do supabase.php no lugar.
?>