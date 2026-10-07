<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\CacheClearController;
use Common\Controller\Plugin\Redirect;
use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Cache\Clear;
use Admin\Form\Model\Form\CacheClear as CacheClearForm;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Http\Request;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CacheClearControllerTest extends MockeryTestCase
{
    private const string ROUTE = 'admin-dashboard/admin-cache-clear';

    private CacheClearController&m\MockInterface $sut;

    private FlashMessengerHelperService&m\MockInterface $flashMessenger;

    private Request&m\MockInterface $request;

    private FormHelperService&m\MockInterface $formHelper;

    private Form&m\MockInterface $form;

    public function setUp(): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);
        $this->formHelper = m::mock(FormHelperService::class);
        $this->form = m::mock(Form::class);
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);

        $this->request = m::mock(Request::class);

        $this->sut = m::mock(
            CacheClearController::class,
            [
                $translationHelper,
                $this->formHelper,
                $this->flashMessenger,
                $navigation,
            ]
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);
    }

    /**
     * Expectations for the page scaffolding indexAction always performs: titles, the form from
     * the helper service, and the CRUD buttons it trims down to a single Clear cache action.
     *
     * Kept out of setUp so the tests that never render the page are not asserting against it.
     */
    private function expectPageScaffolding(): void
    {
        $placeholder = m::mock();

        $placeholder
            ->expects('setPlaceholder')
            ->with('pageTitle', 'Clear cache');

        $placeholder
            ->expects('setPlaceholder')
            ->with('contentTitle', 'Clear cache');

        $this->sut
            ->shouldReceive('placeholder')
            ->andReturn($placeholder);

        $formActions = m::mock(FieldsetInterface::class);
        $submit = m::mock(ElementInterface::class);

        $this->formHelper
            ->expects('createFormWithRequest')
            ->with('CacheClear', $this->request)
            ->andReturn($this->form);

        $this->form
            ->expects('get')
            ->with('form-actions')
            ->andReturn($formActions);

        $formActions
            ->expects('get')
            ->with('submit')
            ->andReturn($submit);

        $submit
            ->expects('setLabel')
            ->with('Clear cache')
            ->andReturnSelf();

        $submit
            ->expects('setAttribute')
            ->with('aria-label', 'Clear cache')
            ->andReturnSelf();

        $formActions->expects('remove')->with('cancel');
        $formActions->expects('remove')->with('addAnother');
    }

    /**
     * Drive a valid POST through the form and return the namespace string the controller sent.
     *
     * @param string[] $cacheTypes
     */
    private function submitAndCaptureNamespace(array $cacheTypes, array $result = []): string
    {
        $this->expectPageScaffolding();

        $postData = [
            'cacheTypes' => $cacheTypes,
            'form-actions' => ['submit' => ''],
            'security' => 'test-token',
        ];

        $this->request->expects('isPost')->andReturnTrue();
        $this->request->expects('getPost')->andReturn($postData);

        $this->form->expects('setData')->with($postData)->andReturnSelf();
        $this->form->expects('isValid')->andReturnTrue();
        $this->form->expects('getData')->andReturn(['cacheTypes' => $cacheTypes]);

        $captured = '';

        $response = m::mock(Response::class);
        $response->expects('isOk')->andReturnTrue();
        $response->expects('getResult')->andReturn($result);

        $this->sut
            ->expects('handleCommand')
            ->withArgs(
                function (Clear $command) use (&$captured): bool {
                    $captured = (string) $command->getNamespace();
                    self::assertFalse($command->getDryRun());

                    return true;
                }
            )
            ->andReturn($response);

        $this->flashMessenger->shouldReceive('addSuccessMessage');
        $this->flashMessenger->shouldNotReceive('addErrorMessage');

        $redirect = m::mock(Redirect::class);
        $this->sut->expects('redirect')->andReturn($redirect);
        $redirect->expects('toRoute')->with(self::ROUTE);

        $this->sut->indexAction();

        return $captured;
    }

    public function testIndexActionForGetRequestReturnsFormView(): void
    {
        $this->expectPageScaffolding();

        $this->request->expects('isPost')->andReturnFalse();

        $this->form->shouldNotReceive('isValid');
        $this->sut->shouldNotReceive('handleCommand');

        $result = $this->sut->indexAction();

        self::assertInstanceOf(ViewModel::class, $result);
        self::assertSame('pages/form', $result->getTemplate());
        self::assertSame($this->form, $result->getVariable('form'));
    }

    public function testIndexActionDoesNotClearCacheWhenFormIsInvalid(): void
    {
        $this->expectPageScaffolding();

        $postData = [
            'cacheTypes' => [],
            'form-actions' => ['submit' => ''],
            'security' => 'test-token',
        ];

        $this->request->expects('isPost')->andReturnTrue();
        $this->request->expects('getPost')->andReturn($postData);

        $this->form->expects('setData')->with($postData)->andReturnSelf();
        $this->form->expects('isValid')->andReturnFalse();

        $this->form->shouldNotReceive('getData');
        $this->sut->shouldNotReceive('handleCommand');
        $this->sut->shouldNotReceive('redirect');

        $result = $this->sut->indexAction();

        self::assertInstanceOf(ViewModel::class, $result);
        self::assertSame('pages/form', $result->getTemplate());
        self::assertSame($this->form, $result->getVariable('form'));
    }

    #[DataProvider('cacheTypeProvider')]
    public function testSelectedCacheTypesExpandToNamespaces(array $cacheTypes, string $expected): void
    {
        self::assertSame($expected, $this->submitAndCaptureNamespace($cacheTypes));
    }

    public static function cacheTypeProvider(): array
    {
        return [
            'translations' => [['translations'], 'translation_key,translation_replacement'],
            'system parameters' => [['system_parameters'], 'sys_param,sys_param_list'],
            'cqrs' => [['cqrs'], 'cqrs'],
            'doctrine' => [['doctrine'], 'doctrine'],
            'jwks' => [['jwks'], 'jwks'],
            'everything' => [
                ['translations', 'system_parameters', 'cqrs', 'doctrine', 'jwks'],
                'translation_key,translation_replacement,sys_param,sys_param_list,cqrs,doctrine,jwks',
            ],
        ];
    }

    /**
     * A checkbox value with no mapping is dropped rather than forwarded to the API, which would
     * reject the whole request.
     */
    public function testUnmappedCacheTypeIsIgnored(): void
    {
        self::assertSame('cqrs', $this->submitAndCaptureNamespace(['cqrs', 'not_a_cache_type']));
    }

    /**
     * A selection that maps to nothing must not reach the API: an empty namespace comes back as
     * a 200 that cleared nothing, which the page would report as success.
     */
    public function testSelectionWithNoMappedNamespaceIsReportedAsAFailure(): void
    {
        $this->expectPageScaffolding();

        $postData = [
            'cacheTypes' => ['not_a_cache_type'],
            'form-actions' => ['submit' => ''],
            'security' => 'test-token',
        ];

        $this->request->expects('isPost')->andReturnTrue();
        $this->request->expects('getPost')->andReturn($postData);

        $this->form->expects('setData')->with($postData)->andReturnSelf();
        $this->form->expects('isValid')->andReturnTrue();
        $this->form->expects('getData')->andReturn(['cacheTypes' => ['not_a_cache_type']]);

        $this->sut->shouldNotReceive('handleCommand');

        $this->flashMessenger->expects('addErrorMessage')->with('Cache could not be cleared');
        $this->flashMessenger->shouldNotReceive('addSuccessMessage');

        $redirect = m::mock(Redirect::class);
        $this->sut->expects('redirect')->andReturn($redirect);
        $redirect->expects('toRoute')->with(self::ROUTE);

        $this->sut->indexAction();
    }

    /**
     * The checkboxes on the form and the controller's map are declared separately. A checkbox
     * without a mapping would be dropped, and a mapping without a checkbox is unreachable, so
     * the two lists have to be identical.
     */
    public function testFormOptionsMatchTheNamespaceMap(): void
    {
        $spec = (new AnnotationBuilder())->getFormSpecification(CacheClearForm::class);

        $valueOptions = null;

        foreach ($spec['elements'] as $element) {
            if (($element['spec']['name'] ?? null) === 'cacheTypes') {
                $valueOptions = $element['spec']['options']['value_options'];
            }
        }

        self::assertIsArray($valueOptions, 'the CacheClear form has no cacheTypes element');
        self::assertSame(
            array_keys(CacheClearController::CACHE_NAMESPACE_MAP),
            array_keys($valueOptions)
        );
    }

    /**
     * Every namespace this page can send has to be one the API command accepts - otherwise the
     * clear fails with a 400 that only shows up at runtime.
     */
    public function testEveryMappedNamespaceIsAcceptedByTheCommand(): void
    {
        foreach (CacheClearController::CACHE_NAMESPACE_MAP as $cacheType => $namespaces) {
            foreach ($namespaces as $namespace) {
                self::assertContains(
                    $namespace,
                    Clear::NAMESPACES,
                    sprintf('"%s" maps to unknown namespace "%s"', $cacheType, $namespace)
                );
            }
        }
    }

    /**
     * The key count is the only thing that distinguishes a clear that worked from one that
     * matched nothing, so it belongs in the message the admin actually reads.
     */
    #[DataProvider('outcomeMessageProvider')]
    public function testSuccessMessageReportsWhatWasRemoved(array $result, string $expectedMessage): void
    {
        $this->expectPageScaffolding();

        $postData = [
            'cacheTypes' => ['cqrs'],
            'form-actions' => ['submit' => ''],
            'security' => 'test-token',
        ];

        $this->request->expects('isPost')->andReturnTrue();
        $this->request->expects('getPost')->andReturn($postData);

        $this->form->expects('setData')->with($postData)->andReturnSelf();
        $this->form->expects('isValid')->andReturnTrue();
        $this->form->expects('getData')->andReturn(['cacheTypes' => ['cqrs']]);

        $response = m::mock(Response::class);
        $response->expects('isOk')->andReturnTrue();
        $response->expects('getResult')->andReturn($result);

        $this->sut->expects('handleCommand')->andReturn($response);

        $this->flashMessenger->expects('addSuccessMessage')->with($expectedMessage);
        $this->flashMessenger->shouldNotReceive('addErrorMessage');

        $redirect = m::mock(Redirect::class);
        $this->sut->expects('redirect')->andReturn($redirect);
        $redirect->expects('toRoute')->with(self::ROUTE);

        $this->sut->indexAction();
    }

    public static function outcomeMessageProvider(): array
    {
        $flag = Clear::RESULT_FLAG_KEYS_DELETED;

        return [
            'nothing matched' => [['flags' => [$flag => 0]], 'Cache cleared - 0 entries removed'],
            'one entry' => [['flags' => [$flag => 1]], 'Cache cleared - 1 entry removed'],
            'many entries' => [['flags' => [$flag => 412]], 'Cache cleared - 412 entries removed'],
            'no count reported' => [['messages' => []], 'Cache cleared'],
        ];
    }

    public function testIndexActionShowsErrorWhenCacheClearFails(): void
    {
        $this->expectPageScaffolding();

        $postData = [
            'cacheTypes' => ['cqrs'],
            'form-actions' => ['submit' => ''],
            'security' => 'test-token',
        ];

        $this->request->expects('isPost')->andReturnTrue();
        $this->request->expects('getPost')->andReturn($postData);

        $this->form->expects('setData')->with($postData)->andReturnSelf();
        $this->form->expects('isValid')->andReturnTrue();
        $this->form->expects('getData')->andReturn(['cacheTypes' => ['cqrs']]);

        $response = m::mock(Response::class);
        $response->expects('isOk')->andReturnFalse();
        $response->shouldNotReceive('getResult');

        $this->sut->expects('handleCommand')->andReturn($response);

        $this->flashMessenger->expects('addErrorMessage')->with('Cache could not be cleared');
        $this->flashMessenger->shouldNotReceive('addSuccessMessage');

        $redirect = m::mock(Redirect::class);
        $this->sut->expects('redirect')->andReturn($redirect);
        $redirect->expects('toRoute')->with(self::ROUTE);

        $this->sut->indexAction();
    }
}
