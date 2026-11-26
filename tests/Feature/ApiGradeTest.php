<?php

use App\Models\Grade;
use App\Models\User;
use Database\Seeders\SchoolSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed([SchoolSeeder::class, UserSeeder::class]);
});

// Grade 1 was given by Liza (teacher 1). Grade 3 was given by Ramon (teacher 2).
function gradeToken(string $email = 'lfernandez@school.edu.ph'): string
{
    return User::where('email', $email)->first()->createToken('test')->plainTextToken;
}

test('changing a grade needs a token', function () {
    $this->putJson('/api/grades/1', ['grade' => 90])->assertUnauthorized();
});

test('a teacher can change a grade they gave', function () {
    $response = $this->withToken(gradeToken())->putJson('/api/grades/1', ['grade' => 91.5])
        ->assertOk()
        ->assertJsonPath('data.id', 1)
        ->assertJsonPath('data.student_id', 1)
        ->assertJsonPath('data.subject_id', 1)
        ->assertJsonPath('data.semester', 1)
        ->assertJsonMissingPath('data.teacher_id');

    // MariaDB gives DECIMAL back as "91.50", SQLite as 91.5, so compare as numbers.
    expect((float) $response->json('data.grade'))->toBe(91.5)
        ->and((float) Grade::find(1)->grade)->toBe(91.5);
});

test("a teacher cannot change another teacher's grade", function () {
    $this->withToken(gradeToken())->putJson('/api/grades/3', ['grade' => 99])->assertForbidden();

    expect((float) Grade::find(3)->grade)->toBe(89.0);
});

test('the registrar can change any grade', function () {
    $this->withToken(gradeToken('registrar@school.edu.ph'))->putJson('/api/grades/3', ['grade' => 90])
        ->assertOk();
});

test('the policy is checked before the rules (403 beats 422)', function () {
    $this->withToken(gradeToken())->putJson('/api/grades/3', ['grade' => 500])->assertForbidden();
});

test('an unknown grade gives 404', function () {
    $this->withToken(gradeToken())->putJson('/api/grades/9999', ['grade' => 90])->assertNotFound();
});

test('bad grades give 422 and change nothing', function (mixed $bad) {
    $this->withToken(gradeToken())->putJson('/api/grades/1', ['grade' => $bad])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('grade');

    expect((float) Grade::find(1)->grade)->toBe(88.25);
})->with([
    'above 100' => 120,
    'below 60' => 59.99,
    'text' => 'abc',
    'three decimals' => 88.756,
]);

test('the grade key is required', function () {
    $this->withToken(gradeToken())->putJson('/api/grades/1', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('grade');
});

test('the edges 60 and 100 are allowed', function (int $edge) {
    $this->withToken(gradeToken())->putJson('/api/grades/1', ['grade' => $edge])->assertOk();
})->with([60, 100]);

test('null clears the grade, which makes the student INC', function () {
    $this->withToken(gradeToken())->putJson('/api/grades/1', ['grade' => null])
        ->assertOk()
        ->assertJsonPath('data.grade', null);

    $this->withToken(gradeToken())->getJson('/api/students/1/report-card')
        ->assertJsonPath('data.remark', 'INC');
});

test('extra fields such as teacher_id are ignored', function () {
    $this->withToken(gradeToken())
        ->putJson('/api/grades/1', ['grade' => 88.25, 'teacher_id' => 2, 'student_id' => 5])
        ->assertOk()
        ->assertJsonPath('data.student_id', 1);

    expect(Grade::find(1)->teacher_id)->toBe(1)
        ->and(Grade::find(1)->student_id)->toBe(1);
});
