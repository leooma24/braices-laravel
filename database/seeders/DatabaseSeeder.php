<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'create properties']);
        Permission::create(['name' => 'edit properties']);
        Permission::create(['name' => 'delete properties']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());
        $roleUser = Role::create(['name' => 'user'])
            ->givePermissionTo(['create users', 'edit users', 'delete users']);;

        DB::table('users')->insert([
            'name' => 'Omar Lerma',
            'email' => 'leooma@hotmail.com',
            'phone_number' => '1234567890',
            'facebook' => 'https://www.facebook.com/leooma',
            'instagram' => 'https://www.instagram.com/leooma',
            'tiktok' => 'https://www.tiktok.com/leooma',
            'x' => 'https://www.x.com/leooma',
            'photo' => 'leooma.jpg',
            'password' => Hash::make('leonleon82'),
        ]);

        $user = User::find(1);
        $user->assignRole('admin');

        $user = DB::table('users')->insert([
            'name' => 'Alberto Montoya',
            'email' => 'amontoya@medioscorp.com',
            'password' => Hash::make('123456'),
        ]);

        $user = User::find(2);
        $user->assignRole('admin');

        $this->call([
            PropertyTypeSeeder::class,
            PropertyStatusSeeder::class,
            TransactionTypeSeeder::class,
            PropertySeeder::class,
        ]);
    }
}
