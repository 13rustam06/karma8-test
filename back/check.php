<?php
// Получаем весь список для проверки email на валидность

if ($argc < 3) {
    die('Usage: php check.php [from] [to]\n');
}
$from = $argv[1];
$to = $argv[2];

$pdo = createPdo();

$currentTime = time();
$daySeconds = 24 * 60 * 60;
$fromSeconds = $currentTime + ($from * $daySeconds);
$toSeconds = $currentTime + ($to * $daySeconds);

$sql = 'SELECT validts, email FROM users WHERE validts >= :from AND validts < :to AND confirmed = 0 AND checked = 0';
$stmt = $pdo->prepare($sql);
$stmt->execute([':from' => $fromSeconds, ':to' => $toSeconds]);

foreach (fetch($stmt) as $user) {
    addJobToCheckJobQueue($pdo, $user['validts'], $user['email']);
}

$pdo = null;




// TODO тут могла бы быть реализация через RabbitMQ, Redis Queue, Kafka
function addJobToCheckJobQueue(PDO $pdo, int $validts, string $email) {
    $stmt = $pdo->prepare("INSERT INTO check_job_queue (validts, email) VALUES (:validts, :email)");
    $stmt->execute([':validts' => $validts, ':email' => $email]);
    echo 'Add to check_job_queue' . PHP_EOL;
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