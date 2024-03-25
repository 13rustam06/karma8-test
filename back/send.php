<?php

if ($argc < 3) {
    die('Usage: php send.php [from] [to]\n');
}

$from = $argv[1];
$to = $argv[2];

$pdo = createPdo();

$currentTime = time();
$daySeconds = 24 * 60 * 60;
$fromSeconds = $currentTime + ((int) $from * $daySeconds);
$toSeconds = $currentTime + ((int) $to * $daySeconds);

$stmt = $pdo->prepare('SELECT validts, username, email FROM users WHERE validts >= :from AND validts < :to AND (confirmed = 1 OR valid = 1)');
$stmt->execute([':from' => $fromSeconds, ':to' => $toSeconds]);

foreach (fetch($stmt) as $user) {
    addJobToSendJobQueue($pdo, $user['validts'], $user['email'], $user['username']);
}

// Закрываем подключение к базе данных
$pdo = null;



// TODO тут могла бы быть реализация через RabbitMQ, Redis Queue, Kafka
function addJobToSendJobQueue(PDO $pdo, int $validts, string $email, string $username) {
    $stmt = $pdo->prepare('INSERT INTO send_job_queue (validts, email, username) VALUES (:validts, :email, :username)');
    $stmt->execute([':validts' => $validts, ':email' => $email, ':username' => $username]);
    echo 'Add to send_job_queue' . PHP_EOL;
}

function createPdo(): PDO
{
    $host = 'db';
    $dbname = 'test_db';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Не удалось подключиться к базе данных: " . $e->getMessage());
    }

    return $pdo;
}

function fetch(PDOStatement $stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
}