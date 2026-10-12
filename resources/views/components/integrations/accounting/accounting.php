<?php

declare(strict_types=1);

use Livewire\Attributes\Computed;
use Livewire\Component;
use VmEngine\Accounting\Accounting;
use VmEngine\Accounting\Integration\JournalBuilder;
use VmEngine\Accounting\Integration\PostingResult;
use VmEngine\Example\Livewire\Concerns\IntegrationPatternPage;
use VmEngine\Example\Models\Example;
use VmEngine\SynAuth\Traits\GuardsBackendPermission;

/**
 * Posts a journal for an example through the accounting module's fluent API.
 * The class and account settings it uses are declared under "accounting" in module.json.
 * Accounting is optional: this page's route and catalog entry exist only where it is installed.
 */
new class extends Component
{
    use GuardsBackendPermission;
    use IntegrationPatternPage;

    public string $exampleId = '';

    public string $amount = '100000';

    public string $type = 'cash';

    /** @var array{outcome: string, journal: string|null, journal_id: int|null, reason: string|null}|null */
    public ?array $result = null;

    public function title(): string
    {
        return __('example::integrations.accounting');
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function exampleOptions(): array
    {
        return Example::query()->latest('id')->limit(50)->get()
            ->map(fn (Example $example): array => ['value' => (string) $example->id, 'label' => '#'.$example->id.' '.$example->text])
            ->values()->all();
    }

    /** @return list<array{value: string, label: string}> */
    #[Computed]
    public function typeOptions(): array
    {
        return [
            ['value' => 'cash', 'label' => __('example::integrations.accounting_type_cash')],
            ['value' => 'transfer', 'label' => __('example::integrations.accounting_type_transfer')],
        ];
    }

    public function post(): void
    {
        if ($example = $this->exampleForAction()) {
            $this->show($this->journalFor($example)->post());
        }
    }

    public function replace(): void
    {
        if ($example = $this->exampleForAction()) {
            $this->show($this->journalFor($example)->replace());
        }
    }

    public function cancel(): void
    {
        if ($example = $this->exampleForAction()) {
            $this->show(Accounting::journal('example.sale')->for($example)->cancel());
        }
    }

    /**
     * The whole integration: describe the journal for this record; the caller picks post/replace.
     * No accounting type is named in a signature here: the module must load without accounting installed.
     *
     * @return JournalBuilder
     */
    private function journalFor(Example $example): object
    {
        return Accounting::journal('example.sale')
            ->for($example)
            ->on(now())
            ->describe('Example sale: '.$example->text)
            ->type($this->type)
            ->reference('EX-'.$example->id)
            ->snapshot(['example_id' => $example->id, 'amount' => $this->amount])
            ->debit('example.cash', $this->amount)
            ->credit('example.revenue', $this->amount);
    }

    private function exampleForAction(): ?Example
    {
        // The route exists only with accounting installed; the guard keeps a stray call harmless.
        if (! class_exists(Accounting::class) || ! $this->guardAction('example.manage.update')) {
            return null;
        }

        $this->validate([
            'exampleId' => ['required', 'integer', 'exists:examples,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'type' => ['required', 'in:cash,transfer'],
        ]);

        return Example::query()->findOrFail((int) $this->exampleId);
    }

    /** @param  PostingResult  $result */
    private function show(object $result): void
    {
        $this->result = [
            'outcome' => $result->outcome->label(),
            'journal' => $result->journal?->number,
            'journal_id' => $result->journal?->id,
            'reason' => $result->pending?->reason,
        ];
    }

    /** The code shown on the page (kept next to journalFor() so the two stay alike). */
    public function sampleCode(): string
    {
        return <<<'CODE'
        if (class_exists(\VmEngine\Accounting\Accounting::class)) {
            Accounting::journal('example.sale')
                ->for($example)
                ->on(now())
                ->describe('Example sale: '.$example->text)
                ->type('cash')
                ->reference('EX-'.$example->id)
                ->snapshot(['example_id' => $example->id, 'amount' => $amount])
                ->debit('example.cash', $amount)
                ->credit('example.revenue', $amount)
                ->post();          // or ->replace() / ->cancel() / ->postOrFail()
        }
        CODE;
    }
};
