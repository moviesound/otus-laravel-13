<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\User;
use App\Models\Bot\UserPhoneProof;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPhoneProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_user_phone_proof(): void
    {
        $proof = UserPhoneProof::factory()->create();

        $this->assertDatabaseHas('user_phone_proofs', [
            'id' => $proof->id,
        ]);
    }

    public function test_filter_by_user_id(): void
    {
        $user = User::factory()->create();

        $proof = UserPhoneProof::factory()->create([
            'user_id' => $user->id,
        ]);

        UserPhoneProof::factory()->create();

        $result = UserPhoneProof::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_by_phone(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'phone' => '+79999999999',
        ]);

        UserPhoneProof::factory()->create([
            'phone' => '+78888888888',
        ]);

        $result = UserPhoneProof::query()
            ->byPhone('+79999999999')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_status_sent(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'status' => 'sent',
        ]);

        UserPhoneProof::factory()->create([
            'status' => 'failed',
        ]);

        $result = UserPhoneProof::query()
            ->isStatusSent()
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_status_success(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'status' => 'success',
        ]);

        UserPhoneProof::factory()->create([
            'status' => 'sent',
        ]);

        $result = UserPhoneProof::query()
            ->isStatusSuccess()
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_status_failed(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'status' => 'failed',
        ]);

        UserPhoneProof::factory()->create([
            'status' => 'success',
        ]);

        $result = UserPhoneProof::query()
            ->isStatusFailed()
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_status_wrong_code(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'status' => 'wrong-code',
        ]);

        UserPhoneProof::factory()->create([
            'status' => 'success',
        ]);

        $result = UserPhoneProof::query()
            ->isStatusWrongCode()
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_status_success_code(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'status' => 'success-code',
        ]);

        UserPhoneProof::factory()->create([
            'status' => 'failed',
        ]);

        $result = UserPhoneProof::query()
            ->isStatusSuccessCode()
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_by_code(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'code' => '1234',
        ]);

        UserPhoneProof::factory()->create([
            'code' => '9999',
        ]);

        $result = UserPhoneProof::query()
            ->byCode('1234')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proof->id,
            $result->first()->id
        );
    }

    public function test_filter_times_greater_less_than(): void
    {
        $proofGreater = UserPhoneProof::factory()->create([
            'times' => 5,
        ]);

        $proofLess = UserPhoneProof::factory()->create([
            'times' => 1,
        ]);

        $result = UserPhoneProof::query()
            ->whereTimesGreaterThan(3)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proofGreater->id,
            $result->first()->id
        );

        $result = UserPhoneProof::query()
            ->whereTimesLessThan(3)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $proofLess->id,
            $result->first()->id
        );
    }

    public function test_filter_created_after(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'created_at' => now()->subHour(),
        ]);

        UserPhoneProof::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $result = UserPhoneProof::query()
            ->whereCreatedAfter(now()->subDay())
            ->get();

        $this->assertTrue(
            $result->contains('id', $proof->id)
        );
    }

    public function test_filter_created_before(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        UserPhoneProof::factory()->create([
            'created_at' => now(),
        ]);

        $result = UserPhoneProof::query()
            ->whereCreatedBefore(now()->subDay())
            ->get();

        $this->assertTrue(
            $result->contains('id', $proof->id)
        );
    }

    public function test_filter_created_between(): void
    {
        $proof = UserPhoneProof::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        UserPhoneProof::factory()->create([
            'created_at' => now()->subDays(10),
        ]);

        $result = UserPhoneProof::query()
            ->whereCreatedBetween(
                now()->subDays(3),
                now()->subDay()
            )
            ->get();

        $this->assertTrue(
            $result->contains('id', $proof->id)
        );
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $proof = UserPhoneProof::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(
            User::class,
            $proof->user
        );

        $this->assertEquals(
            $user->id,
            $proof->user->id
        );
    }
}
