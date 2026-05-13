<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Tariff;
use App\Models\Bot\User;
use App\Models\Bot\UserTariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTariffTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_user_tariff(): void
    {
        $tariff = UserTariff::factory()->create();

        $this->assertDatabaseHas('user_tariffs', [
            'id' => $tariff->id,
        ]);
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $tariff = UserTariff::factory()
            ->forUser($user)
            ->create();

        $this->assertInstanceOf(User::class, $tariff->user);
        $this->assertEquals($user->id, $tariff->user->id);
    }

    public function test_it_belongs_to_tariff(): void
    {
        $plan = Tariff::factory()->create();

        $tariff = UserTariff::factory()
            ->forTariff($plan)
            ->create();

        $this->assertInstanceOf(Tariff::class, $tariff->tariff);
        $this->assertEquals($plan->id, $tariff->tariff->id);
    }

    public function test_filter_by_user_id(): void
    {
        $user = User::factory()->create();

        UserTariff::factory()->forUser($user)->create();
        UserTariff::factory()->create();

        $result = UserTariff::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_by_tariff_id(): void
    {
        $plan = Tariff::factory()->create();

        UserTariff::factory()->forTariff($plan)->create();
        UserTariff::factory()->create();

        $result = UserTariff::query()
            ->byTariffId($plan->id)
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_scope_is_expired(): void
    {
        UserTariff::factory()->create([
            'date_stop' => now()->subDay(),
        ]);

        UserTariff::factory()->create([
            'date_stop' => now()->addDay(),
        ]);

        $result = UserTariff::query()->isExpired()->get();

        $this->assertCount(1, $result);
    }

    public function test_scope_where_start_after(): void
    {
        UserTariff::factory()->create([
            'date_start' => now()->addDays(5),
        ]);

        UserTariff::factory()->create([
            'date_start' => now()->subDays(5),
        ]);

        $result = UserTariff::query()
            ->whereStartAfter(now())
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_scope_where_start_before(): void
    {
        UserTariff::factory()->create([
            'date_start' => now()->subDays(5),
        ]);

        UserTariff::factory()->create([
            'date_start' => now()->addDays(5),
        ]);

        $result = UserTariff::query()
            ->whereStartBefore(now())
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_scope_where_end_after(): void
    {
        UserTariff::factory()->create([
            'date_stop' => now()->addDays(5),
        ]);

        UserTariff::factory()->create([
            'date_stop' => now()->subDays(5),
        ]);

        $result = UserTariff::query()
            ->whereEndAfter(now())
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_scope_where_end_before(): void
    {
        UserTariff::factory()->create([
            'date_stop' => now()->subDays(5),
        ]);

        UserTariff::factory()->create([
            'date_stop' => now()->addDays(5),
        ]);

        $result = UserTariff::query()
            ->whereEndBefore(now())
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_is_current_tariff(): void
    {
        UserTariff::factory()->create([
            'date_start' => now()->subDay(),
            'date_stop' => now()->addDay(),
        ]);

        UserTariff::factory()->create([
            'date_start' => now()->addDays(2),
            'date_stop' => now()->addDays(5),
        ]);

        $result = UserTariff::query()
            ->isCurrentTariff()
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_is_active_helper_returns_true_for_current_tariff(): void
    {
        $tariff = UserTariff::factory()->create([
            'date_start' => now()->subDay(),
            'date_stop' => now()->addDay(),
        ]);

        $this->assertTrue($tariff->isActive());
    }

    public function test_is_active_helper_returns_false_for_expired_tariff(): void
    {
        $tariff = UserTariff::factory()->create([
            'date_start' => now()->subDays(10),
            'date_stop' => now()->subDay(),
        ]);

        $this->assertFalse($tariff->isActive());
    }

    public function test_is_active_helper_returns_true_when_date_stop_is_null(): void
    {
        $tariff = UserTariff::factory()->create([
            'date_start' => now()->subDay(),
            'date_stop' => null,
        ]);

        $this->assertTrue($tariff->isActive());
    }

    public function test_is_active_helper_returns_false_for_future_tariff(): void
    {
        $tariff = UserTariff::factory()->create([
            'date_start' => now()->addDay(),
            'date_stop' => now()->addDays(10),
        ]);

        $this->assertFalse($tariff->isActive());
    }
}
