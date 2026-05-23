<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\SysText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SysTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_sys_text(): void
    {
        $text = SysText::factory()->create([
            'alias' => 'welcome',
            'lang' => 'ru',
            'context' => 'Добро пожаловать',
        ]);

        $this->assertDatabaseHas('sys_texts', [
            'id' => $text->id,
            'alias' => 'welcome',
        ]);
    }

    public function test_filter_by_alias(): void
    {
        SysText::factory()->create([
            'alias' => 'welcome',
        ]);

        SysText::factory()->create([
            'alias' => 'bye',
        ]);

        $result = SysText::query()
            ->byAlias('welcome')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('welcome', $result->first()->alias);
    }

    public function test_filter_by_alias_like(): void
    {
        SysText::factory()->create([
            'alias' => 'welcome_message',
        ]);

        SysText::factory()->create([
            'alias' => 'bye_message',
        ]);

        $result = SysText::query()
            ->byAlias('%welcome%')
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_by_lang(): void
    {
        $text = SysText::factory()->create([
            'lang' => 'ru',
        ]);

        SysText::factory()->create([
            'lang' => 'en',
        ]);

        $result = SysText::query()
            ->byLang('ru')
            ->where('id', $text->id)
            ->get();

        $this->assertTrue(
            $result->contains('id', $text->id)
        );
        $this->assertEquals('ru', $result->first()->lang);
    }

    public function test_filter_by_alias_like_special_scope(): void
    {
        SysText::factory()->create([
            'alias' => 'registration_success',
        ]);

        SysText::factory()->create([
            'alias' => 'payment_error',
        ]);

        $result = SysText::query()
            ->whereAliasLike('registration')
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_by_context_like(): void
    {
        SysText::factory()->create([
            'context' => 'Hello client',
        ]);

        SysText::factory()->create([
            'context' => 'Payment completed',
        ]);

        $result = SysText::query()
            ->whereContextLike('%client%')
            ->get();

        $this->assertCount(1, $result);
    }
}
