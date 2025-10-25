<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateFactory
{

    private mixed $modelBinding;

    /**
     * @param array $modelBinding
     */
    public function __construct(array $modelBinding)
    {
        $this->modelBinding = $modelBinding['className'];
    }

    public function generate(): string
    {
        return "<?php
namespace Database\Factories;

use ;{$this->modelBinding};

class {$this->modelBinding}Factory extends Factory
{
    protected \$model = {$this->modelBinding}::class;

    public function definition(): array
    {
        return [
            'title' => \$this->faker->title(),
        ];
    }
}
        ";
    }
}
