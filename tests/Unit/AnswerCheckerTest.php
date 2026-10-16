<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AnswerChecker;
use PHPUnit\Framework\TestCase;

final class AnswerCheckerTest extends TestCase
{
    public function test_text_is_case_and_space_insensitive(): void
    {
        $spec = ['accepted' => ['10.0.9.77']];
        $this->assertTrue(AnswerChecker::text('10.0.9.77', $spec));
        $this->assertTrue(AnswerChecker::text('  10.0.9.77 ', $spec));
        $this->assertFalse(AnswerChecker::text('10.0.9.78', $spec));
        $this->assertFalse(AnswerChecker::text('', $spec));
    }

    public function test_text_contains_mode(): void
    {
        $spec = ['accepted' => ['retest'], 'contains' => true];
        $this->assertTrue(AnswerChecker::text('I would retest the fix', $spec));
        $this->assertFalse(AnswerChecker::text('I would rerun it', $spec));
    }

    public function test_number_respects_tolerance(): void
    {
        $this->assertTrue(AnswerChecker::number('38', ['value' => 38, 'tolerance' => 0]));
        $this->assertTrue(AnswerChecker::number('39', ['value' => 38, 'tolerance' => 1]));
        $this->assertFalse(AnswerChecker::number('40', ['value' => 38, 'tolerance' => 1]));
        $this->assertFalse(AnswerChecker::number('not a number', ['value' => 38, 'tolerance' => 0]));
        $this->assertTrue(AnswerChecker::number('62', ['value' => 62, 'tolerance' => 0]));
    }

    public function test_choice_and_multi(): void
    {
        $this->assertTrue(AnswerChecker::choice('c', ['correct' => 'c']));
        $this->assertFalse(AnswerChecker::choice('a', ['correct' => 'c']));
        $this->assertTrue(AnswerChecker::multi(['a', 'c'], ['correct' => ['c', 'a']]));
        $this->assertFalse(AnswerChecker::multi(['a'], ['correct' => ['a', 'c']]));
        $this->assertFalse(AnswerChecker::multi(['a', 'c', 'd'], ['correct' => ['a', 'c']]));
        $this->assertFalse(AnswerChecker::multi('a', ['correct' => ['a']]));
    }

    public function test_query_structure_check(): void
    {
        $spec = ['required' => ['/SecurityEvent/i', '/EventID\s*==\s*4625/i', '/summarize/i'], 'forbidden' => ['/drop\s+table/i']];
        $good = 'SecurityEvent | where EventID == 4625 | summarize count() by IpAddress';
        $this->assertTrue(AnswerChecker::query($good, $spec)['ok']);
        $missing = 'SecurityEvent | where EventID == 4625';
        $this->assertFalse(AnswerChecker::query($missing, $spec)['ok']);
        $forbidden = $good . ' ; DROP TABLE users';
        $this->assertFalse(AnswerChecker::query($forbidden, $spec)['ok']);
        $this->assertFalse(AnswerChecker::query('', $spec)['ok']);
    }
}
