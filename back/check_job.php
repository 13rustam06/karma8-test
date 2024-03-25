<?php


if ($argc < 3) {
    die('Usage: php check_job.php [from] [to]\n');
}

// Читаем аргументы
$offset = $argv[1];
$limit = $argv[2];

$pdo = createPdo();

$sql = 'SELECT * FROM check_job_queue WHERE status = "pending" ORDER BY created_at LIMIT ?, ?';
$stmt = $pdo->prepare($sql);

$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();

foreach (fetch($stmt) as $job) {
    $pdo
        ->prepare('UPDATE check_job_queue SET status = "processing", started_at = NOW() WHERE id = :id')
        ->execute([':id' => $job['id']]);

    if (exist_user($pdo, $job['email'], $job['validts'])) {
        $isValid = check_email($job['email']);

        $pdo
            ->prepare('UPDATE check_job_queue SET status = "done", completed_at = NOW() WHERE id = :id')
            ->execute([':id' => $job['id']]);

        $pdo
            ->prepare('UPDATE users SET checked = 1, valid = ? WHERE email = ?')
            ->execute([$isValid, $job['email']]);
    } else {
        $pdo
            ->prepare('UPDATE check_job_queue SET status = "skipped", completed_at = NOW() WHERE id = :id')
            ->execute([':id' => $job['id']]);
    }
}

function check_email($email): int {
    sleep(rand(1, 60));
    echo 'check_email' . PHP_EOL;

    return rand(0, 1);
}

function exist_user(PDO $pdo, string $email, int $validts): bool
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND validts = :validts AND confirmed = 0 AND checked = 0');
    $stmt->execute([':email' => $email, ':validts' => $validts]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return !empty($user['id']);
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