<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva;

use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Application\Controller\PaymentSubmissionController;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Request;
use Laminas\Stdlib\Parameters;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * When the API refuses for a missing SMS number, the user is told why and sent to the addresses page.
 */
final class PaymentSubmissionSmsNumberTest extends MockeryTestCase
{
    private const string KEY = 'application.submit.sms-number-required';

    private m\MockInterface $sut;

    private m\MockInterface $translationHelper;

    private m\MockInterface $flashMessengerHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->translationHelper = m::mock(TranslationHelperService::class);
        $this->translationHelper->shouldReceive('translate')->with(self::KEY)->andReturn('Give an SMS number');
        $this->flashMessengerHelper = m::mock(FlashMessengerHelperService::class);

        $this->sut = m::mock(
            PaymentSubmissionController::class,
            [
                m::mock(NiTextTranslation::class),
                m::mock(AuthorizationService::class),
                $this->translationHelper,
                $this->flashMessengerHelper,
                m::mock(TableFactory::class),
                m::mock(FormHelperService::class),
                m::mock(RestrictionHelperService::class),
                m::mock(StringHelperService::class),
            ]
        )->makePartial()->shouldAllowMockingProtectedMethods();

        $this->sut->shouldReceive('getApplicationId')->andReturn(7);
    }

    private function refusal(): Response
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getResult')->andReturn(['messages' => [self::KEY]]);

        return $response;
    }

    private function expectSentToAddresses(): void
    {
        $this->sut->shouldReceive('addErrorMessage')->once()->with('Give an SMS number');
        $this->flashMessengerHelper->shouldReceive('addUnknownError')->never();
        $this->sut->shouldReceive('redirect->toRoute')
            ->once()
            ->with('lva-application/addresses', ['application' => 7])
            ->andReturn('ADDRESSES');
    }

    public function testPayPathRefusalShowsSmsMessage(): void
    {
        $this->sut->shouldReceive('url->fromRoute')->andReturn('https://redirect');
        $this->sut->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(PayOutstandingFees::class))
            ->andReturn($this->refusal());
        $this->sut->shouldReceive('addErrorMessage')->with('feeNotPaidError')->never();
        $this->expectSentToAddresses();

        $this->assertSame('ADDRESSES', $this->sut->indexAction());
    }

    public function testNoFeeSubmitRefusalShowsSmsMessage(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters(['version' => 3]));
        $this->sut->shouldReceive('getRequest')->andReturn($request);
        $this->sut->shouldReceive('isButtonPressed')->with('customCancel')->andReturn(false);
        $this->sut->shouldReceive('getOutstandingFeeDataForApplication')->with(7)->andReturn([]);
        $this->sut->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(SubmitApplication::class))
            ->andReturn($this->refusal());
        $this->expectSentToAddresses();

        $this->assertSame('ADDRESSES', $this->sut->payAndSubmitAction());
    }

    public function testOtherSubmitFailureStillShowsUnknownError(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters(['version' => 3]));
        $this->sut->shouldReceive('getRequest')->andReturn($request);
        $this->sut->shouldReceive('isButtonPressed')->with('customCancel')->andReturn(false);
        $this->sut->shouldReceive('getOutstandingFeeDataForApplication')->with(7)->andReturn([]);

        $response = m::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getResult')->andReturn(['messages' => ['something else']]);
        $this->sut->shouldReceive('handleCommand')->once()->andReturn($response);

        $this->flashMessengerHelper->shouldReceive('addUnknownError')->once();
        $this->sut->shouldReceive('redirect->toRoute')
            ->once()
            ->with('lva-application', ['application' => 7])
            ->andReturn('OVERVIEW');

        $this->assertSame('OVERVIEW', $this->sut->payAndSubmitAction());
    }
}
