<?php

namespace Tests\Feature\Services\Bot\Scenario\Middleware;

use App\Contracts\Bot\Scenario\Middleware\Cases\PoliticsMiddlewareInterface;
use App\Services\Bot\Scenario\Middleware\BotMiddlewarePipeline;
use Tests\BotTestCase;

class BotMiddlewarePipelineTest extends BotTestCase
{
    public function test_pipeline_calls_politics_middleware()
    {
        $context = $this->makeBotContext();


        $politics = $this->mock(
            PoliticsMiddlewareInterface::class
        );


        $politics
            ->shouldReceive('handle')
            ->once()
            ->with($context);


        $pipeline = new BotMiddlewarePipeline(
            $politics
        );


        $pipeline->handle($context);
    }
}
