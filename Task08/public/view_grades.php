<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

$pdo = get_db_connection();
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($student_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.first_name, s.last_name, g.group_number, g.entry_year 
    FROM students s
    JOIN groups g ON s.group_id = g.id
    WHERE s.id = :id
");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php");
    exit;
}

$full_group_number = calculate_group_display_number($student['group_number'], $student['entry_year']);
$first_digit_of_group = (int)substr($full_group_number, 0, 1);

$available_courses = [];
if ($first_digit_of_group > 0) {
    $available_courses = range(1, $first_digit_of_group);
} else {
    $available_courses = [1, 2, 3, 4];
}

// Get selected course from GET parameter, default to the student's current course if not set
$selected_course = isset($_GET['course']) && in_array((int)$_GET['course'], $available_courses) ? (int)$_GET['course'] : $first_digit_of_group;

$semesters_for_course = [
    1 => [1, 2],
    2 => [3, 4],
    3 => [5, 6],
    4 => [7, 8],
];

$semesters_to_filter = $semesters_for_course[$selected_course] ?? [];

// Fetch grades for the student
$grades_query = "
    SELECT 
        g.id,
        d.name AS discipline_name,
        c.semester,
        g.points,
        g.exam_grade,
        g.exam_date
    FROM grades g
    JOIN curriculum c ON g.curriculum_id = c.id
    JOIN disciplines d ON c.discipline_id = d.id
    WHERE g.student_id = ?
";
$grades_params = [$student_id];

if (!empty($semesters_to_filter)) {
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($semesters_to_filter), '?'));
    $grades_query .= " AND c.semester IN ({$placeholders})";
    
    // Merge student_id with semester parameters
    $grades_params = array_merge($grades_params, $semesters_to_filter);
}

$grades_query .= " ORDER BY g.exam_date ASC";

$grades_stmt = $pdo->prepare($grades_query);
$grades_stmt->execute($grades_params);
$grades = $grades_stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты экзаменов</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">
            Результаты экзаменов студента: <?= htmlspecialchars($student['last_name'] . ' ' . $student['first_name']); ?>
        </h1>
        <p><strong>Группа:</strong> <?= htmlspecialchars(calculate_group_display_number($student['group_number'], $student['entry_year'])); ?></p>

        <a href="index.php" class="btn btn-secondary mb-3">Назад к списку студентов</a>

        <div class="mb-3">
            <form method="GET" action="view_grades.php" class="d-flex align-items-center">
                <input type="hidden" name="student_id" value="<?= $student_id; ?>">
                <label for="courseFilter" class="form-label me-2 mb-0">Курс обучения:</label>
                <select class="form-select w-auto me-2" id="courseFilter" name="course" onchange="this.form.submit()">
                    <?php foreach ($available_courses as $course_num): ?>
                        <option value="<?= $course_num; ?>" <?= ($course_num == $selected_course) ? 'selected' : ''; ?>>
                            Курс <?= $course_num; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <h2 class="mb-3">Оценки за <?= $selected_course; ?> курс</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Дисциплина</th>
                    <th>Семестр</th>
                    <th>Дата экзамена</th>
                    <th>Баллы</th>
                    <th>Оценка</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($grades) > 0): ?>
                    <?php foreach ($grades as $grade): ?>
                        <tr>
                            <td><?= htmlspecialchars($grade['discipline_name']); ?></td>
                            <td><?= htmlspecialchars($grade['semester']); ?></td>
                            <td><?= htmlspecialchars($grade['exam_date']); ?></td>
                            <td><?= htmlspecialchars($grade['points']); ?></td>
                            <td><?= htmlspecialchars($grade['exam_grade']); ?></td>
                            <td>
                                <a href="edit_grade.php?id=<?= $grade['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="delete_grade.php?id=<?= $grade['id']; ?>&student_id=<?= $student_id; ?>" class="btn btn-sm btn-danger">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Оценки не найдены для выбранного курса.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="add_grade.php?student_id=<?= $student_id; ?>" class="btn btn-success">Добавить оценку</a>
    </div>
</body>
</html>
