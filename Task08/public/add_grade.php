<?php
require_once __DIR__ . '/../src/db.php';

$pdo = get_db_connection();
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$errors = [];

if ($student_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT g.field_id, g.entry_year
    FROM students s
    JOIN groups g ON s.group_id = g.id
    WHERE s.id = :student_id
");
$stmt->execute([':student_id' => $student_id]);
$student_info = $stmt->fetch();

if (!$student_info) {
    header("Location: index.php");
    exit;
}

$curriculum_stmt = $pdo->prepare("
    SELECT c.id, d.name, c.semester
    FROM curriculum c
    JOIN disciplines d ON c.discipline_id = d.id
    WHERE c.field_id = :field_id
    ORDER BY c.semester, d.name
");
$curriculum_stmt->execute([':field_id' => $student_info['field_id']]);
$available_curriculum = $curriculum_stmt->fetchAll();

$curriculum_by_semester = [];
foreach ($available_curriculum as $item) {
    $curriculum_by_semester[$item['semester']][] = $item;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curriculum_id = (int)($_POST['curriculum_id'] ?? 0);
    $exam_date = $_POST['exam_date'] ?? '';
    $points = (int)($_POST['points'] ?? 0);
    $exam_grade = (int)($_POST['exam_grade'] ?? 0);

    if ($curriculum_id === 0) $errors[] = 'Дисциплина обязательна для выбора.';
    if (empty($exam_date)) $errors[] = 'Дата экзамена обязательна для заполнения.';
    if ($points < 0 || $points > 100) $errors[] = 'Баллы должны быть в диапазоне от 0 до 100.';
    if ($exam_grade < 2 || $exam_grade > 5) $errors[] = 'Оценка должна быть в диапазоне от 2 до 5.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO grades (student_id, curriculum_id, exam_date, points, exam_grade)
                VALUES (:student_id, :curriculum_id, :exam_date, :points, :exam_grade)
            ");
            $stmt->execute([
                ':student_id' => $student_id,
                ':curriculum_id' => $curriculum_id,
                ':exam_date' => $exam_date,
                ':points' => $points,
                ':exam_grade' => $exam_grade
            ]);

            header("Location: view_grades.php?student_id=" . $student_id);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (UNIQUE)
                $errors[] = 'Оценка по этой дисциплине для данного студента уже существует.';
            } else {
                $errors[] = "Ошибка при добавлении оценки: " . $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить оценку</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Добавить новую оценку</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_grade.php?student_id=<?= $student_id; ?>">
            <div class="form-group">
                <label for="semester">Семестр</label>
                <select id="semester" class="form-control" required>
                    <option value="">Выберите семестр</option>
                    <?php foreach (array_keys($curriculum_by_semester) as $semester): ?>
                        <option value="<?= htmlspecialchars($semester); ?>">
                            <?= htmlspecialchars($semester); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="curriculum_id">Дисциплина</label>
                <select name="curriculum_id" id="curriculum_id" class="form-control" required>
                    <option value="">Сначала выберите семестр</option>
                </select>
            </div>
            <div class="form-group">
                <label for="exam_date">Дата экзамена</label>
                <input type="date" name="exam_date" id="exam_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="points">Баллы</label>
                <input type="number" name="points" id="points" class="form-control" min="0" max="100" required>
            </div>
            <div class="form-group">
                <label for="exam_grade">Оценка</label>
                <input type="number" name="exam_grade" id="exam_grade" class="form-control" min="2" max="5" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Добавить оценку</button>
            <a href="view_grades.php?student_id=<?= $student_id; ?>" class="btn btn-secondary">Отмена</a>
        </form>
    </div>

    <script>
        const curriculumBySemester = <?= json_encode($curriculum_by_semester); ?>;
        const semesterSelect = document.getElementById('semester');
        const disciplineSelect = document.getElementById('curriculum_id');

        semesterSelect.addEventListener('change', function() {
            const selectedSemester = this.value;
            disciplineSelect.innerHTML = '<option value="">Выберите дисциплину</option>';

            if (selectedSemester && curriculumBySemester[selectedSemester]) {
                curriculumBySemester[selectedSemester].forEach(function(item) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    disciplineSelect.appendChild(option);
                });
            }
        });
    </script>
</body>
</html>
