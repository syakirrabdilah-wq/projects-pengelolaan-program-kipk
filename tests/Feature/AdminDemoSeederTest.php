<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class AdminDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_seeder_creates_three_admins_with_hashed_environment_passwords(): void
    {
        app()['env'] = 'local';
        config([
            'admin_demo.admins' => [
                ['username' => 'admin1', 'password' => 'local-test-secret-one'],
                ['username' => 'admin2', 'password' => 'local-test-secret-two'],
                ['username' => 'admin3', 'password' => 'local-test-secret-three'],
            ],
        ]);

        $seeder = new AdminDemoSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('users', 3);

        foreach (range(1, 3) as $number) {
            $user = User::where('username', "admin{$number}")->firstOrFail();

            $this->assertSame('admin', $user->role);
            $this->assertTrue($user->aktif);
            $this->assertTrue(Hash::check('local-test-secret-'.str_replace(
                ['1', '2', '3'],
                ['one', 'two', 'three'],
                (string) $number,
            ), $user->password));
        }
    }

    public function test_seeder_does_not_create_demo_admins_outside_local_environment(): void
    {
        app()['env'] = 'production';

        (new AdminDemoSeeder)->run();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_seeder_requires_all_three_admin_passwords(): void
    {
        app()['env'] = 'local';
        config([
            'admin_demo.admins' => [
                ['username' => 'admin1', 'password' => 'local-test-secret-one'],
                ['username' => 'admin2', 'password' => ''],
                ['username' => 'admin3', 'password' => 'local-test-secret-three'],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);

        (new AdminDemoSeeder)->run();
    }
}
