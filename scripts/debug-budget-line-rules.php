<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function dumpRule(string $label, object $component): void
{
    $ref = new ReflectionClass($component);
    $file = $ref->getFileName() ?: 'unknown';
    $method = $ref->getMethod('rules');
    $method->setAccessible(true);

    /** @var array $rules */
    $rules = $method->invoke($component);

    $rule = $rules['budget_line'] ?? 'MISSING';

    if (is_array($rule)) {
        $rule = json_encode($rule);
    }

    echo $label . ' (' . $file . '): ' . $rule . PHP_EOL;
}

dumpRule('Acq Create budget_line', new App\Livewire\Acquisitions\Requests\Create());
dumpRule('Acq Edit budget_line', new App\Livewire\Acquisitions\Requests\Edit());
dumpRule('Acq Form budget_line', new App\Livewire\Acquisitions\Requests\Form());
dumpRule('Mgmt Form budget_line', new App\Livewire\Management\Requests\Form());
