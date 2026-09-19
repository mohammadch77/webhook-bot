<?php

namespace App\Bot\Engines;

use App\Bot\DTOs\ConditionResult;
use App\Models\Process;
use App\Models\ProcessConditionRule;
use App\Models\Submission;

class ConditionEngine
{
    public function evaluate(Process $process, Submission $submission): ?ConditionResult
    {
        $groups = $process->conditionGroups()
            ->with('rules')
            ->orderBy('display_order')
            ->get();

        foreach ($groups as $group) {
            if ($group->rules->isEmpty()) {
                continue;
            }

            $allMatch = $group->rules->every(
                fn (ProcessConditionRule $rule) => $this->evaluateRule($rule, $submission)
            );

            if ($allMatch) {
                return new ConditionResult(
                    action: $group->action,
                    targetStepId: $group->target_step_id,
                    stopMessage: $group->stop_message,
                );
            }
        }

        return null;
    }

    protected function evaluateRule(ProcessConditionRule $rule, Submission $submission): bool
    {
        $submissionValue = $submission->values()
            ->where('field_id', $rule->field_id)
            ->first();

        if ($submissionValue === null) {
            return false;
        }

        $actual = $submissionValue->value;
        $expected = $rule->value;

        return match ($rule->operator) {
            '=' => $this->looseEquals($actual, $expected),
            '!=' => ! $this->looseEquals($actual, $expected),
            '>' => (float) $actual > (float) $expected,
            '<' => (float) $actual < (float) $expected,
            '>=' => (float) $actual >= (float) $expected,
            '<=' => (float) $actual <= (float) $expected,
            default => false,
        };
    }

    protected function looseEquals(?string $actual, string $expected): bool
    {
        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        return $actual === $expected;
    }
}
