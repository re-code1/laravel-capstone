<?php
// A tiny JSON API for the Day 2 school database, in plain PHP.
// Run it with:  php -S localhost:8005 index.php

$pdo = new PDO('mysql:host=localhost;dbname=school;charset=utf8mb4', 'franky', 'Lk232G123', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Send any PHP value as JSON with a status code.
function respond(int $status, $data = null): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    if ($data !== null) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);   // e.g. /api/students/4

// GET /api/students   or   GET /api/students?section_id=1
if ($path === '/api/students') {
    if ($method === 'GET') {
        $sql = 'SELECT student_id AS id, lrn, first_name, last_name, section_id FROM students';
        $params = [];
        if (isset($_GET['section_id'])) {
            $sql .= ' WHERE section_id = ?';
            $params[] = $_GET['section_id'];
        }
        $stmt = $pdo->prepare($sql . ' ORDER BY last_name');
        $stmt->execute($params);
        respond(200, ['data' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = [];
        if (! preg_match('/^\d{12}$/', $input['lrn'] ?? '')) {
            $errors['lrn'] = 'The LRN must be exactly 12 digits.';
        }
        foreach (['first_name', 'last_name'] as $field) {
            if (trim($input[$field] ?? '') === '') {
                $errors[$field] = "The $field is required.";
            }
        }
        if (! in_array($input['section_id'] ?? null, [1, 2], true)) {
            $errors['section_id'] = 'Choose section 1 or 2.';
        }
        if ($errors) {
            respond(422, ['error' => 'The data is not valid.', 'details' => $errors]);
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO students (lrn, first_name, last_name, section_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$input['lrn'], $input['first_name'], $input['last_name'], $input['section_id']]);
        } catch (PDOException $e) {
            respond(409, ['error' => 'A student with this LRN already exists.']);
        }

        $id = (int) $pdo->lastInsertId();
        header("Location: /api/students/$id");
        respond(201, ['data' => ['id' => $id] + $input]);
    }

    header('Allow: GET, POST');
    respond(405, ['error' => "Method $method is not allowed here."]);
}

// GET /api/students/4   and   DELETE /api/students/4
if (preg_match('#^/api/students/(\d+)$#', $path, $m)) {
    $id = (int) $m[1];
    $stmt = $pdo->prepare('SELECT student_id AS id, lrn, first_name, last_name, section_id FROM students WHERE student_id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (! $student) {
        respond(404, ['error' => "Student $id not found."]);
    }

    if ($method === 'GET') {
        respond(200, ['data' => $student]);
    }

    if ($method === 'DELETE') {
        try {
            $pdo->prepare('DELETE FROM students WHERE student_id = ?')->execute([$id]);
        } catch (PDOException $e) {
            respond(409, ['error' => 'This student has grades, so they cannot be deleted.']);
        }
        respond(204);
    }

    header('Allow: GET, DELETE');
    respond(405, ['error' => "Method $method is not allowed here."]);
}

// GET /api/students/4/grades
if (preg_match('#^/api/students/(\d+)/grades$#', $path, $m) && $method === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT s.name AS subject, g.semester, g.grade
           FROM grades g JOIN subjects s ON s.subject_id = g.subject_id
          WHERE g.student_id = ? ORDER BY s.subject_id, g.semester'
    );
    $stmt->execute([(int) $m[1]]);
    respond(200, ['data' => $stmt->fetchAll()]);
}

respond(404, ['error' => "No endpoint at $path."]);