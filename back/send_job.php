<?php

if ($argc < 3) {
    die('Usage: php send_job.php [from] [to]\n');
}

$offset = $argv[1];
$limit = $argv[2];

$pdo = createPdo();

$stmt = $pdo->prepare('SELECT * FROM send_job_queue WHERE status = "pending" ORDER BY created_at LIMIT ?, ?');

$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();

foreach (fetch($stmt) as $job) {
    $pdo
        ->prepare('UPDATE send_job_queue SET status = "processing", started_at = NOW() WHERE id = :id')
        ->execute([':id' => $job['id']]);

    $text = sprintf('%s, your subscription is expiring soon', $job['username']);
    send_email('from@example.com', $job['email'], $text);

    $pdo
        ->prepare('UPDATE send_job_queue SET status = "done", completed_at = NOW() WHERE id = :id')
        ->execute([':id' => $job['id']]);
}

// TODO по хорошему нужно иметь журнал отправленных писем
function send_email($from, $to, $text): void {
    sleep(rand(1, 10));

    echo sprintf('Send email from %s to %s: %s\n', $from, $to, $text) . PHP_EOL;
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