<?php

namespace Dvsa\OlcsTest\FormTester;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicSelect;
use Common\Service\Translator\TranslationLoader;
use Common\Validator as CommonValidator;
use Dvsa\Olcs\Transfer\Validators as TransferValidator;
use Laminas\Form\Element\DateSelect;
use Laminas\Form\Element\DateTimeSelect;
use Laminas\Form\Element\MonthSelect;
use Laminas\Form\ElementInterface;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Validator;

/**
 * Class AbstractFormValidationTest
 */
abstract class AbstractFormValidationTestCase extends TestCase
{
    /**
     * @var string The class name of the form being tested
     */
    protected $formName;

    /**
     * @var \Common\Form\Form
     */
    protected $sut;

    /**
     * If you intentionally want to skip tests on an element they can be added here
     * @var array List of form elements eg (fields.numOfCows) that have been tested
     */
    protected static $testedElements = [];

    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * @var array
     */
    private static $forms = [];

    /**
     * Setup
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function setUp(): void
    {
        // sut is not needed for the 'testMissingTest' tests, and it slows it down a lot
        if (strpos($this->getName(), 'testMissingTest') === false) {
            $this->sut = $this->getForm();
        }
    }

    /**
     * We can access service manager if we need to add a mock for certain applications
     *
     * @return \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected function getServiceManager()
    {
        if ($this->serviceManager === null) {
            $this->serviceManager = self::getRealServiceManager();

            // inject a real string helper

            $this->serviceManager->setAllowOverride(true);

            $this->serviceManager->get('FormElementManager')->setFactory(
                'DynamicSelect',
                function ($serviceLocator, $name, $requestedName) {
                    $element = new DynamicSelect();
                    $element->setValueOptions(
                        [
                            '1' => 'one',
                            '2' => 'two',
                            '3' => 'three'
                        ]
                    );
                    return $element;
                }
            );

            $this->serviceManager->get('FormElementManager')->setFactory(
                'DynamicRadio',
                function ($serviceLocator, $name, $requestedName) {
                    $element = new DynamicRadio();
                    $element->setValueOptions(
                        [
                            '1' => 'one',
                            '2' => 'two',
                            '3' => 'three'
                        ]
                    );
                    return $element;
                }
            );

            $this->serviceManager->get('FormElementManager')->setFactory(
                'Common\Form\Element\DynamicMultiCheckbox',
                function ($serviceLocator, $name, $requestedName) {
                    $element = new DynamicMultiCheckbox();
                    $element->setValueOptions(
                        [
                            '1' => 'one',
                            '2' => 'two',
                            '3' => 'three'
                        ]
                    );
                    return $element;
                }
            );
        }

        return $this->serviceManager;
    }

    public static function getRealServiceManager()
    {
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', include __DIR__ . '/../../config/application.config.php');
        $serviceManager->get('ModuleManager')->loadModules();
        $serviceManager->setAllowOverride(true);

        $mockTranslationLoader = m::mock(TranslationLoader::class);
        $mockTranslationLoader->shouldReceive('load')->andReturn(['default' => ['en_GB' => []]]);
        $mockTranslationLoader->shouldReceive('loadReplacements')->andReturn([]);
        $serviceManager->setService(TranslationLoader::class, $mockTranslationLoader);

        $pluginManager = new LoaderPluginManager($serviceManager);
        $pluginManager->setService(TranslationLoader::class, $mockTranslationLoader);
        $serviceManager->setService('TranslatorPluginManager', $pluginManager);

        // Mess up the backend, so any real rest calls will fail
        $config = $serviceManager->get('Config');
        $config['service_api_mapping']['endpoints']['backend'] = 'http://some-fake-backend/';
        $serviceManager->setService('Config', $config);

        return $serviceManager;
    }

    /**
     * Get the form object
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        if ($this->formName == null) {
            throw new \Exception('formName property is not defined');
        }

        if (!isset(self::$forms[$this->formName])) {
            /** @var \Common\Form\Annotation\CustomAnnotationBuilder $c */
            $frmAnnotBuilder = $this->getServiceManager()->get('FormAnnotationBuilder');

