<?php
require_once __DIR__ . '/../src/db.php';

$pdo = get_db_connection();
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

if ($student_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE id = :id");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM grades WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $student_id]);

        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $stmt->execute([':id' => $student_id]);

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = 'Невозможно удалить студента. Сначала удалите связанные с ним данные (например, оценки). ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить студента</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Удалить студента</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <p>Вы уверены, что хотите удалить студента?</p>
            <p><strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></p>
            <p>Это действие также удалит все оценки этого студента. Это действие необратимо.</p>
        </div>

        <form method="POST" action="delete_student.php?id=<?= $student_id; ?>">
            <button type="submit" class="btn btn-danger">Удалить</button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>
