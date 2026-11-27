<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Application\Controller;

use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Validators\DateNotInFuture;
use Common\Form\Form;
use Common\InputFilter\PersonDateOfBirth;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Application\Controller\TransportManagersController;
use Laminas\Http\Request;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Stdlib\Parameters;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TransportManagerDraftDateOfBirthTest extends TestCase
{
    #[DataProvider('dateCases')]
    public function testDateValidationOnSaveCrudAndSubmit(
        string $action,
        array $date,
        string $otherField,
        bool $expectedValid,
        ?string $expectedDateError
    ): void {
        $birthDate = new PersonDateOfBirth('birthDate');
        $birthDate->setRequired(true);
        $birthDate->getFilterChain()->attach(new DateSelectNullifier());
        $birthDate->getValidatorChain()->attach(new DateNotInFuture());

        $details = new InputFilter();
        $details->add($birthDate);
        $otherInput = new Input('other');
        $otherInput->setRequired(true);
        $details->add($otherInput);
        $filter = new InputFilter();
        $filter->add($details, 'details');
        $filter->setData(['details' => ['birthDate' => $date, 'other' => $otherField]]);

        $form = $this->createMock(Form::class);
        $form->method('setData')->willReturnSelf();
        $form->method('getInputFilter')->willReturn($filter);
        $form->expects(self::once())->method('isValid')->willReturnCallback(function () use (
            $filter,
            $expectedValid,
            $expectedDateError
        ): bool {
            self::assertSame($expectedValid, $filter->isValid());
            $messages = $filter->getMessages();
            if ($expectedDateError !== null) {
                self::assertArrayHasKey($expectedDateError, $messages['details']['birthDate']);
            } else {
                self::assertArrayNotHasKey('birthDate', $messages['details'] ?? []);
            }
            return false;
        });

        $formHelper = $this->getMockBuilder(FormHelperService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['processAddressLookupForm'])
            ->getMock();
        $formHelper->expects(self::once())->method('processAddressLookupForm')->willReturn(false);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $post = match ($action) {
            'save' => ['form-actions' => ['save' => '']],
            'crud' => ['table' => ['action' => 'add']],
            default => ['form-actions' => ['submit' => '']],
        };
        $request->setPost(new Parameters($post));

        $controller = $this->getMockBuilder(TransportManagersController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequest', 'formatFormData', 'getDetailsForm', 'maybeSelectOptions', 'processFiles', 'renderWithForm'])
            ->getMock();
        $controller->method('getRequest')->willReturn($request);
        $controller->method('formatFormData')->willReturn([]);
        $controller->expects(self::once())->method('getDetailsForm')->willReturn($form);
        $controller->method('processFiles')->willReturn(false);
        $controller->method('renderWithForm')->willReturn(null);

        $formHelperProperty = new \ReflectionProperty($controller, 'formHelper');
        $formHelperProperty->setValue($controller, $formHelper);
        $flashProperty = new \ReflectionProperty($controller, 'flashMessengerHelper');
        $flashProperty->setValue($controller, $this->createStub(FlashMessengerHelperService::class));

        $page = new \ReflectionMethod($controller, 'page1Point1');
        $page->invoke($controller, ['application' => []]);
    }

    public static function dateCases(): array
    {
        $blank = ['day' => '', 'month' => '', 'year' => ''];
        $partial = ['day' => '12', 'month' => '', 'year' => '1990'];
        $invalid = ['day' => '31', 'month' => '2', 'year' => '1990'];
        $past = ['day' => '1', 'month' => '1', 'year' => '1990'];
        $future = ['day' => '1', 'month' => '1', 'year' => '9999'];

        return [
            'save allows blank and other incomplete fields' => ['save', $blank, '', true, null],
            'save allows past date' => ['save', $past, '', true, null],
            'save rejects partial date' => ['save', $partial, '', false, 'dateIncomplete'],
            'save rejects invalid date' => ['save', $invalid, '', false, 'dateInvalid'],
            'save rejects future date' => ['save', $future, '', false, 'inFuture'],
            'crud allows blank and other incomplete fields' => ['crud', $blank, '', true, null],
            'crud rejects future date' => ['crud', $future, '', false, 'inFuture'],
            'submit requires date' => ['submit', $blank, 'complete', false, 'dateMissing'],
            'submit rejects future date' => ['submit', $future, 'complete', false, 'inFuture'],
            'submit still requires other fields' => ['submit', $past, '', false, null],
            'submit accepts complete valid fields' => ['submit', $past, 'complete', true, null],
        ];
    }
}
