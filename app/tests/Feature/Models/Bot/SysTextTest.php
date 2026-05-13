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

    public function test_get_text_by_alias_and_lang(): void
    {
        SysText::factory()->create([
            'alias' => 'welcome',
            'lang' => 'ru',
            'context' => 'Добро пожаловать',
        ]);

        $result = SysText::get('welcome', 'ru');

        $this->assertEquals('Добро пожаловать', $result);
    }

    public function test_it_returns_alias_when_text_not_found(): void
    {
        $result = SysText::get('unknown_alias');

        $this->assertEquals('unknown_alias', $result);
    }

    public function test_it_fallbacks_to_russian_language(): void
    {
        SysText::factory()->create([
            'alias' => 'welcome',
            'lang' => 'ru',
            'context' => 'Добро пожаловать',
        ]);

        $result = SysText::get('welcome', 'en');

        $this->assertEquals('Добро пожаловать', $result);
    }

    public function test_it_replaces_placeholders(): void
    {
        SysText::factory()->create([
            'alias' => 'welcome',
            'lang' => 'ru',
            'context' => 'Привет, {#NAME#}',
        ]);

        $result = SysText::get('welcome', 'ru', [
            'name' => 'Alex',
        ]);

        $this->assertEquals('Привет, Alex', $result);
    }

    public function test_it_stores_text_in_cache(): void
    {
        Cache::flush();

        SysText::factory()->create([
            'alias' => 'welcome2',
            'lang' => 'ru',
            'context' => 'Добро пожаловать',
        ]);

        SysText::get('welcome2', 'ru');

        $cached = Cache::tags(['sys_text'])
            ->get('sys_text:ru:welcome2');

        $this->assertEquals('Добро пожаловать', $cached);
    }

    public function test_it_forgets_single_cache_entry(): void
    {
        Cache::tags(['sys_text'])
            ->put('sys_text:ru:welcome3', 'test');

        SysText::forget('welcome3', 'ru');

        $cached = Cache::tags(['sys_text'])
            ->get('sys_text:ru:welcome3');

        $this->assertNull($cached);
    }

    public function test_it_flushes_all_cache(): void
    {
        Cache::tags(['sys_text'])
            ->put('sys_text:ru:welcome4', 'test');
        Cache::tags(['sys_text'])
            ->put('sys_text:ru:welcome5', 'test');

        SysText::flushAll();

        $cached4 = Cache::tags(['sys_text'])
            ->get('sys_text:ru:welcome4');
        $cached5 = Cache::tags(['sys_text'])
            ->get('sys_text:ru:welcome5');

        $this->assertNull($cached4);
        $this->assertNull($cached5);
    }

    public function test_it_updates_cache_after_save(): void
    {
        $text = SysText::factory()->create([
            'alias' => 'welcome10',
            'lang' => 'ru',
            'context' => 'Old text',
        ]);

        $text->update([
            'context' => 'New text',
        ]);

        $cached = Cache::tags(['sys_text'])
            ->get('sys_text:ru:welcome10');

        $this->assertEquals('New text', $cached);
    }

    public function test_it_removes_cache_after_delete(): void
    {
        $text = SysText::factory()->create([
            'alias' => 'welcome11',
            'lang' => 'ru',
            'context' => 'Hello',
        ]);

        SysText::get('welcome11', 'ru');

        $text->delete();

        $cached = Cache::tags(['sys_text'])
            ->get('sys_text:ru:welcome11');

        $this->assertNull($cached);
    }
}
