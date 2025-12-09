BEGIN TRANSACTION;

DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS curriculum;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS disciplines;
DROP TABLE IF EXISTS fields;

CREATE TABLE fields (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL
);

CREATE TABLE disciplines (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL
);

CREATE TABLE groups (
  id INTEGER PRIMARY KEY,
  field_id INTEGER NOT NULL,
  group_number INTEGER NOT NULL,
  entry_year INTEGER NOT NULL,
  FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE RESTRICT.
  UNIQUE (field_id, group_number, entry_year)
);

CREATE TABLE curriculum (
  id INTEGER PRIMARY KEY,
  field_id INTEGER NOT NULL,
  discipline_id INTEGER NOT NULL,
  semester INTEGER NOT NULL,
  lecture_hours INTEGER DEFAULT 0,
  practice_hours INTEGER DEFAULT 0,
  control_type TEXT CHECK (control_type IN ('exam', 'credit')),
  FOREIGN KEY (field_id) REFERENCES fields(id),
  FOREIGN KEY (discipline_id) REFERENCES disciplines(id),
  UNIQUE (field_id, discipline_id, semester)
);

CREATE TABLE students (
  id INTEGER PRIMARY KEY,
  first_name TEXT NOT NULL,
  middle_name TEXT,
  last_name TEXT NOT NULL,
  gender TEXT CHECK(gender IN ('male', 'female')),
  birth_date DATE NOT NULL,
  group_id INTEGER NOT NULL,
  FOREIGN KEY (group_id) REFERENCES groups(id)
);

CREATE TABLE grades (
  id INTEGER PRIMARY KEY,
  curriculum_id INTEGER NOT NULL,
  student_id INTEGER NOT NULL,
  points INTEGER CHECK(points >= 0 AND points <= 100) DEFAULT 0,
  exam_date DATE,
  exam_grade INTEGER,
  FOREIGN KEY (curriculum_id) REFERENCES curriculum(id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  UNIQUE (curriculum_id, student_id)
);

COMMIT;
