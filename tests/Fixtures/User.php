<?php

namespace Climactic\LaravelPolar\Tests\Fixtures;

use Climactic\LaravelPolar\Billable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Climactic\LaravelPolar\Tests\Fixtures\Factories\UserFactory;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
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
