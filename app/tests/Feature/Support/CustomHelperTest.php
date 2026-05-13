<?php

namespace Tests\Feature\Support;

use Mockery;
use Tests\TestCase;
use App\Support\CustomHelper;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTimeInterface;
use Faker\Factory as FakerFactory;
use InvalidArgumentException;

class CustomHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    // canText

    public function test_can_text_function_returns_false_when_user_not_logged_in()
    {
        Auth::shouldReceive('guard->user')
            ->andReturn(null);

        $this->assertFalse(CustomHelper::canText('edit'));
    }

    public function test_can_text_function_returns_false_when_user_has_no_permission()
    {
        $user = Mockery::mock();

        $user->shouldReceive('can')
            ->once()
            ->with('texts.edit')
            ->andReturn(false);

        Auth::shouldReceive('guard')
            ->once()
            ->with('admin')
            ->andReturnSelf();

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $this->assertFalse(CustomHelper::canText('edit'));
    }

    public function test_can_text_function_returns_true_when_user_has_permission()
    {
        $user = Mockery::mock();

        $user->shouldReceive('can')
            ->once()
            ->with('texts.edit')
            ->andReturn(true);

        Auth::shouldReceive('guard')
            ->once()
            ->with('admin')
            ->andReturnSelf();

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $this->assertTrue(CustomHelper::canText('edit'));
    }
}
