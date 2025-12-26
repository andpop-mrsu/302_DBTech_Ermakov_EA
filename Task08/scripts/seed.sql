BEGIN TRANSACTION;

INSERT INTO fields (id, name) VALUES
(1, 'Фундаментальная информатика и информационные технологии'),
(2, 'Прикладная математика');
INSERT INTO disciplines (id, name) VALUES
(1, 'Функциональный анализ'),
(2, 'Правоведение'),
(3, 'Веб-разработа'),
(4, 'Математический анализ'),
(5, 'Теория автоматов и формальных языков');
INSERT INTO groups (id, field_id, group_number, entry_year) VALUES
(1, 1, 2, 2023),
(2, 2, 3, 2024);
INSERT INTO curriculum (field_id, discipline_id, semester) VALUES
(1, 4, 3),
(1, 5, 3),
(1, 1, 5),
(1, 2, 5);
INSERT INTO curriculum (field_id, discipline_id, semester) VALUES
(2, 4, 3),
(2, 5, 3);

INSERT INTO students (first_name, last_name, middle_name, gender, birth_date, group_id) VALUES
('Егор', 'Ермаков', 'Александрович', 'male', '2005-11-05', 1),
('Пётр',' Васильев', 'Алексеевич', 'male', '2005-06-14', 1),
('Иван', 'Иванов', 'Иванович', 'male', '2006-08-23', 2),
('София', 'Крючкова', 'Константиновна', 'female', '2006-03-23', 2);

INSERT INTO grades (curriculum_id, student_id, points, exam_date, exam_grade) VALUES
(1, 1, 58, '2024-12-27', 3),
(2, 1, 68, '2024-12-25', 3),
(3, 1, 62, '2025-12-25', 3),
(4, 1, 60, '2025-12-23', 3),
(3, 2, 71, '2025-12-25', 4),
(4, 2, 80, '2025-12-23', 4);
INSERT INTO grades (curriculum_id, student_id, points, exam_date, exam_grade) VALUES
(5, 3, 53, '2025-12-21', 3),
(5, 4, 84, '2025-12-21', 4);

COMMIT;
