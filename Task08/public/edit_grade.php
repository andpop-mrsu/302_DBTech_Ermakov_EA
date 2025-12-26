<?php
require_once __DIR__ . '/../src/db.php';

$pdo = get_db_connection();
$grade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

if ($grade_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        g.id, g.student_id, g.curriculum_id, g.exam_date, g.points, g.exam_grade,
        d.name AS discipline_name,
        c.semester
    FROM grades g
    JOIN curriculum c ON g.curriculum_id = c.id
    JOIN disciplines d ON c.discipline_id = d.id
    WHERE g.id = :id
");
$stmt->execute([':id' => $grade_id]);
$grade = $stmt->fetch();

if (!$grade) {
    header("Location: index.php");
    exit;
}

$student_id = $grade['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_date = $_POST['exam_date'] ?? '';
    $points = (int)($_POST['points'] ?? 0);
    $exam_grade = (int)($_POST['exam_grade'] ?? 0);

    if (empty($exam_date)) $errors[] = 'Дата экзамена обязательна для заполнения.';
    if ($points < 0 || $points > 100) $errors[] = 'Баллы должны быть в диапазоне от 0 до 100.';
    if ($exam_grade < 2 || $exam_grade > 5) $errors[] = 'Оценка должна быть в диапазоне от 2 до 5.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE grades
                SET exam_date = :exam_date, points = :points, exam_grade = :exam_grade
                WHERE id = :id
            ");
            $stmt->execute([
                ':exam_date' => $exam_date,
                ':points' => $points,
                ':exam_grade' => $exam_grade,
                ':id' => $grade_id
            ]);

            header("Location: view_grades.php?student_id=" . $student_id);
            exit;
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении оценки: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать оценку</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Редактировать оценку</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_grade.php?id=<?= $grade_id; ?>">
            <div class="form-group">
                <label>Дисциплина</label>
                <input type="text" class="form-control"
                       value="<?= htmlspecialchars($grade['discipline_name'] . ' (Семестр ' . $grade['semester'] . ')'); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="exam_date">Дата экзамена</label>
                <input type="date" name="exam_date" id="exam_date" class="form-control" value="<?= htmlspecialchars($grade['exam_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="points">Баллы</label>
                <input type="number" name="points" id="points" class="form-control" min="0" max="100" value="<?= htmlspecialchars($grade['points']); ?>" required>
            </div>
            <div class="form-group">
                <label for="exam_grade">Оценка</label>
                <input type="number" name="exam_grade" id="exam_grade" class="form-control" min="2" max="5" value="<?= htmlspecialchars($grade['exam_grade']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <a href="view_grades.php?student_id=<?= $student_id; ?>" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>
