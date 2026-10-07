<?php

declare(strict_types=1);

namespace VmEngine\Example\Livewire\Concerns;

/**
 * Step navigation for wizards: next() validates only the current step's rules,
 * back() never validates, goToStep() only jumps back to completed steps.
 * Hosts implement stepRules(): array<int, array<string, mixed>> keyed 1..n
 * (the last step — review — usually has no rules).
 *
 * Used only by view-based (MFC) components, which PHPStan does not analyse.
 *
 * @phpstan-ignore trait.unused
 */
trait WizardSteps
{
    public int $step = 1;

    /**
     * @return array<int, array<string, mixed>>
     */
    abstract protected function stepRules(): array;

    public function next(): void
    {
        $rules = $this->stepRules()[$this->step] ?? [];

        if ($rules !== []) {
            $this->validate($rules);
        }

        $this->step = min($this->step + 1, count($this->stepRules()));
    }

    public function back(): void
    {
        $this->resetValidation();
        $this->step = max(1, $this->step - 1);
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step < $this->step) {
            $this->resetValidation();
            $this->step = $step;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function allStepRules(): array
    {
        return array_merge(...array_values($this->stepRules()));
    }
}
