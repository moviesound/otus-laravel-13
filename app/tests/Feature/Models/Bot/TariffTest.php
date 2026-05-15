<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Tariff;
use App\Models\Bot\UserTariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TariffTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_tariff(): void
    {
        $tariff = Tariff::factory()->create([
            'name' => 'Basic',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('tariffs', [
            'id' => $tariff->id,
            'name' => 'Basic',
        ]);
    }

    public function test_filter_active(): void
    {
        Tariff::factory()->active()->create();
        Tariff::factory()->inactive()->create();

        $result = Tariff::query()->isActive()->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->status);
    }

    public function test_it_filter_inactive(): void
    {
        Tariff::factory()->active()->create();
        Tariff::factory()->inactive()->create();

        $result = Tariff::query()->isInactive()->get();

        $this->assertCount(1, $result);
        $this->assertEquals(0, $result->first()->status);
    }

    public function test_it_filter_default(): void
    {
        Tariff::factory()->create(['default' => 1]);
        Tariff::factory()->create(['default' => 0]);

        $result = Tariff::query()->isDefault()->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->default);
    }

    public function test_filter_autoprolong(): void
    {
        Tariff::factory()->create(['autoprolong' => 1]);
        Tariff::factory()->create(['autoprolong' => 0]);

        $result = Tariff::query()->isAutoprolong()->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->autoprolong);
    }

    public function test_filter_prolongation(): void
    {
        Tariff::factory()->create(['prolongation' => 1]);
        Tariff::factory()->create(['prolongation' => 0]);

        $result = Tariff::query()->isProlongationActive()->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->prolongation);
    }

    public function test_filter_by_cost_range(): void
    {
        Tariff::factory()->create(['cost' => 100]);
        Tariff::factory()->create(['cost' => 500]);
        Tariff::factory()->create(['cost' => 1000]);

        $result = Tariff::query()
            ->whereCostGreaterThan(400)
            ->whereCostLessThan(900)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals(500, (float)$result->first()->cost);
    }

    public function test_it_has_user_tariffs_relation(): void
    {
        $tariff = Tariff::factory()->create();

        UserTariff::factory()->create([
            'tariff_id' => $tariff->id,
        ]);

        UserTariff::factory()->create([
            'tariff_id' => $tariff->id,
        ]);

        $this->assertCount(2, $tariff->refresh()->userTariffs);
    }
}
