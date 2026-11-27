<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Application\Controller;

use Common\Filter\DateSelectNullifier;
use Common\Form\Elements\Validators\DateNotInFuture;
use Common\Form\Form;
use Common\Form\View\Helper\FormDateSelect;
use Common\InputFilter\PersonDateOfBirth;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Application\Controller\TransportManagersController;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Element\Select;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Form\View\Helper\FormInput;
use Laminas\Form\View\Helper\FormSelect;
use Laminas\Http\Request;
use Laminas\I18n\Translator\Translator;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Stdlib\Parameters;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

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
        $detailsFieldset = new Fieldset('details');
        $detailsFieldset->add(new Text('emailAddress'));
        $dateElement = new DateSelect('birthDate');
        $dateElement->setAttribute('id', 'dob');
        $detailsFieldset->add($dateElement);
        $homeAddress = new Fieldset('homeAddress');
        $workAddress = new Fieldset('workAddress');
        foreach ([$homeAddress, $workAddress] as $address) {
            foreach (['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town', 'postcode'] as $field) {
                $address->add(new Text($field));
            }
            $address->add(new Select('countryCode'));
            $searchPostcode = new Fieldset('searchPostcode');
            $searchPostcode->add(new Text('postcode'));
            $address->add($searchPostcode);
        }
        $form->expects(self::exactly(2))->method('get')->willReturnMap([
            ['details', $detailsFieldset],
            ['homeAddress', $homeAddress],
        ]);
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

        $helpers = new HelperPluginManager($this->createStub(ContainerInterface::class));
        $helpers->setService('forminput', new FormInput());
        $view = new PhpRenderer();
        $view->setHelperPluginManager($helpers);
        $dateHelper = new FormDateSelect();
        $dateHelper->setView($view);
        $dateHelper->setTranslator(new Translator());
        $document = new \DOMDocument();
        $document->loadHTML($dateHelper->render($dateElement));
        $xpath = new \DOMXPath($document);
        foreach (['day', 'month', 'year'] as $part) {
            $input = '//input[@id="dob_' . $part . '"]';
            self::assertSame(1, $xpath->query($input)->length);
            self::assertSame('bday-' . $part, $xpath->evaluate('string(' . $input . '/@autocomplete)'));
        }

        $inputHelper = new FormInput();
        $selectHelper = new FormSelect();
        foreach (
            [
                'addressLine1' => 'address-line1',
                'addressLine2' => 'address-line2',
                'addressLine3' => 'address-line3',
                'town' => 'address-level2',
                'postcode' => 'postal-code',
                'countryCode' => 'country',
            ] as $field => $autocomplete
        ) {
            $element = $homeAddress->get($field);
            $markup = $element instanceof Select ? $selectHelper->render($element) : $inputHelper->render($element);
            $document = new \DOMDocument();
            $document->loadHTML($markup);
            $xpath = new \DOMXPath($document);
            self::assertSame($autocomplete, $xpath->evaluate('string(//*[@name="' . $field . '"]/@autocomplete)'));
            self::assertNull($workAddress->get($field)->getAttribute('autocomplete'));
        }
        self::assertSame(
            'postal-code',
            $homeAddress->get('searchPostcode')->get('postcode')->getAttribute('autocomplete')
        );
        self::assertNull($homeAddress->get('addressLine4')->getAttribute('autocomplete'));
        self::assertNull($workAddress->get('searchPostcode')->get('postcode')->getAttribute('autocomplete'));
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
