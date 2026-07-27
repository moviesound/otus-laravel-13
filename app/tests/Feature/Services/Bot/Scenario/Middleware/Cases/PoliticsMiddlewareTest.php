<?php

namespace Tests\Feature\Services\Bot\Scenario\Middleware\Cases;

use App\Contracts\Bot\Repositories\StepRepositoryInterface;
use App\Services\Bot\Scenario\Middleware\Cases\PoliticsMiddleware;
use App\Services\Bot\Scenario\Politics\Steps\ConfirmAgreementPoliticsStep;
use Tests\BotTestCase;

class PoliticsMiddlewareTest extends BotTestCase
{
    public function test_middleware_redirects_user_without_politics_agreement()
    {
        $context = $this->makeBotContext(
            politicsAgreed: 0
        );


        $repository = $this->mock(
            StepRepositoryInterface::class
        );


        $repository
            ->shouldReceive('save')
            ->once()
            ->withArgs(function ($state) use ($context) {

                return
                    $state->scenario === 'onPolitics'
                    &&
                    $state->step === ConfirmAgreementPoliticsStep::STEP_KEY
                    &&
                    $state->userSocialId === $context->scenarioDTO->userSocialId;
            });


        $middleware = new PoliticsMiddleware(
            $repository
        );


        $middleware->handle($context);


        $this->assertEquals(
            'onPolitics',
            $context->scenarioDTO->scenario
        );


        $this->assertEquals(
            ConfirmAgreementPoliticsStep::STEP_KEY,
            $context->scenarioDTO->step
        );
    }


    public function test_middleware_does_nothing_when_user_agreed()
    {
        $context = $this->makeBotContext(
            politicsAgreed: 1
        );


        $repository = $this->mock(
            StepRepositoryInterface::class
        );


        $repository
            ->shouldNotReceive('save');


        $middleware = new PoliticsMiddleware(
            $repository
        );


        $middleware->handle($context);


        $this->assertEquals(
            'onPlanning',
            $context->scenarioDTO->scenario
        );
    }


    public function test_middleware_does_nothing_inside_politics_scenario()
    {
        $context = $this->makeBotContext(
            politicsAgreed: 0,
            scenario: 'onPolitics',
            step: ConfirmAgreementPoliticsStep::STEP_KEY
        );


        $repository = $this->mock(
            StepRepositoryInterface::class
        );


        $repository
            ->shouldNotReceive('save');


        $middleware = new PoliticsMiddleware(
            $repository
        );


        $middleware->handle($context);


        $this->assertEquals(
            'onPolitics',
            $context->scenarioDTO->scenario
        );
    }
}
