<?php

namespace Climactic\LaravelPolar\Tests\Fixtures;

use Climactic\LaravelPolar\Billable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Climactic\LaravelPolar\Tests\Fixtures\Factories\UserFactory;

class User extends Model
{
    use Billable;
    use HasFactory;

    protected $guarded = [];

    public function getMorphClass()
    {
        return 'users';
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