            self::$forms[$this->formName] = $frmAnnotBuilder->createForm($this->formName);
        }

        return clone self::$forms[$this->formName];
    }

    /**
     * Assert that a form element with a value is valid
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param mixed $value            The value to be tested in the form element
     * @param array $context          Form data context required to test the validation
     *
     * @return void
     */
    protected function assertFormElementValid(array $elementHierarchy, $value, array $context = [])
    {
        self::$testedElements[implode('.', $elementHierarchy)] = true;

        $this->assertElementExists($elementHierarchy);
        $this->setData($elementHierarchy, $value, $context);
        $this->setValidationGroup($elementHierarchy);

        $valid = $this->sut->isValid();
        $message = sprintf(
            '"%s" form element with value "%s" should be valid : %s',
            implode('.', $elementHierarchy),
            print_r($value, true),
            implode(', ', array_keys($this->getFormMessages($elementHierarchy)))
        );

        $this->assertTrue($valid, $message);
    }

    /**
     * Get the form validation messages for an element
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return array
     */
    protected function getFormMessages(array $elementHierarchy)
    {
        $messages = $this->sut->getMessages();
        foreach ($elementHierarchy as $name) {
            if (isset($messages[$name])) {
                $messages = $messages[$name];
            }
        }
        return $messages;
    }

    /**
     * Set the validation group so that ony the form element is validated
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function setValidationGroup(array $elementHierarchy)
    {
        $array = null;
        foreach (array_reverse($elementHierarchy) as $name) {
            if ($array == null) {
                $array = [$name];
            } else {
                $array = [$name => $array];
            }
        }

        $this->sut->setValidationGroup($array);
    }

    /**
     * Set the form data
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param mixed $value            Form element (being tested) value
     * @param array $context          Form data context required to test the validation
     *
     * @return void
     */
    protected function setData(array $elementHierarchy, $value, $context = [])
    {
        $element = $this->getElementByHierarchy($elementHierarchy);
        if (empty($value) && $element instanceof MonthSelect) {
            $value = [
                'month' => null,
                'year' => null,
            ];
            if($element instanceof DateSelect) {
                $value['day'] = null;
            }
            if($element instanceof DateTimeSelect) {
                $value['hour'] = null;
                $value['minute'] = null;
                $value['second'] = null;
            }
        }

        $array = $value;
        foreach (array_reverse($elementHierarchy) as $name) {
            $array = [$name => $array];
        }
        $this->sut->setData(array_merge_recursive($context, $array));
    }

    /**
     * Assert that the form element exists in the form
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertElementExists(array $elementHierarchy)
    {
        try {
            $this->getFormElement($elementHierarchy);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Get the form element
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return \Laminas\Form\Element
     */
    protected function getFormElement(array $elementHierarchy)
    {
        $element = $this->sut;
        foreach ($elementHierarchy as $name) {
            if (!$element->has($name)) {
                throw new \Exception(
                    sprintf('Cannot find element by name "%s" in "%s"', $name, implode('.', $elementHierarchy))
                );
            }
            $element = $element->get($name);
        }
        return $element;
    }

    /**
     * Assert the type of a form element
     *
     * @param array  $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param string $type             Class name of the type
     *
     * @return void
     */
    protected function assertFormElementType(array $elementHierarchy, $type)
    {
        $this->assertInstanceOf($type, $this->getFormElement($elementHierarchy));
    }

    /**
     * Assert that a form element with a value is NOT valid
     *
     * @param array        $elementHierarchy   Form element name eg ['fields','numOfCows']
     * @param mixed        $value              The value to be tested in the form element
     * @param string|array $validationMessages A single or an array of expected validation messages keys
     * @param array        $context            Form data context required to test the validation
     *
     * @return void
     */
    protected function assertFormElementNotValid(
        array $elementHierarchy,
        $value,
        $validationMessages,
        array $context = []
    ) {
        self::$testedElements[implode('.', $elementHierarchy)] = true;

        if (!is_array($validationMessages)) {
            $validationMessages = [$validationMessages];
        }

        $this->assertElementExists($elementHierarchy);
        $this->setData($elementHierarchy, $value, $context);
        $this->setValidationGroup($elementHierarchy);

        $valid = $this->sut->isValid();

        $this->assertFalse(
            $valid,
            sprintf(
                '"%s" form element with value "%s" should *not* be valid',
                implode('.', $elementHierarchy),
                print_r($value, true)
            )
        );

        $errorMessages = array_keys($this->getFormMessages($elementHierarchy));
        // If error messages has no keys, it is probably because the top level ErrorMessage has been used
        // therefore check the contents of the error, rather than the key
        if (array_keys($this->getFormMessages($elementHierarchy)) === [0 => 0]) {
            $errorMessages = $this->getFormMessages($elementHierarchy);
        }

        $this->assertSame(
            $validationMessages,
            $errorMessages,
            sprintf(
                '"%s" form element with value "%s" error messages not as expected',
                implode('.', $elementHierarchy),
                print_r($value, true)
            )
        );
    }

    /**
     * Assert than a form element is a text input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param int   $min              Minimum allowed string length
     * @param int   $max              Maximum allowed string length
     * @param array $context          Any form context required for this validation
     *
     * @return void
     */
    protected function assertFormElementText(
        $elementHierarchy,
        $min = 0,
        $max = null,
        array $context = []
    ) {
        if ($min > 0) {
            $this->assertFormElementValid($elementHierarchy, str_pad('', $min, 'x'), $context);
        }
        if ($min > 1) {
            $this->assertFormElementNotValid(
                $elementHierarchy,
                str_pad('', $min - 1, 'x'),
                Validator\StringLength::TOO_SHORT,
                $context
            );
        } else {
            $this->assertFormElementValid($elementHierarchy, 'x', $context);
        }

        if ($max !== null) {
            $this->assertFormElementValid($elementHierarchy, str_pad('', $max, 'x'), $context);
            $this->assertFormElementNotValid(
                $elementHierarchy,
                str_pad('', $max + 1, 'x'),
                Validator\StringLength::TOO_LONG,
                $context
            );
        }
    }

    /**
     * Assert than a form element is a number input
     *
     * @param array        $elementHierarchy   Form element name eg ['fields','numOfCows']
     * @param int          $min                Minimum allowed value
     * @param int          $max                Maximum allowed value
     * @param string|array $validationMessages A single or an array of expected validation messages keys
     *
     * @return void
     */
    protected function assertFormElementNumber($elementHierarchy, $min = 0, $max = null, $validationMessages = null)
    {
        $this->assertFormElementValid($elementHierarchy, $min);
        $this->assertFormElementValid($elementHierarchy, $min + 1);

        if ($min > 0) {
            $this->assertFormElementNotValid(
                $elementHierarchy,
                $min - 1,
                $validationMessages ? : Validator\Between::NOT_BETWEEN
            );
        }

        if ($max !== null) {
            $this->assertFormElementValid($elementHierarchy, $max);
            $this->assertFormElementNotValid(
                $elementHierarchy,
                $max + 1,
                $validationMessages ? : Validator\Between::NOT_BETWEEN
            );
        }

        if ($validationMessages === null) {
            $validationMessages = [Validator\Digits::NOT_DIGITS];

            if ($min > 0 || $max !== null) {
                $validationMessages[] = Validator\Between::VALUE_NOT_NUMERIC;
            }

            $this->assertFormElementNotValid($elementHierarchy, 'X', $validationMessages);
        }
    }

    /**
     * Assert than a form element is a float input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param int   $min              Minimum allowed value
     * @param int   $max              Maximum allowed value
     *
     * @return void
     */
    protected function assertFormElementFloat($elementHierarchy, $min = 0, $max = null)
    {
        $this->assertFormElementValid($elementHierarchy, $min);
        $this->assertFormElementValid($elementHierarchy, $min + 0.1);

        if ($min > 0) {
            $this->assertFormElementNotValid($elementHierarchy, $min - 0.1, Validator\Between::NOT_BETWEEN);
        }

        if ($max !== null) {
            $this->assertFormElementValid($elementHierarchy, $max);
            $this->assertFormElementNotValid($elementHierarchy, $max + 0.1, Validator\LessThan::NOT_LESS_INCLUSIVE);
        }

        $this->assertFormElementNotValid($elementHierarchy, 'X', [\Laminas\I18n\Validator\IsFloat::NOT_FLOAT]);
    }

    /**
     * Assert than a form element is a checkbox input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementCheckbox($elementHierarchy, $uncheckedValue = 'N', $checkedValue = 'Y')
    {
        $this->assertFormElementValid($elementHierarchy, $checkedValue);
        $this->assertFormElementValid($elementHierarchy, $uncheckedValue);
        $this->assertFormElementNotValid($elementHierarchy, 'X', [Validator\InArray::NOT_IN_ARRAY]);
    }

    /**
     * Assert than a form element is a hidden input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementHidden($elementHierarchy)
    {
        $this->assertFormElementRequired($elementHierarchy, false);
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementValid($elementHierarchy, 'X');
    }

    /**
     * Assert than a form element is a html input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementHtml($elementHierarchy)
    {
        $this->assertFormElementRequired($elementHierarchy, false);
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementValid($elementHierarchy, 'X');
    }

    /**
     * Assert than a form element is a action button input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementActionButton($elementHierarchy)
    {
        $this->assertFormElementRequired($elementHierarchy, false);
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementValid($elementHierarchy, 'X');
    }

    /**
     * Assert than a form element is a usernameCreate input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementUsernameCreate($elementHierarchy)
    {
        $this->assertFormElementText($elementHierarchy, 4, 40);

        $this->assertFormElementValid($elementHierarchy, 'usr0001');
        $this->assertFormElementValid($elementHierarchy, 'USR0001'); // Should be transformed to lowercase
        $this->assertFormElementValid($elementHierarchy, 'abcdefghijklmnoprstuvwxyz');
        $this->assertFormElementValid($elementHierarchy, 'ABCDEFGHIJKLMNOPRSTUVWXYZ'); // Should be transformed to lowercase

        $this->assertFormElementNotValid($elementHierarchy, '0usr0001', TransferValidator\UsernameCreate::USERNAME_INVALID);

        $this->assertFormElementNotValid($elementHierarchy, 'a¬bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a!bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a£bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a&bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a*bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a(bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a)bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a+bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a_bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a.bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a\bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a/bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a=bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a@bs', TransferValidator\UsernameCreate::USERNAME_INVALID);
    }

    /**
     * Assert than a form element is a username legacy input (supporting legacy usernames)
     *
     * @deprecated
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementUsername($elementHierarchy)
    {
        $this->assertFormElementText($elementHierarchy, 2, 40);

        $this->assertFormElementValid($elementHierarchy, '0123456789');
        $this->assertFormElementValid($elementHierarchy, 'abcdefghijklmnoprstuvwxyz');
        $this->assertFormElementValid($elementHierarchy, 'ABCDEFGHIJKLMNOPRSTUVWXYZ');
        $this->assertFormElementValid($elementHierarchy, '#$%\'+-/=?^_.@`|~",:;<>');

        $this->assertFormElementNotValid($elementHierarchy, 'a¬b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a!b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a£b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a&b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a*b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a(b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a)b', TransferValidator\Username::USERNAME_INVALID);
        $this->assertFormElementNotValid($elementHierarchy, 'a b', TransferValidator\Username::USERNAME_INVALID);
    }

    /**
     * Assert than a form element is an email address
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementEmailAddress($elementHierarchy)
    {
        $this->assertFormElementValid($elementHierarchy, 'valid@email.com');
        $this->assertFormElementValid(
            $elementHierarchy,
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
            '123456789012345678901234567890123456789012345678901234567890.com'
        );
        // total length greater than 254
        $this->assertFormElementNotValid(
            $elementHierarchy,
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890@' .
            '123456789012345678901234567890123456789012345678901234567890.' .
            '123456789012345678901234567890123456789012345678901234567890.' .
            '123456789012345678901234567890123456789012345678901234567890.com',
            TransferValidator\EmailAddress::ERROR_INVALID
        );
        // domain parts max greate than 63 chars
        $this->assertFormElementNotValid(
            $elementHierarchy,
            '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890' .
            '@1234567890123456789012345678901234567890123456789012345678901234.com',
            TransferValidator\EmailAddress::INVALID_FORMAT
        );
        $this->assertFormElementNotValid(
            $elementHierarchy,
            '1234567890123456789012345678901234567890123456789012345678901',
            TransferValidator\EmailAddress::INVALID_FORMAT
        );
    }

    /**
     * Assert than a form element is a postcode
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementPostcode($elementHierarchy)
    {
        $this->assertFormElementValid($elementHierarchy, 'LS9 6NF');
        $this->assertFormElementValid($elementHierarchy, 'ls9 6nf');
        $this->assertFormElementValid($elementHierarchy, 'ls96NF');
        $this->assertFormElementNotValid($elementHierarchy, 'not a postcode', Validator\StringLength::TOO_LONG);
    }

    /**
     * Assert than a form element is a phone
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementPhone($elementHierarchy)
    {
        $this->assertFormElementType($elementHierarchy, \Common\Form\Elements\InputFilters\Phone::class);
        $this->assertFormElementValid($elementHierarchy, '0123456789');
        $this->assertFormElementValid($elementHierarchy, '+44123456789');
        $this->assertFormElementValid($elementHierarchy, '(0044)1234567889');
        $this->assertFormElementValid($elementHierarchy, '0123-456789');
        $this->assertFormElementNotValid($elementHierarchy, 'not a phone number', Validator\Regex::NOT_MATCH);
    }

    /**
     * Note for developers
     * We are not really testing here.  There is a custom validation on the
     * frontend (mainly AJAX functionality).  For this purpose there is no real
     * use testing case.  So we skip these searchPostcode elements.
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementPostcodeSearch($elementHierarchy)
    {
        $searchPostcodeElements = [
            'postcode',
            'search',
            'addresses',
            'select',
            'manual-link',
        ];

        foreach ($searchPostcodeElements as $element) {
            $elementToSkip = array_merge(
                $elementHierarchy,
                [
                    $element,
                ]
            );

            self::$testedElements[implode('.', $elementToSkip)] = true;
        }
    }

    /**
     * Assert than a form element is a company number
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementCompanyNumber($elementHierarchy)
    {
        $this->assertFormElementText($elementHierarchy, 1, 8);
        $this->assertFormElementNotValid($elementHierarchy, '#', \Laminas\I18n\Validator\Alnum::NOT_ALNUM);
    }

    /**
     * Assert than a form element is a company number type
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementCompanyNumberType($elementHierarchy)
    {
        $this->assertFormElementHtml(array_merge($elementHierarchy, ['description']));
        $this->assertFormElementCompanyNumber(array_merge($elementHierarchy, ['company_number']));
        $this->assertFormElementActionButton(array_merge($elementHierarchy, ['submit_lookup_company']));
    }

    /**
     * Assert than a form element is a table
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementTable($elementHierarchy)
    {
        $this->assertFormElementType($elementHierarchy, \Common\Form\Elements\Types\Table::class);
    }

    /**
     * Assert than a form element is a NoRender
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementNoRender($elementHierarchy)
    {
        $this->assertFormElementRequired($elementHierarchy, false);
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementType($elementHierarchy, \Common\Form\Elements\InputFilters\NoRender::class);
    }

    /**
     * Assert than a form element is an ActionLink
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementActionLink($elementHierarchy)
    {
        $this->assertFormElementRequired($elementHierarchy, false);
        $this->assertFormElementAllowEmpty($elementHierarchy, true);
        $this->assertFormElementType($elementHierarchy, \Common\Form\Elements\InputFilters\ActionLink::class);
    }

    /**
     * Assert than a form element is a MultipleFileUpload
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementMultipleFileUpload($elementHierarchy)
    {
        $fileCountElement = $fileElement = $messagesElement = $uploadElement = $elementHierarchy;

        $fileCountElement[] = 'fileCount';
        $this->assertFormElementRequired($fileCountElement, false);
        $this->assertFormElementAllowEmpty($fileCountElement, true);

        $fileElement[] = 'file';
        $this->assertFormElementRequired($fileElement, false);
        $this->assertFormElementAllowEmpty($fileElement, true);
        $this->assertFormElementType($fileElement, \Common\Form\Elements\Types\AttachFilesButton::class);

        $messagesElement[] = '__messages__';
        $this->assertFormElementHidden($messagesElement);

        $uploadElement[] = 'upload';
        $this->assertFormElementType($uploadElement, \Common\Form\Elements\InputFilters\ActionButton::class);
        $this->assertFormElementRequired($uploadElement, false);

        // FileUploadCountV2 validator
        $this->assertFormElementValid($elementHierarchy, []);
        $this->assertFormElementValid($elementHierarchy, ['fileCount' => 1, 'list' => [1]]);
        $this->assertFormElementValid($elementHierarchy, ['fileCount' => 4, 'list' => [1, 2, 3, 4]]);
        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['fileCount' => 0],
            'fileCount'
        );
        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['fileCount' => 0, 'list' => []],
            'fileCount'
        );
        $this->assertSame(
            [
                'fileCount' => [
                    CommonValidator\FileUploadCountV2::TOO_FEW => 'Too few files uploaded',
                ]
            ],
            $this->getFormMessages($elementHierarchy),
            sprintf(
                '"%s" form element with value "%s" error messages not as expected',
                implode('.', $elementHierarchy),
                print_r(['fileCount' => 0, 'list' => []], true)
            )
        );
    }

    /**
     * Assert than a form element is a VRM
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementVrm($elementHierarchy)
    {
        $this->assertFormElementValid($elementHierarchy, 'XX59 GTB');
        $this->assertFormElementValid($elementHierarchy, 'FOO1');
        $this->assertFormElementNotValid($elementHierarchy, 'FOO', 'invalid');
        $this->assertFormElementType($elementHierarchy, \Common\Form\Elements\Custom\VehicleVrm::class);
    }

    /**
     * Assert than a form element is a vehicle plated weight
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementVehiclePlatedWeight($elementHierarchy)
    {
        $this->assertFormElementNumber($elementHierarchy, 0, 999999);
        $this->assertFormElementType($elementHierarchy, \Common\Form\Elements\Custom\VehiclePlatedWeight::class);
    }

    /**
     * Assert that a form element is a dynamic multi checkbox
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param bool  $required         Is the form element required
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function assertFormElementDynamicMultiCheckbox($elementHierarchy, $required = true)
    {
        $this->assertFormElementValid($elementHierarchy, 1);
        $this->assertFormElementValid($elementHierarchy, '1');
    }

    /**
     * Assert that a form element is a dynamic radio
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param bool  $required         Is the form element required
     *
     * @return void
     */
    protected function assertFormElementDynamicRadio($elementHierarchy, $required = true)
    {
        $this->assertFormElementValid($elementHierarchy, 1);
        $this->assertFormElementValid($elementHierarchy, '1');
        if ($required) {
            $this->assertFormElementNotValid($elementHierarchy, 'X', Validator\InArray::NOT_IN_ARRAY);
        }
    }

    /**
     * Assert that a form element is a dynamic select
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param bool  $required         Is the form element required
     *
     * @return void
     */
    protected function assertFormElementDynamicSelect(
        $elementHierarchy,
        $required = true
    ) {
        $this->assertFormElementValid($elementHierarchy, 1);
        $this->assertFormElementValid($elementHierarchy, '1');
        if ($required) {
            //uncomment the following line once "prefer_form_input_filter": true has been removed from the forms
            //$this->assertFormElementNotValid($elementHierarchy, 'X', Validator\InArray::NOT_IN_ARRAY);
        }
    }

    /**
     * Assert that a form element is a month select input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementMonthSelect($elementHierarchy)
    {
        $this->assertFormElementValid($elementHierarchy, ['month' => '2', 'year' => '1999']);
        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['month' => 'X', 'year' => '1999'],
            [
                \Laminas\Validator\Regex::NOT_MATCH
            ]
        );
        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['month' => '3', 'year' => 'XXXX'],
            [
                \Laminas\Validator\Regex::NOT_MATCH
            ]
        );
    }

    /**
     * Assert that a form element is a date input
     *
     * @param array $elementHierarchy Form element name eg ['fields','numOfCows']
     *
     * @return void
     */
    protected function assertFormElementDate($elementHierarchy, array $context = [])
    {
        $errorMessages = [
            \Common\Validator\Date::DATE_ERR_CONTAINS_STRING,
            \Laminas\Validator\Date::INVALID_DATE
        ];

        $this->assertFormElementValid($elementHierarchy, ['day' => 1, 'month' => '2', 'year' => 1999], $context);

        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['day' => 'X', 'month' => '2', 'year' => 1999],
            $errorMessages,
            $context
        );

        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['day' => '1', 'month' => 'X', 'year' => 1999],
            $errorMessages,
            $context
        );

        $this->assertFormElementNotValid(
            $elementHierarchy,
            ['day' => 1, 'month' => 3, 'year' => 'XXXX'],
            [
                \Common\Validator\Date::DATE_ERR_CONTAINS_STRING,
                \Common\Validator\Date::DATE_ERR_YEAR_LENGTH,
                Validator\Date::INVALID_DATE
            ],
            $context
        );
    }

    /**
     * Assert that a form element is a date time input.  For any complex
     * logic such as; `endDate` with contexts - use the individual methods.
     *
     * @param array     $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param bool|true $required         Is this input required?  Default is 'true'
     * @param null      $value            Currently the default will be tomorrow's date
     *
     * @return void
     */
    protected function assertFormElementDateTime(array $elementHierarchy, $required = true, $value = null)
    {
        if ($value === null) {
            $currentDate = new \DateTimeImmutable('tomorrow');

            // Date inputted will be exact time tomorrow.
            $value = [
                'year' => $currentDate->format('Y'),
                'month' => $currentDate->format('m'),
                'day' => $currentDate->format('j'),
                'hour' => $currentDate->format('h'),
                'minute' => $currentDate->format('i'),
                'second' => $currentDate->format('s'),
            ];
        }

        $this->assertFormElementRequired($elementHierarchy, $required);
        $this->assertFormElementDateTimeNotValidCheck($elementHierarchy);
        $this->assertFormElementDateTimeValidCheck($elementHierarchy, $value);
    }

    /**
     * To avoid duplication, you can call this method separately and
     * pass custom validation messages
     *
     * @param array $elementHierarchy   Form element name eg ['fields','numOfCows']
     * @param array $validationMessages Specify if validation messages are expected to be different
     *
     * @return void
     */
    protected function assertFormElementDateTimeNotValidCheck(array $elementHierarchy, $validationMessages = [])
    {
        if (empty($validationMessages)) {
            $validationMessages = [
                \Common\Validator\Date::DATE_ERR_CONTAINS_STRING,
                \Common\Validator\Date::DATE_ERR_YEAR_LENGTH,
                Validator\Date::INVALID_DATE,
            ];
        }

        // String in values
        $this->assertFormElementNotValid(
            $elementHierarchy,
            [
                'year' => 'XXXX',
                'month' => 'XX',
                'day' => 'XX',
                'hour' => 'XX',
                'minute' => 'XX',
                'second' => 'XX',
            ],
            $validationMessages
        );

        $validationMessages = [
            Validator\Date::INVALID_DATE
        ];

        // Invalid date
        $this->assertFormElementNotValid(
            $elementHierarchy,
            [
                'year' => 2000,
                'month' => 15,
                'day' => 35,
                'hour' => 27,
                'minute' => 100,
                'second' => 5000,
            ],
            $validationMessages
        );
    }

    /**
     * Developer note;
     * Value is expected to be an array with 'year', 'month', 'day', 'hour', 'minute', 'second'
     *
     * @param array      $elementHierarchy Form element name eg ['fields','numOfCows']
     * @param null|mixed $value            Default date is tomorrows date.  Can be changed if future not allowed
     * @param array      $context          Context is normally used for startDate/endDates
     *
     * @return void
     */
    protected function assertFormElementDateTimeValidCheck(array $elementHierarchy, $value = null, array $context = [])
    {
        if ($value === null) {
            $currentDate = new \DateTimeImmutable('tomorrow');

            // Date inputted will be exact time tomorrow.
            $value = [
                'year' => $currentDate->format('Y'),
                'month' => $currentDate->format('m'),
                'day' => $currentDate->format('j'),
                'hour' => $currentDate->format('h'),
                'minute' => $currentDate->format('i'),
                'second' => $currentDate->format('s'),
            ];
        }

        // Valid scenario
        $this->assertFormElementValid($elementHierarchy, $value, $context);
    }

    /**
     * Assert whether a form element allows empty
     *
     * @param array        $elementHierarchy   Form element name eg ['fields','numOfCows']
     * @param bool         $allowEmpty         if true, form element allows empty
     * @param array        $context            Context
     * @param string|array $validationMessages A single or an array of expected validation messages keys
     *
     * @return void
     */
    protected function assertFormElementAllowEmpty(
        $elementHierarchy,
        $allowEmpty,
        $context = [],
        $validationMessages = null
    ) {
        if ($allowEmpty === true) {
            $this->assertFormElementValid($elementHierarchy, '', $context);
        } else {
            $this->assertFormElementNotValid(
                $elementHierarchy,
                '',
                $validationMessages ? : Validator\NotEmpty::IS_EMPTY,
                $context
            );
        }
    }

    /**
     * Assert whether a form element is required
     *
     * This method checks the value, but 'required' is about checking for the key.
     * New method is: assertFormElementIsRequired.  You will notice some of the field validations
     * will fail after using the new method.  In this scenario, check the requirement of the field
     * and check for any clashes.  AllowEmpty(true) and Required(true) fields make no sense.
     * Resource: http://stackoverflow.com/questions/7242703/zend-framework-how-to-allow-empty-field-for-form-element
     *
     * @param string       $elementHierarchy   Form element name
     * @param bool         $required           true, form element is required
     * @param string|array $validationMessages A single or an array of expected validation messages keys
     *
     * @return void
     * @deprecated
     */
    protected function assertFormElementRequired($elementHierarchy, $required, $validationMessages = null)
    {
        if ($required === true) {
            $this->assertFormElementNotValid(
                $elementHierarchy,
                null,
                $validationMessages ? : Validator\NotEmpty::IS_EMPTY
            );
        } else {
            $this->assertFormElementValid($elementHierarchy, null);
        }
    }

    /**
     * New method used apart from assertFormElementRequired()
     * Avoid using assertFormElementRequired()
     *
     * @param array     $elementHierarchy           Element hierarchy as array (including fieldsets)
     * @param bool|true $required                   true, form element is required
     * @param array     $expectedValidationMessages A single or an array of expected validation messages keys
     *
     * @return void
     */
    protected function assertFormElementIsRequired(
        $elementHierarchy,
        $required = true,
        $expectedValidationMessages = [Validator\NotEmpty::IS_EMPTY]
    ) {
        self::$testedElements[implode('.', $elementHierarchy)] = true;

        // set no data to get the response from the Validation Groups
        $this->setData($elementHierarchy, null);
        $this->setValidationGroup($elementHierarchy);

        $this->sut->isValid();

        $formErrorMessages = $this->sut->getMessages();
        $elementErrorMessages = $this->getElementMessages(
            $elementHierarchy,
            $formErrorMessages
        );

        if ($required === true) {
            $this->assertTrue((!empty($elementErrorMessages)));
            $this->assertEquals(
                array_keys($elementErrorMessages),
                $expectedValidationMessages
            );
        } else {
            $this->assertFalse($elementErrorMessages);
        }
    }

    /**
     * Test if service name as expected
     *
     * @param $elementHierarchy
     * @param $serviceName
     *
     * @throws \Exception
     */
    public function assertServiceEquals($elementHierarchy, $serviceName)
    {

        $element =  $this->getFormElement($elementHierarchy);
        $this->assertContains('service_name', array_keys($element->getOptions()), "service name option not set");
        $this->assertEquals($element->getOption('service_name'), $serviceName, "service_name option does not match class name provided");
    }

    /**
     * Get messages for specified element
     *
     * @param array $elementHierarchy  Element and/or Fieldset hierarchy
     * @param array $formErrorMessages Error messages from Form service
     *
     * @return array|false
     */
    private function getElementMessages($elementHierarchy, $formErrorMessages)
    {
        $elementOrFieldsetName = (is_array($elementHierarchy)) ?
            current($elementHierarchy) : next($elementHierarchy);

        if (
            isset($formErrorMessages[$elementOrFieldsetName]) &&
            is_array($formErrorMessages[$elementOrFieldsetName])
        ) {
            // are we at the end?
            if (next($elementHierarchy) === false) {
                return $formErrorMessages[$elementOrFieldsetName];
            }

            return $this->getElementMessages(
                $elementHierarchy,
                $formErrorMessages[$elementOrFieldsetName]
            );
        }

        return false;
    }

    /**
     * Check that tests exists for all form elements
     * This needs to be the last test that runs
     *
     * @param string $elementName Element name to test
     *
     * @dataProvider dataProviderAllElementNames
     * @doesNotPerformAssertions
     *
     * @return void
     */
    public function testMissingTest($elementName)
    {
        if (!array_key_exists($elementName, self::$testedElements)) {
            $this->markTestIncomplete(sprintf('"%s" form element not tested', $elementName));
        }
    }

    /**
     * Data provider, a full list of element names on this form
     *
     * @return array
     */
    public function dataProviderAllElementNames()
    {
        $elementList = $this->getElementList($this->getForm());
        foreach ($elementList as &$elementName) {
            $elementName = [$elementName];
        }
        return $elementList;
    }

    /**
     * Get a list of all form elements
     *
     * @param \Laminas\Form\Fieldset $fieldsset Fieldset
     * @param string              $prefix    Prefix
     *
     * @return array eg ['fields.numOfCows', 'fields.numOfDogs']
     */
    private function getElementList(\Laminas\Form\Fieldset $fieldsset, $prefix = '')
    {
        $elementList = [];
        /** @var \Laminas\Form\Element $element */
        foreach ($fieldsset->getFieldsets() as $childFieldSet) {
            $elementList = array_merge(
                $elementList,
                $this->getElementList(
                    $childFieldSet,
                    $prefix . $childFieldSet->getName() . '.'
                )
            );
        }
        foreach ($fieldsset->getElements() as $element) {
            $elementList[] = $prefix . $element->getName();
        }
        return $elementList;
    }

    protected function getElementByHierarchy($elementHierarchy): ElementInterface
    {
        $elementOrFieldSet = $this->sut;
        foreach ($elementHierarchy as $name) {
            $elementOrFieldSet = $elementOrFieldSet->get($name);
        }
        return $elementOrFieldSet;
    }
}
