<?php

declare(strict_types=1);

use App\Core\App;
use Database\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

// Boot a testing application once, then build the schema in the test database.
$app = new App(dirname(__DIR__), true);

if (app()->config()->get('database.database') !== 'cyberacademy_test') {
    fwrite(STDERR, "Refusing to run tests against a non-test database.\n");
    exit(1);
}

$migrator = new Migrator(db(), dirname(__DIR__) . '/database/migrations');
$migrator->dropAll();
$migrator->migrate();

// Reference data and the full curriculum/labs/questions are needed by feature tests.
(new Database\Seeders\RoleSeeder())->run();
(new Database\Seeders\SettingsSeeder())->run();
(new Database\Seeders\CurriculumSeeder())->run();
(new Database\Seeders\LessonSeeder())->run();
(new Database\Seeders\LabSeeder())->run();
(new Database\Seeders\AssessmentSeeder())->run();
(new Database\Seeders\CommerceSeeder())->run();
