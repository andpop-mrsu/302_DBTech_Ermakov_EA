<?php
require_once __DIR__ . '/../src/db.php';

$pdo = get_db_connection();
$grade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$error = '';

if ($grade_id === 0 || $student_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.name AS discipline_name, g.exam_date
    FROM grades g
    JOIN curriculum c ON g.curriculum_id = c.id
    JOIN disciplines d ON c.discipline_id = d.id
    WHERE g.id = :id
");
$stmt->execute([':id' => $grade_id]);
$grade = $stmt->fetch();

if (!$grade) {
    header("Location: view_grades.php?student_id=" . $student_id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM grades WHERE id = :id");
        $stmt->execute([':id' => $grade_id]);

        header("Location: view_grades.php?student_id=" . $student_id);
        exit;
    } catch (PDOException $e) {
        $error = "Ошибка при удалении оценки: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить оценку</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Удалить оценку</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <p>Вы уверены, что хотите удалить эту оценку?</p>
            <p>
                <strong>Дисциплина:</strong> <?= htmlspecialchars($grade['discipline_name']); ?><br>
                <strong>Дата экзамена:</strong> <?= htmlspecialchars($grade['exam_date']); ?>
            </p>
            <p>Это действие необратимо.</p>
        </div>

        <form method="POST" action="delete_grade.php?id=<?= $grade_id; ?>&student_id=<?= $student_id; ?>">
            <button type="submit" class="btn btn-danger">Удалить</button>
            <a href="view_grades.php?student_id=<?= $student_id; ?>" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>
