<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@society.com',
            'password' => Hash::make('password'),
            'mobile' => '+91 98765 43210',
            'status' => 'active',
        ]);

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $superAdmin->roles()->attach($superAdminRole->id);

        $societyAdmins = [
            ['name' => 'Ramesh Agarwal', 'email' => 'ramesh.agarwal@greenpark.org', 'mobile' => '+91 98765 43210'],
            ['name' => 'Priya Sharma', 'email' => 'priya.sharma@skylineapts.com', 'mobile' => '+91 91234 56789'],
            ['name' => 'Amit Reddy', 'email' => 'amit.reddy@sunriseresidency.in', 'mobile' => '+91 90000 11122'],
            ['name' => 'Neha Kapoor', 'email' => 'neha.kapoor@oceanview.org', 'mobile' => '+91 99887 76655'],
            ['name' => 'Vikram Iyer', 'email' => 'vikram.iyer@maplewoodhomes.in', 'mobile' => '+91 97654 32109'],
            ['name' => 'Suresh Singh', 'email' => 'suresh.singh@silverstoneapts.com', 'mobile' => '+91 98221 33445'],
            ['name' => 'Deepak Mehta', 'email' => 'deepak.mehta@greengate.com', 'mobile' => '+91 93456 77889'],
        ];

        $societyAdminRole = Role::where('name', 'society_admin')->first();

        // Link each society admin to a distinct society (in creation order) so
        // the society panel scopes its data to the signed-in admin's society.
        $societyIds = Society::orderBy('id')->pluck('id')->all();

        foreach ($societyAdmins as $index => $admin) {
            $user = User::create([
                'society_id' => $societyIds[$index] ?? null,
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make('password'),
                'mobile' => $admin['mobile'],
                'status' => 'active',
            ]);
            $user->roles()->attach($societyAdminRole->id);
        }
    }
}
