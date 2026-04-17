<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\Auth\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Tests\TestCase;

class OtpAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // S'assurer que les rôles existent si nécessaire (RolePermissionSeeder)
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_can_send_otp(): void
    {
        $this->mock(SmsService::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')->once();
        });

        $response = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '90010203',
            'type' => 'login'
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Code OTP envoyé avec succès.');

        $this->assertDatabaseHas('otp_codes', [
            'phone' => '+22890010203',
            'is_used' => false
        ]);
    }

    public function test_can_verify_otp_and_create_new_user(): void
    {
        $phone = '+22890010203';
        $code = '123456';
        
        OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($code),
            'type' => 'login',
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'attempts' => 0
        ]);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '90010203',
            'code' => $code,
            'device_name' => 'Test Device'
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'access_token',
                    'user' => ['id', 'phone', 'role'],
                    'is_new_user'
                ]
            ])
            ->assertJsonPath('data.is_new_user', true);

        $this->assertDatabaseHas('users', ['phone' => $phone]);
        $this->assertDatabaseHas('otp_codes', ['phone' => $phone, 'is_used' => true]);
    }

    public function test_can_verify_otp_for_existing_user(): void
    {
        $phone = '+22890010203';
        $code = '123456';
        
        User::factory()->create(['phone' => $phone]);

        OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($code),
            'type' => 'login',
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'attempts' => 0
        ]);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '90010203',
            'code' => $code
        ]);

        $response->assertOk()
            ->assertJsonPath('data.is_new_user', false);
    }

    public function test_cannot_verify_expired_otp(): void
    {
        $phone = '+22890010203';
        $code = '123456';
        
        OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($code),
            'type' => 'login',
            'expires_at' => now()->subMinutes(1),
            'is_used' => false,
            'attempts' => 0
        ]);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '90010203',
            'code' => $code
        ]);

        $response->assertStatus(500); // Car AuthService lance une Exception générique capturée par try()
    }

    public function test_cannot_verify_with_wrong_code(): void
    {
        $phone = '+22890010203';
        $code = '123456';
        
        $otp = OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($code),
            'type' => 'login',
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'attempts' => 0
        ]);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '90010203',
            'code' => '000000'
        ]);

        $response->assertStatus(500);
        $this->assertEquals(1, $otp->fresh()->attempts);
    }

    public function test_otp_is_invalidated_after_3_failed_attempts(): void
    {
        $phone = '+22890010203';
        $code = '123456';
        
        $otp = OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($code),
            'type' => 'login',
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'attempts' => 2
        ]);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'phone' => '90010203',
            'code' => '000000'
        ]);

        $this->assertTrue($otp->fresh()->is_used);
        $this->assertEquals(3, $otp->fresh()->attempts);
    }

    public function test_send_otp_rate_limiting(): void
    {
        $phone = '+22890010203';
        
        for ($i = 0; $i < 5; $i++) {
            OtpCode::create([
                'phone' => $phone,
                'code' => 'hash',
                'type' => 'login',
                'expires_at' => now()->addMinutes(10),
                'created_at' => now()
            ]);
        }

        $response = $this->postJson('/api/v1/auth/otp/send', [
            'phone' => '90010203',
            'type' => 'login'
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('message', 'Trop de demandes d\'OTP. Réessayez dans 1 heure.');
    }
}
