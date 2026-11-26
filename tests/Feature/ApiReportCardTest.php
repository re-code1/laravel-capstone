<?php

use App\Models\Section;
use App\Models\User;
use Database\Seeders\SchoolSeeder;
use Database\Seeders\UserSeeder;

beforeEach(function () {
    $this->seed([SchoolSeeder::class, UserSeeder::class]);
});

// Liza Fernandez advises Sampaguita (section 1). Ramon Torres advises Narra (section 2).
function tokenFor(string $email): string
{
    return User::where('email', $email)->first()->createToken('test')->plainTextToken;
}

test('every school endpoint needs a token', function (string $url) {
    $this->getJson($url)->assertUnauthorized();
})->with(['/api/sections', '/api/sections/1/students', '/api/students/4/report-card']);

test('a teacher sees only the section they advise', function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/sections')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0', ['id' => 1, 'name' => 'Sampaguita', 'grade_level' => 10, 'students' => 6]);
});

test('the registrar sees every section, ordered by name', function () {
    $this->withToken(tokenFor('registrar@school.edu.ph'))->getJson('/api/sections')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Narra')
        ->assertJsonPath('data.1.name', 'Sampaguita');
});

test('a teacher who advises no section sees an empty list', function () {
    // Grace Lim teaches but advises nothing.
    $this->withToken(tokenFor('glim@school.edu.ph'))->getJson('/api/sections')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('a teacher gets the class list of their own section', function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/sections/1/students')
        ->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonStructure(['data' => [['id', 'lrn', 'name', 'general_average', 'remark']]]);
});

test("a teacher cannot get another section's class list", function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/sections/2/students')
        ->assertForbidden();
});

test('the registrar can get any class list', function () {
    $this->withToken(tokenFor('registrar@school.edu.ph'))->getJson('/api/sections/2/students')
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

test('a report card comes back as JSON from the service', function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/students/4/report-card')
        ->assertOk()
        ->assertJsonPath('data.lrn', '136512250004')
        ->assertJsonPath('data.name', 'Bautista, Andrea')
        ->assertJsonPath('data.section', 'Sampaguita')
        ->assertJsonPath('data.remark', 'INC')
        ->assertJsonCount(6, 'data.subjects')
        ->assertJsonStructure(['data' => [
            'lrn', 'name', 'section', 'general_average', 'remark',
            'subjects' => [['subject', 'sem1', 'sem2', 'final']],
        ]]);
});

test('a student with every grade gets a passing remark', function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/students/1/report-card')
        ->assertOk()
        ->assertJsonPath('data.remark', 'PASSED');
});

test("a teacher cannot read another section's report card", function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/students/6/report-card')
        ->assertForbidden();
});

test('the registrar can read any report card', function () {
    $this->withToken(tokenFor('registrar@school.edu.ph'))->getJson('/api/students/6/report-card')
        ->assertOk();
});

test('an unknown student gives 404', function () {
    $this->withToken(tokenFor('lfernandez@school.edu.ph'))->getJson('/api/students/999/report-card')
        ->assertNotFound();
});

test('a login with no teacher cannot see a section with no adviser', function () {
    $orphan = Section::forceCreate(['name' => 'Orphan', 'grade_level' => 10, 'adviser_id' => null]);
    $user = User::factory()->create(); // role teacher, teacher_id null
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->getJson('/api/sections')->assertOk()->assertJsonCount(0, 'data');
    $this->withToken($token)->getJson("/api/sections/{$orphan->id}/students")->assertForbidden();
});
