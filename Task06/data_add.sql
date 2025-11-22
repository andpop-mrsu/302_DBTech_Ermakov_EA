INSERT INTO users (id, name, email, gender, register_date, occupation_id) VALUES
(NULL, "Ермаков Егор", "ermakov.egor@example.com", "male", date("now"), 6),
(NULL, "Гришуков Егор", "grishukov321@example.com", "male", date("now"), 6),
(NULL, "Данькин Иван", "dankin342543@example.com", "male", date("now"), 6),
(NULL, "Китаев Евгений", "kitaev@example.com", "male", date("now"), 6),
(NULL, "Кармазов Никита", "karmazove32912@example.com", "male", date("now"), 6);


INSERT INTO movies (id, title, year) VALUES
(NULL, "One Battle After Another", 2025),
(NULL, "28 Years Later", 2025),
(NULL, "A Minecraft Movie", 2025);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name="Action"
    WHERE m.title="One Battle After Another";
INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name="Comedy"
    WHERE m.title="A Minecraft Movie";
INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name="Horror"
    WHERE m.title="28 Years Later";


INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 4.5, STRFTIME('%s','now')
    FROM users u JOIN movies.m ON m.title="One Battle After Another"
    WHERE u.email="ermakov.egor@example.com"
    AND NOT EXISTS (
	SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
    );

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 5.0, STRFTIME('%s','now')
    FROM users u JOIN movies.m ON m.title="A Minecraft Movie"
    WHERE u.email="ermakov.egor@example.com"
    AND NOT EXISTS (
	SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
    );

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 5.0, STRFTIME('%s','now')
    FROM users u JOIN movies.m ON m.title="28 Years Later"
    WHERE u.email="ermakov.egor@example.com"
    AND NOT EXISTS (
	SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
    );
