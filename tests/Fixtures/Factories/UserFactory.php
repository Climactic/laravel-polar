<?php

namespace Climactic\LaravelPolar\Tests\Fixtures\Factories;

use Climactic\LaravelPolar\Tests\Fixtures\User;
use Orchestra\Testbench\Factories\UserFactory as OrchestraUserFactory;

class UserFactory extends OrchestraUserFactory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;
}
