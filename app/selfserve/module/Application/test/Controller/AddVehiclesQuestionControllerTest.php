<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Test\MockeryTestCase;
use Common\Test\MocksServicesTrait;
use Common\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use Laminas\Http\Request;
use Laminas\Router\Http\RouteMatch;
use Mockery\MockInterface;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Cqrs\Response;
use Laminas\Http\Response as HttpResponse;
use Hamcrest\Core\IsInstanceOf;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Laminas\Stdlib\Parameters;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Dvsa\Olcs\Application\Form\AddVehiclesQuestionForm;
use Laminas\Form\Form;
use Laminas\Form\Exception\DomainException;
use Common\Form\FormValidator;
use Common\Test\Form\FormValidatorBuilder;
use Common\Exception\ResourceNotFoundException;
use Common\Controller\Plugin\HandleCommand;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicles;
use Common\Service\Cqrs\Exception\BadCommandResponseException;
use Common\Exception\BailOutException;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Cqrs\Exception\BadQueryResponseException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;

/**
 * @see AddVehiclesQuestionController
 */
class AddVehiclesQuestionControllerTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected const APPLICATION_ID = 1;
    protected const VEHICLE_SECTION_ID = 'vehicles';
    protected const VEHICLE_SECTION_DATA = [];
    protected const APPLICATION_ID_ROUTE_PARAMETER_NAME = 'application';
    protected const URL_TO_APPLICATION_OVERVIEW_PAGE = 'URL TO APPLICATION OVERVIEW PAGE';
    protected const APPLICATION_OVERVIEW_ROUTE_CONFIG = ['lva-application', ['application' => self::APPLICATION_ID]];
    protected const BACK_URL_VARIABLE = 'backUrl';
    protected const FORM_VARIABLE = 'form';
    protected const ADD_VEHICLES_VIEW_TEMPLATE = 'application/pages/ask-vehicles-question';
    protected const APPLICATION_VEHICLES_ROUTE = 'lva-application/vehicles';
    protected const PARAMETERS_WITH_APPLICATION_ID = ['application' => self::APPLICATION_ID];
    protected const FLASH_MESSAGE_INPUT_NAMESPACE = 'add-vehicles-question-controller-input';
    protected const INVALID_RADIO_OPTION = 'INVALID RADIO OPTION';
    protected const RADIO_OPTION_YES = 1;
    protected const RADIO_OPTION_YES_STRING = '1';
    protected const RADIO_OPTION_NO = 0;
    protected const INVALID_CSRF = 'INVALID CSRF';
    protected const VALID_CSRF = 'VALID CSRF';
    protected const INPUT_WITH_AN_INVALID_RADIO_OPTION = [self::RADIO_INPUT_KEY => self::INVALID_RADIO_OPTION];
    protected const INPUT_WITH_AN_INVALID_RADIO_OPTION_AND_NULL_FOR_REMAINING_INPUTS = [
        self::RADIO_INPUT_KEY => self::INVALID_RADIO_OPTION,
        self::SUBMIT_INPUT_KEY => null,
        self::SECURITY_INPUT_KEY => null,
        self::APPLICATION_VERSION_INPUT_KEY => null,
    ];
    protected const INPUT_WITH_THE_RADIO_OPTION_SET_TO_YES_STRING = [self::RADIO_INPUT_KEY => self::RADIO_OPTION_YES_STRING];
    protected const INPUT_WITH_THE_RADIO_OPTION_SET_TO_YES_INT = [self::RADIO_INPUT_KEY => self::RADIO_OPTION_YES];
    protected const INPUT_WITH_THE_RADIO_OPTION_SET_TO_YES_INT_AND_NULL_FOR_REMAINING_INPUTS = [
        self::RADIO_INPUT_KEY => self::RADIO_OPTION_YES,
        self::SUBMIT_INPUT_KEY => null,
        self::SECURITY_INPUT_KEY => null,
        self::APPLICATION_VERSION_INPUT_KEY => null,
    ];
    protected const SECURITY_INPUT_KEY = 'security';
    protected const EMPTY_FORM_DATA = [
        self::RADIO_INPUT_KEY => null,
        self::SUBMIT_INPUT_KEY => null,
        self::SECURITY_INPUT_KEY => null,
        self::APPLICATION_VERSION_INPUT_KEY => null,
    ];
    protected const FORM_DATA_FROM_INPUT_WITH_AN_INVALID_RADIO_OPTION = [
        self::RADIO_INPUT_KEY => self::INVALID_RADIO_OPTION,
        self::SUBMIT_INPUT_KEY => null,
        self::SECURITY_INPUT_KEY => null,
        self::APPLICATION_VERSION_INPUT_KEY => self::APPLICATION_VERSION,
    ];
    protected const MESSAGES_WITH_JSON_FROM_INPUT_WITH_AN_INVALID_RADIO_OPTION = ['{"radio": "' . self::INVALID_RADIO_OPTION . '"}'];
    protected const INVALID_SUBMIT_VALUE = "INVALID SUBMIT VALUE";
    protected const RADIO_INPUT_KEY = 'radio';
    protected const SUBMIT_INPUT_KEY = 'submit';
    protected const OVERVIEW_SUBMIT_VALUE = 'overview';
    protected const NEXT_SUBMIT_VALUE = 'next';
    protected const GO_TO_OVERVIEW_AND_SUBMIT_VEHICLES_INPUT_SET = [
        self::SUBMIT_INPUT_KEY => self::OVERVIEW_SUBMIT_VALUE,
        self::RADIO_INPUT_KEY => self::RADIO_OPTION_YES,
        self::APPLICATION_VERSION_INPUT_KEY => self::APPLICATION_VERSION,
    ];
    protected const UNIMPLEMENTED_PATH_EXCEPTION_MESSAGE = 'This path still needs to be implemented';
    protected const GO_TO_NEXT_AND_SUBMIT_VEHICLE_DETAILS_INPUT_SET = [
        self::SUBMIT_INPUT_KEY => self::NEXT_SUBMIT_VALUE,
        self::RADIO_INPUT_KEY => self::RADIO_OPTION_YES,
        self::APPLICATION_VERSION_INPUT_KEY => self::APPLICATION_VERSION,
    ];
    protected const GO_TO_NEXT_WITHOUT_SUBMITTING_VEHICLE_DETAILS_INPUT_SET = [
        self::SUBMIT_INPUT_KEY => self::NEXT_SUBMIT_VALUE,
        self::RADIO_INPUT_KEY => self::RADIO_OPTION_NO,
        self::APPLICATION_VERSION_INPUT_KEY => self::APPLICATION_VERSION,
    ];
    protected const APPLICATION_SAFETY_AND_COMPLIANCE_ROUTE_CONFIG = ['lva-application/safety', ['application' => self::APPLICATION_ID]];
    protected const GO_TO_OVERVIEW_WITHOUT_SUBMITTING_VEHICLES_INPUT_SET = [
        self::SUBMIT_INPUT_KEY => self::OVERVIEW_SUBMIT_VALUE,
        self::RADIO_INPUT_KEY => self::RADIO_OPTION_NO,
        self::APPLICATION_VERSION_INPUT_KEY => self::APPLICATION_VERSION,
    ];
    protected const HAS_NOT_ENTERED_REGISTRATION = 'N';
    protected const HAS_ENTERED_REGISTRATION = 'Y';
    protected const IS_PARTIAL_UPDATE = true;
    protected const APPLICATION_VERSION_INPUT_KEY = 'application-version';
    protected const APPLICATION_VERSION = 5;
    protected const INVALIDATE_RESPONSE_STATUS_EXCEPTION_MESSAGE = 'Unexpected response status: "%s';
    protected const BAILOUT_EXCEPTION_MESSAGE = 'BAILOUT EXCEPTION MESSAGE';
    protected const BAILOUT_EXCEPTION_RESPONSE = 'BAILOUT EXCEPTION RESPONSE';
    protected const VARIATION_ID = 99;
    protected const EXPECTED_APPLICATION_BUT_GOT_VARIATION_EXCEPTION_MESSAGE = 'Entity with the id provided is a variation; expected an application';
    protected const APPLICATION_IS_VARIATION_KEY = 'isVariation';
    protected const IS_VARIATION = true;
    protected const IS_NOT_VARIATION = false;
    protected const APPLICATION_HAS_ENTERED_REGISTRATION_KEY = 'hasEnteredReg';
    protected const APPLICATION_ID_KEY = 'id';
    protected const APPLICATION_VERSION_KEY = 'version';
    protected const APPLICATION_SECTIONS_KEY = 'sections';
    protected const APPLICATION_DATA = [
        self::APPLICATION_ID_KEY => self::APPLICATION_ID,
        self::APPLICATION_VERSION_KEY => self::APPLICATION_VERSION,
        self::APPLICATION_SECTIONS_KEY => [
            self::VEHICLE_SECTION_ID => self::VEHICLE_SECTION_DATA,
        ],
        self::APPLICATION_HAS_ENTERED_REGISTRATION_KEY => self::HAS_NOT_ENTERED_REGISTRATION,
        self::APPLICATION_IS_VARIATION_KEY => self::IS_NOT_VARIATION,
        self::APPLICATION_STATUS_KEY => self::APPLICATION_STATUS_NOT_SUBMITTED,
        self::APPLICATION_GOODS_OR_PSV_KEY => [
            self::APPLICATION_GOODS_OR_PSV_ID_KEY => RefData::LICENCE_CATEGORY_GOODS_VEHICLE,
        ],
        self::APPLICATION_LICENCE_TYPE_KEY => [
            self::APPLICATION_LICENCE_TYPE_ID_KEY => RefData::LICENCE_TYPE_STANDARD_NATIONAL,
        ],
    ];
    protected const APPLICATION_DATA_STATUS_UNDER_CONSIDERATION = [
        self::APPLICATION_STATUS_KEY => self::APPLICATION_STATUS_UNDER_CONSIDERATION,
    ];
    protected const SUBMISSION_SUMMARY_ROUTE = ['lva-application/submission-summary', ['application' => self::APPLICATION_ID]];
    protected const APPLICATION_STATUS_KEY = 'status';
    protected const APPLICATION_STATUS_ID_KEY = 'id';
    protected const APPLICATION_STATUS_NOT_SUBMITTED = [
        self::APPLICATION_STATUS_ID_KEY => RefData::APPLICATION_STATUS_NOT_SUBMITTED
    ];
    protected const APPLICATION_STATUS_UNDER_CONSIDERATION = [
        self::APPLICATION_STATUS_ID_KEY => RefData::APPLICATION_STATUS_UNDER_CONSIDERATION
    ];
    protected const EXPECTED_APPLICATION_WITH_CATEGORY_GOODS_EXCEPTION_MESSAGE = 'Request not supported for the licence category of this application';
    protected const APPLICATION_GOODS_OR_PSV_KEY = 'goodsOrPsv';
    protected const APPLICATION_GOODS_OR_PSV_ID_KEY = 'id';
    protected const APPLICATION_DATA_CATEGORY_PSV = [
        self::APPLICATION_GOODS_OR_PSV_KEY => [
            self::APPLICATION_GOODS_OR_PSV_ID_KEY => RefData::LICENCE_CATEGORY_PSV,
        ]
    ];
    protected const EXPECTED_APPLICATION_WITH_SUPPORTED_LICENCE_TYPE_EXCEPTION_MESSAGE = 'Request not supported for the licence type of this application';
    protected const APPLICATION_LICENCE_TYPE_KEY = 'licenceType';
    protected const APPLICATION_LICENCE_TYPE_ID_KEY = 'id';
    protected const APPLICATION_DATA_LICENCE_TYPE_SPECIAL_RESTRICTED = [
        self::APPLICATION_LICENCE_TYPE_KEY => [
            self::APPLICATION_LICENCE_TYPE_ID_KEY => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
        ],
    ];
    protected const APPLICATION_APPLICATION_COMPLETION_KEY = 'applicationCompletion';
    protected const APPLICATION_APPLICATION_COMPLETION_VEHICLE_STATUS_KEY = 'vehiclesStatus';
    protected const APPLICATION_DATA_COMPLETED_WITHOUT_VEHICLES = [
        self::APPLICATION_HAS_ENTERED_REGISTRATION_KEY => self::HAS_NOT_ENTERED_REGISTRATION,
        self::APPLICATION_APPLICATION_COMPLETION_KEY => [
            self::APPLICATION_APPLICATION_COMPLETION_VEHICLE_STATUS_KEY => RefData::APPLICATION_COMPLETION_STATUS_COMPLETE
        ],
    ];
    protected const APPLICATION_DATA_WITH_VEHICLES = [
        self::APPLICATION_HAS_ENTERED_REGISTRATION_KEY => self::HAS_ENTERED_REGISTRATION
    ];
    protected const CANNOT_RETURN_DATA_AS_VALIDATION_NOT_OCCURRED_EXCEPTION_MESSAGE = 'Laminas\Form\Form::getData cannot return data as validation has not yet occurred';

    /**
     * @var AddVehiclesQuestionController
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->indexAction(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModel(): ViewModel
    {
        // Setup
        $this->setUpSut();
        $request = new Request();

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);

        return $result;
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModel')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModelWithTheCorrectTemplate(ViewModel $viewModel): void
    {
        // Assert
        $this->assertEquals(static::ADD_VEHICLES_VIEW_TEMPLATE, $viewModel->getTemplate());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModel')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModelWithABackLinkToTheApplicationOverviewPage(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();

        // Expect
        $this->urlHelper()->expects('fromRoute')->withArgs(static::APPLICATION_OVERVIEW_ROUTE_CONFIG)->andReturn(static::URL_TO_APPLICATION_OVERVIEW_PAGE);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->assertEquals(static::URL_TO_APPLICATION_OVERVIEW_PAGE, $result->getVariable(static::BACK_URL_VARIABLE));
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModel')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModelWithForm(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());
        $form = $result->getVariable(static::FORM_VARIABLE);

        // Assert
        $this->assertInstanceOf(AddVehiclesQuestionForm::class, $form);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModelWithForm')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModelWithFormWithoutData(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();

        // Expect
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(self::CANNOT_RETURN_DATA_AS_VALIDATION_NOT_OCCURRED_EXCEPTION_MESSAGE);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());
        $form = $result->getVariable(static::FORM_VARIABLE);
        assert($form instanceof Form);
        $form->getData();
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModelWithForm')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModelWithFormWithDataWhenInputHasBeenFlashed(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();

        // Expect
        $this->flashMessenger()->allows()->hasMessages(static::FLASH_MESSAGE_INPUT_NAMESPACE)->andReturn(true);
        $this->flashMessenger()->expects()->getMessages(static::FLASH_MESSAGE_INPUT_NAMESPACE)->andReturn(static::MESSAGES_WITH_JSON_FROM_INPUT_WITH_AN_INVALID_RADIO_OPTION);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());
        $form = $result->getVariable(static::FORM_VARIABLE);
        assert($form instanceof Form);
        $form->isValid();

        // Assert
        $this->assertEquals(static::FORM_DATA_FROM_INPUT_WITH_AN_INVALID_RADIO_OPTION, $form->getData());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModelWithForm')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingReturnsViewModelWithFormWithNoPreSelectedWhenAUserHasCompletedTheVehicleSectionWithoutVehicles(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();
        $applicationQueryResponse = $this->applicationCqrsResponse(static::APPLICATION_DATA_COMPLETED_WITHOUT_VEHICLES);
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($applicationQueryResponse);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());
        $form = $result->getVariable(static::FORM_VARIABLE);
        assert($form instanceof AddVehiclesQuestionForm);

        // Assert
        $this->assertSame(static::RADIO_OPTION_NO, $form->getRadioInput()->getValue('application-version'));
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingReturnsViewModelWithForm')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndUserHasAlreadyAddedVehiclesThrowResourceNotFoundException(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(self::APPLICATION_DATA_WITH_VEHICLES));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::UNIMPLEMENTED_PATH_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndAUserProvidesTheIdOfAVariationReturnsA404(): void
    {
        // Setup
        $this->setUpSut();
        $request = new Request();
        $applicationData = [static::APPLICATION_IS_VARIATION_KEY => static::IS_VARIATION];
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse($applicationData));
        $routeMatch = new RouteMatch([static::APPLICATION_ID_ROUTE_PARAMETER_NAME => static::VARIATION_ID]);

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::EXPECTED_APPLICATION_BUT_GOT_VARIATION_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndAUserProvidesAnApplicationIdWhichDoesNotExistReturnsA404(): void
    {
        // Setup
        $this->setUpSut();
        $expectedException = new NotFoundException();
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andThrow($expectedException);

        // Expect
        $this->expectExceptionObject($expectedException);

        // Execute
        $this->sut->indexAction(new Request(), $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndAUserProvidesAnApplicationWhichHasAStatusOtherThenNotSubmittedReturnsARedirectToTheSubmissionSummaryPage(): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_STATUS_UNDER_CONSIDERATION));
        $redirect = $this->redirect();
        $this->redirectHelper()->allows()->toRoute(...static::SUBMISSION_SUMMARY_ROUTE)->andReturn($redirect);

        // Execute
        $result = $this->sut->indexAction(new Request(), $this->routeMatch());

        // Assert
        $this->assertSame($redirect, $result);
    }

    /**
     *
     * @throws BadCommandResponseException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCqrsResponseStatusCodesDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingThrowsExceptionIfApplicationResponseHasAStatusOtherThen200(int $invalidStatusCode): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->invalidCqrsResponse($invalidStatusCode));

        // Expect
        $this->expectException(BadQueryResponseException::class);
        $this->expectExceptionMessage(sprintf(static::INVALIDATE_RESPONSE_STATUS_EXCEPTION_MESSAGE, $invalidStatusCode));

        // Execute
        $this->sut->indexAction(new Request(), $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndAUserProvidesAnApplicationWhichDoesNotHaveTheLicenceCategoryGoodsReturnA404(): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_CATEGORY_PSV));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::EXPECTED_APPLICATION_WITH_CATEGORY_GOODS_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction(new Request(), $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndAUserProvidesAnApplicationWhichHasAnUnsupportedLicenceTypeReturnA404(): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_LICENCE_TYPE_SPECIAL_RESTRICTED));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::EXPECTED_APPLICATION_WITH_SUPPORTED_LICENCE_TYPE_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction(new Request(), $this->routeMatch());
    }

    /**
     * @return array
     */
    public static function supportedLicenceTypeDataProvider(): array
    {
        return [
            'standard national' => [RefData::LICENCE_TYPE_STANDARD_NATIONAL],
            'standard international' => [RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            'restricted' => [RefData::LICENCE_TYPE_RESTRICTED],
        ];
    }

    /**
     *
     * @throws BadCommandResponseException
     * @throws BadQueryResponseException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('supportedLicenceTypeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenGettingAndAUserProvidesAnApplicationWithASupportedLicenceTypeDoesNotReturnA404(string $licenceType): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse([
                static::APPLICATION_LICENCE_TYPE_KEY => [
                    static::APPLICATION_LICENCE_TYPE_ID_KEY => $licenceType,
                ]
            ]));

        // Execute
        $result = $this->sut->indexAction(new Request(), $this->routeMatch());

        // Assert
        if ($result instanceof \Laminas\Http\Response) {
            $this->assertNotEquals(\Laminas\Http\Response::STATUS_CODE_404, $result->getStatusCode());
        }
        $this->assertTrue(true);
    }

    /**
     * @return array
     */
    public static function invalidInputSetDataProvider(): array
    {
        return [
            'no radio option' => [[]],
            'invalid radio option' => [static::INPUT_WITH_AN_INVALID_RADIO_OPTION],
            'no csrf' => [[]],
            'invalid csrf' => [['security' => static::INVALID_CSRF]],
            'no submit' => [[]],
            'invalid submit value' => [["submit" => static::INVALID_SUBMIT_VALUE]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndUserHasAlreadyAddedVehiclesThrowResourceNotFoundException(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest();
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_WITH_VEHICLES));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::UNIMPLEMENTED_PATH_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidInputSetDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserHasSuppliedInvalidInputRedirectBack(array $invalidInputSet): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest($invalidInputSet);
        $expectedRedirect = $this->redirect();

        // Expect
        $this->redirectHelper()
            ->expects()
            ->toRoute(static::APPLICATION_VEHICLES_ROUTE, static::PARAMETERS_WITH_APPLICATION_ID)
            ->andReturn($expectedRedirect);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->assertSame($expectedRedirect, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserHasSuppliedInvalidInputFlashTheUsersInputWithCustomNamespace(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(static::INPUT_WITH_AN_INVALID_RADIO_OPTION);

        $this->redirectHelper()->allows('toRoute')->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs(function ($message, $namespace) {
            $this->assertSame(static::FLASH_MESSAGE_INPUT_NAMESPACE, $namespace);
            return true;
        });

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserHasSuppliedInvalidInputFlashTheUsersInputWithJson(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(static::INPUT_WITH_AN_INVALID_RADIO_OPTION);

        $this->redirectHelper()->allows('toRoute')->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs(function ($message) {
            $this->assertJson($message);
            return true;
        });

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenPostingAndAUserHasSuppliedInvalidInputFlashTheUsersInputWithJson')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserHasSuppliedInvalidInputFlashTheUsersInputWithJsonContainingOriginalInput(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(static::INPUT_WITH_AN_INVALID_RADIO_OPTION);
        $this->redirectHelper()->allows('toRoute')->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs(function ($message) {
            $this->assertSame(static::INPUT_WITH_AN_INVALID_RADIO_OPTION_AND_NULL_FOR_REMAINING_INPUTS, json_decode($message, true));
            return true;
        });

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenPostingAndAUserHasSuppliedInvalidInputFlashTheUsersInputWithJson')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserHasSuppliedInvalidInputFlashTheUsersInputWithJsonContainingOriginalInputFilteredByForm(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest(static::INPUT_WITH_THE_RADIO_OPTION_SET_TO_YES_STRING);
        $this->redirectHelper()->allows('toRoute')->andReturn($this->redirect());

        // Expect
        $this->flashMessenger()->expects('addMessage')->withArgs(function ($message) {
            $this->assertSame(static::INPUT_WITH_THE_RADIO_OPTION_SET_TO_YES_INT_AND_NULL_FOR_REMAINING_INPUTS, json_decode($message, true));
            return true;
        });

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserSelectsReturnToOverviewAndYesRedirectsAUserToOverview(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_AND_SUBMIT_VEHICLES_INPUT_SET);
        $expectedRedirect = $this->redirect();

        // Expect
        $this->redirectHelper()
            ->expects()
            ->toRoute(...static::APPLICATION_OVERVIEW_ROUTE_CONFIG)
            ->andReturn($expectedRedirect);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->assertSame($expectedRedirect, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserSelectsReturnToOverviewAndYesUpdateVehicleSectionStatusToIncomplete(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_AND_SUBMIT_VEHICLES_INPUT_SET);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->commandSender()->shouldHaveReceived('send')->withArgs(fn($command) => $this->assertCommandUpdatesVehicleSectionToBeIncomplete($command));
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsNextAndYesReturnsA404(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_NEXT_AND_SUBMIT_VEHICLE_DETAILS_INPUT_SET);

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::UNIMPLEMENTED_PATH_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsNextAndYesUpdateVehicleSectionStatusToIncomplete(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_NEXT_AND_SUBMIT_VEHICLE_DETAILS_INPUT_SET);

        // Execute
        try {
            $this->sut->indexAction($request, $this->routeMatch());
        } catch (ResourceNotFoundException) {
        }

        // Assert
        $this->commandSender()->shouldHaveReceived('send')->withArgs(fn($command) => $this->assertCommandUpdatesVehicleSectionToBeIncomplete($command));
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectNextAndNoRedirectsToTheSafetyAndComplianceStep(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_NEXT_WITHOUT_SUBMITTING_VEHICLE_DETAILS_INPUT_SET);
        $expectedRedirect = $this->redirect();

        // Expect
        $this->redirectHelper()
            ->expects()
            ->toRoute(...static::APPLICATION_SAFETY_AND_COMPLIANCE_ROUTE_CONFIG)
            ->andReturn($expectedRedirect);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->assertSame($expectedRedirect, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsNextAndNoUpdateVehicleSectionStatusToComplete(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_NEXT_WITHOUT_SUBMITTING_VEHICLE_DETAILS_INPUT_SET);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->commandSender()->shouldHaveReceived('send')->withArgs(fn($command) => $this->assertCommandUpdatesVehicleSectionToBeCompleted($command));
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheNoRadioOptionIsSelectedUpdateVehicleSectionStatusToComplete(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_WITHOUT_SUBMITTING_VEHICLES_INPUT_SET);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->commandSender()->shouldHaveReceived('send')->withArgs(fn($command) => $this->assertCommandUpdatesVehicleSectionToBeCompleted($command));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheNoRadioOptionIsSelectedUpdateVehicleSectionStatusToCompleteExposesBailOutExceptionsFromTheCommandHandler(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_WITHOUT_SUBMITTING_VEHICLES_INPUT_SET);
        $this->commandSender()->allows('send')->with(IsInstanceOf::anInstanceOf(UpdateVehicles::class))->andThrow(BailOutException::class);

        // Expect
        $this->expectException(BailOutException::class);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    /**
     * @return array
     */
    public static function invalidCqrsResponseStatusCodesDataProvider(): array
    {
        return [
            '102 status code' => [\Laminas\Http\Response::STATUS_CODE_102],
            '300 status code' => [\Laminas\Http\Response::STATUS_CODE_300],
        ];
    }

    /**
     *
     * @throws BadCommandResponseException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheNoRadioOptionIsSelectedUpdateVehicleSectionStatusToComplete')]
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCqrsResponseStatusCodesDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheUserHasSelectedNotToAddVehicleDetailsThrowsExceptionIfCommandResponseHasAStatusOtherThen200(int $invalidStatusCode): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_WITHOUT_SUBMITTING_VEHICLES_INPUT_SET);
        $this->commandSender()->allows('send')->andReturn($this->invalidCqrsResponse($invalidStatusCode));

        // Expect
        $this->expectException(BadCommandResponseException::class);
        $this->expectExceptionMessage(sprintf(static::INVALIDATE_RESPONSE_STATUS_EXCEPTION_MESSAGE, $invalidStatusCode));

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheNoRadioOptionIsSelectedUpdateVehicleSectionStatusToComplete')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheUserHasSelectedNotToAddVehicleDetailsExposesBailOutExceptionsFromTheCommandHandler(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_WITHOUT_SUBMITTING_VEHICLES_INPUT_SET);
        $expectedException = new BailOutException(static::BAILOUT_EXCEPTION_MESSAGE, static::BAILOUT_EXCEPTION_RESPONSE);
        $this->commandSender()->allows('send')->andThrow($expectedException);

        // Expect
        $this->expectExceptionObject($expectedException);

        // Execute
        $this->sut->indexAction($request, $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingWhenAUserSelectsSaveAndReturnToOverviewAndTheUserHasSelectedNotToAddVehicleDetailsRedirectsAUserToTheApplicationOverview(): void
    {
        // Setup
        $this->enablePopulationOfCsrfDataBeforeFormValidation();
        $this->setUpSut();
        $request = $this->postRequest(static::GO_TO_OVERVIEW_WITHOUT_SUBMITTING_VEHICLES_INPUT_SET);
        $expectedRedirect = $this->redirect();

        // Expect
        $this->redirectHelper()->allows('toRoute')->with(...static::APPLICATION_OVERVIEW_ROUTE_CONFIG)->andReturn($expectedRedirect);

        // Execute
        $result = $this->sut->indexAction($request, $this->routeMatch());

        // Assert
        $this->assertSame($expectedRedirect, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingAndAUserProvidesTheIdOfAVariationReturnsA404')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserProvidesTheIdOfAVariationReturnsA404(): void
    {
        // Setup
        $this->setUpSut();
        $request = $this->postRequest();
        $routeMatch = new RouteMatch([static::APPLICATION_ID_ROUTE_PARAMETER_NAME => static::VARIATION_ID]);
        $applicationData = [static::APPLICATION_IS_VARIATION_KEY => static::IS_VARIATION];
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse($applicationData));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::EXPECTED_APPLICATION_BUT_GOT_VARIATION_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($request, $routeMatch);
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingAndAUserProvidesAnApplicationIdWhichDoesNotExistReturnsA404')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserProvidesAnApplicationIdWhichDoesNotExistReturnsA404(): void
    {
        // Setup
        $this->setUpSut();
        $expectedException = new NotFoundException();
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andThrow($expectedException);

        // Expect
        $this->expectExceptionObject($expectedException);

        // Execute
        $this->sut->indexAction($this->postRequest(), $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingAndAUserProvidesAnApplicationWhichHasAStatusOtherThenNotSubmittedReturnsARedirectToTheSubmissionSummaryPage')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserProvidesAnApplicationWhichHasAStatusOtherThenNotSubmittedReturnsARedirectToTheSubmissionSummaryPage(): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_STATUS_UNDER_CONSIDERATION));
        $redirect = $this->redirect();
        $this->redirectHelper()->allows()->toRoute(...static::SUBMISSION_SUMMARY_ROUTE)->andReturn($redirect);

        // Execute
        $result = $this->sut->indexAction($this->postRequest(), $this->routeMatch());

        // Assert
        $this->assertSame($redirect, $result);
    }

    /**
     *
     * @throws BadCommandResponseException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingThrowsExceptionIfApplicationResponseHasAStatusOtherThen200')]
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCqrsResponseStatusCodesDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingThrowsExceptionIfApplicationResponseHasAStatusOtherThen200(int $invalidStatusCode): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()->allows('__invoke')
            ->with(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->invalidCqrsResponse($invalidStatusCode));

        // Expect
        $this->expectException(BadQueryResponseException::class);
        $this->expectExceptionMessage(sprintf(static::INVALIDATE_RESPONSE_STATUS_EXCEPTION_MESSAGE, $invalidStatusCode));

        // Execute
        $this->sut->indexAction($this->postRequest(), $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingAndAUserProvidesAnApplicationWhichDoesNotHaveTheLicenceCategoryGoodsReturnA404')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserProvidesAnApplicationWhichDoesNotHaveTheLicenceCategoryGoodsReturnA404(): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_CATEGORY_PSV));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::EXPECTED_APPLICATION_WITH_CATEGORY_GOODS_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($this->postRequest(), $this->routeMatch());
    }

    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingAndAUserProvidesAnApplicationWhichHasAnUnsupportedLicenceTypeReturnA404')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserProvidesAnApplicationWhichHasAnUnsupportedLicenceTypeReturnA404(): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse(static::APPLICATION_DATA_LICENCE_TYPE_SPECIAL_RESTRICTED));

        // Expect
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage(static::EXPECTED_APPLICATION_WITH_SUPPORTED_LICENCE_TYPE_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->indexAction($this->postRequest(), $this->routeMatch());
    }

    /**
     *
     * @throws BadCommandResponseException
     * @throws BadQueryResponseException
     * @throws BailOutException
     * @throws ResourceNotFoundException
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Depends('indexActionIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('indexActionWhenGettingAndAUserProvidesAnApplicationWithASupportedLicenceTypeDoesNotReturnA404')]
    #[\PHPUnit\Framework\Attributes\DataProvider('supportedLicenceTypeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function indexActionWhenPostingAndAUserProvidesAnApplicationWithASupportedLicenceTypeDoesNotReturnA404(string $licenceType): void
    {
        // Setup
        $this->setUpSut();
        $this->queryHandler()
            ->allows()
            ->__invoke(IsInstanceOf::anInstanceOf(Application::class))
            ->andReturn($this->applicationCqrsResponse([
                static::APPLICATION_LICENCE_TYPE_KEY => [
                    static::APPLICATION_LICENCE_TYPE_ID_KEY => $licenceType,
                ]
            ]));

        // Execute
        $result = $this->sut->indexAction($this->postRequest(), $this->routeMatch());

        // Assert
        if ($result instanceof \Laminas\Http\Response) {
            $this->assertNotEquals(\Laminas\Http\Response::STATUS_CODE_404, $result->getStatusCode());
        }
        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpServiceManager();
    }

    protected function setUpSut(): void
    {
        $this->sut = new AddVehiclesQuestionController(
            $this->urlHelper(),
            $this->redirectHelper(),
            $this->queryHandler(),
            $this->flashMessenger(),
            $this->formValidator(),
            $this->commandHandler()
        );
    }

    /**
     * @return ServiceManager
     */
    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager|array
    {
        $this->redirectHelper();
        $this->urlHelper();
        $this->queryHandler();
        $this->flashMessenger();
        $this->commandHandler();
        return $serviceManager;
    }

    /**
     * @return HttpResponse
     */
    protected function redirect(): HttpResponse
    {
        $response = new HttpResponse();
        $response->setStatusCode(HttpResponse::STATUS_CODE_302);
        return $response;
    }

    /**
     * @param array|null $data
     * @return Request
     */
    protected function postRequest(array $data = null): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters($data ?? static::EMPTY_FORM_DATA));
        return $request;
    }


    protected function urlHelper(): MockInterface
    {
        if (!$this->serviceManager->has(Url::class)) {
            $instance = $this->setUpMockService(Url::class);
            $this->serviceManager->setService(Url::class, $instance);
        }
        $instance = $this->serviceManager->get(Url::class);
        assert($instance instanceof MockInterface);
        return $instance;
    }

    protected function queryHandler(): MockInterface
    {
        if (!$this->serviceManager->has(HandleQuery::class)) {
            $instance = $this->setUpMockService(HandleQuery::class);
            $instance->allows('__invoke')->andReturnUsing(function () {
                $response = new Response(new HttpResponse());
                $response->setResult([]);
                return $response;
            })->byDefault();

            $instance->allows('__invoke')->with(IsInstanceOf::anInstanceOf(Application::class))->andReturnUsing(fn() => $this->applicationCqrsResponse())->byDefault();

            $this->serviceManager->setService(HandleQuery::class, $instance);
        }
        $instance = $this->serviceManager->get(HandleQuery::class);
        assert($instance instanceof MockInterface);
        return $instance;
    }

    protected function redirectHelper(): MockInterface
    {
        if (!$this->serviceManager->has(Redirect::class)) {
            $instance = $this->setUpMockService(Redirect::class);
            $instance->allows('toRoute')->andReturn($this->redirect())->byDefault();
            $this->serviceManager->setService(Redirect::class, $instance);
        }
        $instance = $this->serviceManager->get(Redirect::class);
        assert($instance instanceof MockInterface);
        return $instance;
    }

    protected function flashMessenger(): MockInterface
    {
        if (!$this->serviceManager->has('FlashMessenger')) {
            $this->serviceManager->setService('FlashMessenger', $this->setUpMockService(FlashMessenger::class));
        }
        $instance = $this->serviceManager->get('FlashMessenger');
        assert($instance instanceof MockInterface);
        return $instance;
    }

    /**
     * @return RouteMatch
     */
    protected function routeMatch(): RouteMatch
    {
        return new RouteMatch([static::APPLICATION_ID_ROUTE_PARAMETER_NAME => static::APPLICATION_ID]);
    }

    /**
     * @return FormValidator
     */
    protected function formValidator(): FormValidator
    {
        if (! $this->serviceManager->has(FormValidator::class)) {
            $instance = FormValidatorBuilder::aValidator()->build();
            $this->serviceManager->setService(FormValidator::class, $instance);
        }
        return $this->serviceManager->get(FormValidator::class);
    }

    protected function enablePopulationOfCsrfDataBeforeFormValidation(): void
    {
        $this->serviceManager->setService(FormValidator::class, FormValidatorBuilder::aValidator()->populateCsrfDataBeforeValidating()->build());
    }

    /**
     * @return HandleCommand
     */
    protected function commandHandler(): HandleCommand
    {
        if (! $this->serviceManager->has(HandleCommand::class)) {
            $flashMessengerHelper = new FlashMessengerHelperService($this->flashMessenger());
            $instance = new HandleCommand($this->commandSender(), $flashMessengerHelper);
            $this->serviceManager->setService(HandleCommand::class, $instance);
        }
        return $this->serviceManager->get(HandleCommand::class);
    }

    protected function commandSender(): MockInterface
    {
        if (! $this->serviceManager->has(CommandSender::class)) {
            $instance = $this->setUpMockService(CommandSender::class);
            $instance->allows('send')->andReturnUsing(fn() => $this->cqrsResponse())->byDefault();
            $this->serviceManager->setService(CommandSender::class, $instance);
        }
        return $this->serviceManager->get(CommandSender::class);
    }

    /**
     * @return bool
     */
    protected function assertCommandUpdatesVehicleSectionToBeCompleted(mixed $command): bool
    {
        $this->assertInstanceOf(UpdateVehicles::class, $command);
        assert($command instanceof UpdateVehicles);
        $this->assertEquals(static::APPLICATION_ID, $command->getId());
        $this->assertEquals(static::HAS_NOT_ENTERED_REGISTRATION, $command->getHasEnteredReg());
        $this->assertEquals(static::IS_PARTIAL_UPDATE, $command->getPartial());
        $this->assertEquals(static::APPLICATION_VERSION, $command->getVersion());
        return true;
    }

    /**
     * @return bool
     */
    protected function assertCommandUpdatesVehicleSectionToBeIncomplete(mixed $command): bool
    {
        $this->assertInstanceOf(UpdateVehicles::class, $command);
        assert($command instanceof UpdateVehicles);
        $this->assertEquals(static::APPLICATION_ID, $command->getId());
        $this->assertEquals(static::HAS_ENTERED_REGISTRATION, $command->getHasEnteredReg());
        $this->assertEquals(static::IS_PARTIAL_UPDATE, $command->getPartial());
        $this->assertEquals(static::APPLICATION_VERSION, $command->getVersion());
        return true;
    }

    /**
     * @param int|null $statusCode
     * @return Response
     */
    protected function invalidCqrsResponse(int $statusCode = null): Response
    {
        $httpResponse = new HttpResponse();
        $httpResponse->setStatusCode($statusCode ?? \Laminas\Http\Response::STATUS_CODE_500);
        $response = new Response($httpResponse);
        $response->setResult([]);
        return $response;
    }

    /**
     * @param array|null $data
     * @return Response
     */
    protected function cqrsResponse(array $data = null): Response
    {
        $response = new Response(new HttpResponse());
        $response->setResult($data ?? []);
        return $response;
    }

    /**
     * @param array|null $data
     * @return Response
     */
    protected function applicationCqrsResponse(array $data = null, bool $merge = true): Response
    {
        if (true === $merge || null === $data) {
            $data = array_merge(static::APPLICATION_DATA, $data ?? []);
        }
        return $this->cqrsResponse($data);
    }
}
