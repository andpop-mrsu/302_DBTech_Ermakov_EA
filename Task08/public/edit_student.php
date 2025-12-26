<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

$pdo = get_db_connection();
$errors = [];
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: index.php");
    exit;
}

$groups_stmt = $pdo->query("SELECT id, group_number, entry_year FROM groups ORDER BY entry_year, group_number");
$groups = $groups_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $group_id = (int)($_POST['group_id'] ?? 0);

    if (empty($first_name)) $errors[] = 'Имя обязательно для заполнения.';
    else if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-']+$/u", $first_name)) $errors[] = 'Имя должно содержать только буквы, пробелы, дефисы или апострофы.';

    if (empty($last_name)) $errors[] = 'Фамилия обязательна для заполнения.';
    else if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-']+$/u", $last_name)) $errors[] = 'Фамилия должна содержать только буквы, пробелы, дефисы или апострофы.';

    if (!empty($middle_name) && !preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-']+$/u", $middle_name)) $errors[] = 'Отчество должно содержать только буквы, пробелы, дефисы или апострофы.';

    if (empty($gender)) $errors[] = 'Пол обязателен для заполнения.';
    if (empty($birth_date)) $errors[] = 'Дата рождения обязательна для заполнения.';
    if ($group_id === 0) $errors[] = 'Группа обязательна для выбора.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE students 
                SET first_name = :first_name, last_name = :last_name, middle_name = :middle_name,
                    gender = :gender, birth_date = :birth_date, group_id = :group_id
                WHERE id = :id
            ");
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':middle_name' => $middle_name,
                ':gender' => $gender,
                ':birth_date' => $birth_date,
                ':group_id' => $group_id,
                ':id' => $student_id
            ]);

            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении студента: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать студента</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.8/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Редактировать студента</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_student.php?id=<?= $student_id; ?>">
            <div class="form-group">
                <label for="last_name">Фамилия</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="first_name">Имя</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Отчество</label>
                <input type="text" name="middle_name" id="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Пол</label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" <?php if ($student['gender'] === 'male') echo 'checked'; ?> required>
                        <label class="form-check-label" for="gender_male">Мужской</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" <?php if ($student['gender'] === 'female') echo 'checked'; ?>>
                        <label class="form-check-label" for="gender_female">Женский</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="birth_date">Дата рождения</label>
                <input type="date" name="birth_date" id="birth_date" class="form-control" value="<?= htmlspecialchars($student['birth_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="group_id">Группа</label>
                <select name="group_id" id="group_id" class="form-control" required>
                    <option value="">Выберите группу</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']); ?>" <?php if ($student['group_id'] == $group['id']) echo 'selected'; ?>>
                            <?= htmlspecialchars(calculate_group_display_number($group['group_number'], $group['entry_year'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</body>
</html>
