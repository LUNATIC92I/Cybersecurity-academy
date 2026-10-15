<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Seeds reference data and the curriculum. All seeders are idempotent so
 * they can be re-run after content files change.
 */
final class DatabaseSeeder
{
    /** @var callable */
    private $out;

    public function __construct(?callable $out = null)
    {
        $this->out = $out ?? static fn (string $line) => null;
    }

    public function run(bool $demo = false): void
    {
        $steps = [
            'Roles and permissions' => fn () => (new RoleSeeder())->run(),
            'Settings' => fn () => (new SettingsSeeder())->run(),
            'Curriculum (paths, courses, modules)' => fn () => (new CurriculumSeeder())->run(),
            'Lessons' => fn () => (new LessonSeeder())->run(),
            'Labs' => fn () => (new LabSeeder())->run(),
            'Question banks and assessments' => fn () => (new AssessmentSeeder())->run(),
            'Plans and achievements' => fn () => (new CommerceSeeder())->run(),
        ];
        if ($demo) {
            $steps['Demonstration accounts'] = fn () => (new DemoSeeder($this->out))->run();
        }
        foreach ($steps as $label => $step) {
            $result = $step();
            ($this->out)(sprintf('Seeded: %s%s', $label, is_string($result) ? " ($result)" : ''));
        }
    }
}
