<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

$pdo = get_db_connection();

$selected_group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
$selected_entry_year = isset($_GET['entry_year']) ? (int)$_GET['entry_year'] : 0;

$groups_stmt = $pdo->query("SELECT id, group_number, entry_year FROM groups ORDER BY entry_year, group_number");
$groups = $groups_stmt->fetchAll();

$years_stmt = $pdo->query("SELECT DISTINCT entry_year FROM groups ORDER BY entry_year DESC");
$entry_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

$sql = "
    SELECT s.id, s.first_name, s.middle_name, s.last_name, g.group_number, g.entry_year
    FROM students s
    JOIN groups g ON s.group_id = g.id
";

$where_clauses = [];
if ($selected_group_id > 0) {
    $where_clauses[] = "s.group_id = :group_id";
}
if ($selected_entry_year > 0) {
    $where_clauses[] = "g.entry_year = :entry_year";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY g.entry_year, g.group_number, s.last_name";

$stmt = $pdo->prepare($sql);

if ($selected_group_id > 0) {
    $stmt->bindValue(':group_id', $selected_group_id, PDO::PARAM_INT);
}
if ($selected_entry_year > 0) {
    $stmt->bindValue(':entry_year', $selected_entry_year, PDO::PARAM_INT);
}

$stmt->execute();
$students = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление студентами</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Управление студентами</h1>

        <form method="GET" action="index.php" class="form-inline mb-4">
            <div class="form-group">
                <label for="group_id" class="mr-2">Фильтр по группе:</label>
                <select name="group_id" id="group_id" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="0">Все группы</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']); ?>" <?= $selected_group_id == $group['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(calculate_group_display_number($group['group_number'], $group['entry_year'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group ml-3">
                <label for="entry_year" class="mr-2">Фильтр по году поступления:</label>
                <select name="entry_year" id="entry_year" class="form-control mr-2" onchange="this.form.submit()">
                    <option value="0">Все года</option>
                    <?php foreach ($entry_years as $year): ?>
                        <option value="<?= htmlspecialchars($year); ?>" <?= $selected_entry_year == $year ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Группа</th>
                    <th>Год поступления</th>
                    <th>Фамилия</th>
                    <th>Имя</th>
                    <th>Отчество</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars(calculate_group_display_number($student['group_number'], $student['entry_year'])); ?></td>
                            <td><?= htmlspecialchars($student['entry_year']); ?></td>
                            <td><?= htmlspecialchars($student['last_name']); ?></td>
                            <td><?= htmlspecialchars($student['first_name']); ?></td>
                            <td><?= htmlspecialchars($student['middle_name']); ?></td>
                            <td>
                                <a href="edit_student.php?id=<?= $student['id']; ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="delete_student.php?id=<?= $student['id']; ?>" class="btn btn-sm btn-danger">Удалить</a>
                                <a href="view_grades.php?student_id=<?= $student['id']; ?>" class="btn btn-sm btn-info">Результаты экзаменов</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Студенты не найдены.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="add_student.php" class="btn btn-success">Добавить студента</a>
    </div>

</body>
</html>
