<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Cases\PublicInquiry;

use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreatePiSlaException;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Cases\PublicInquiry\SlaExceptionController;
use Olcs\Form\Model\Form\PublicInquirySlaException;
use Olcs\Mvc\Controller\Plugin\Placeholder;

final class SlaExceptionControllerTest extends MockeryTestCase
{
    private const SLA_DESCRIPTION = 'Test SLA group';

    public function testAddActionSendsSelectedSlaExceptionToApi(): void
    {
        $caseId = 123;
        $slaExceptionId = 456;
        $postData = [
            'fields' => [
                'case' => (string) $caseId,
                'slaException' => (string) $slaExceptionId,
            ],
        ];

        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $flashMessenger = m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);

        $controller = m::mock(SlaExceptionController::class, [
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $params = m::mock(Params::class);
        $params->shouldReceive('fromRoute')->with('action')->andReturn('add');
        $params->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $params->shouldReceive('fromPost')->withNoArgs()->andReturn($postData);
        $params->shouldReceive('fromPost')->with('form-actions')->andReturn(null);

        $placeholder = m::mock(Placeholder::class);
        $placeholder->shouldReceive('setPlaceholder')->with('form', m::type(Form::class))->once();
        $placeholder->shouldReceive('setPlaceholder')->with('contentTitle', 'Add SLA Exception')->once();

        $form = $this->createSlaExceptionForm($slaExceptionId);

        $controller->shouldReceive('plugin')->with('params')->andReturn($params);
        $controller->shouldReceive('params')->andReturn($params);
        $controller->shouldReceive('placeholder')->andReturn($placeholder);
        $controller->shouldReceive('getRequest')->andReturn($request);
        $controller->shouldReceive('getForm')->with(PublicInquirySlaException::class)->andReturn($form);

        $formHelper->shouldReceive('processAddressLookupForm')->with($form, $request)->andReturn(false)->once();

        $response = m::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(true)->once();
        $response->shouldReceive('getResult')->andReturn([])->once();

        $controller->shouldReceive('handleCommand')
            ->with(m::on(function ($command) use ($caseId, $slaExceptionId): bool {
                self::assertInstanceOf(CreatePiSlaException::class, $command);
                self::assertSame($caseId, $command->getCase());
                self::assertSame($slaExceptionId, $command->getSlaException());

                return true;
            }))
            ->andReturn($response)
            ->once();

        $flashMessenger->shouldReceive('addSuccessMessage')
            ->with('SLA Exception added successfully')
            ->once();

        $controller->shouldReceive('redirectTo')->with([])->andReturn('redirected')->once();

        self::assertSame('redirected', $controller->addAction());
    }

    private function createSlaExceptionForm(int $slaExceptionId): Form
    {
        $form = new Form();
        $fields = new Fieldset('fields');
        $fields->add(new Hidden('case'));

        $slaException = new Select('slaException');
        $slaException->setValueOptions([
            self::SLA_DESCRIPTION => [
                'label' => self::SLA_DESCRIPTION,
                'options' => [
                    $slaExceptionId => 'A pending court case',
                ],
            ],
        ]);
        $fields->add($slaException);
        $form->add($fields);

        return $form;
    }
}
