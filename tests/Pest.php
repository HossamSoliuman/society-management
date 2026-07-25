<?php

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function linkSocietyAdmin(User $user, Society $society): void
{
    $role = Role::firstOrCreate(
        ['name' => 'society_admin'],
        ['display_name' => 'Society Admin', 'status' => 'active']
    );

    $user->update(['society_id' => $society->id, 'status' => 'active']);
    $user->roles()->syncWithoutDetaching([$role->id]);
}

function linkSuperAdmin(User $user): void
{
    $role = Role::firstOrCreate(
        ['name' => 'super_admin'],
        ['display_name' => 'Super Admin', 'status' => 'active']
    );

    $user->update(['society_id' => null, 'status' => 'active']);
    $user->roles()->syncWithoutDetaching([$role->id]);
}
