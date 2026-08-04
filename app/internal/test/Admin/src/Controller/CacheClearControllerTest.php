<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\CacheClearController;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Cache\Clear;
use Laminas\Http\Request;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Form\Form;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;

final class CacheClearControllerTest extends MockeryTestCase
{
    /**
     * @var CacheClearController&m\MockInterface
     */
    private $sut;

    /**
     * @var FlashMessengerHelperService&m\MockInterface
     */
    private $flashMessenger;

    /**
     * @var Request&m\MockInterface
     */
    private $request;

    /**
     * @var FormHelperService&m\MockInterface
     */
    private $formHelper;

    /**
     * @var Form&m\MockInterface
     */
    private $form;

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

        $placeholder = m::mock();

        $placeholder
            ->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'Clear cache')
            ->once();

        $placeholder
            ->shouldReceive('setPlaceholder')
            ->with('contentTitle', 'Clear cache')
            ->once();

        $this->sut
            ->shouldReceive('placeholder')
            ->andReturn($placeholder);

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);

        $formActions = m::mock(FieldsetInterface::class);
        $submit = m::mock(ElementInterface::class);

        $this->formHelper
            ->shouldReceive('createFormWithRequest')
            ->with('CacheClear', $this->request)
            ->andReturn($this->form);

        $this->form
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($formActions);

        $formActions
            ->shouldReceive('get')
            ->with('submit')
            ->andReturn($submit);

        $submit
            ->shouldReceive('setLabel')
            ->with('Clear cache')
            ->andReturnSelf();

        $submit
            ->shouldReceive('setAttribute')
            ->with('aria-label', 'Clear cache')
            ->andReturnSelf();

        $formActions
            ->shouldReceive('remove')
            ->with('cancel');

        $formActions
            ->shouldReceive('remove')
            ->with('addAnother');
    }

    public function testIndexActionShowsErrorWhenCacheClearFails(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnTrue();

        $postData = [
            'cacheTypes' => ['cqrs'],
            'form-actions' => [
                'submit' => '',
            ],
            'security' => 'test-token',
        ];

        $this->request
            ->shouldReceive('getPost')
            ->once()
            ->andReturn($postData);

        $this->form
            ->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();

        $this->form
            ->shouldReceive('getData')
            ->once()
            ->andReturn([
                'cacheTypes' => ['cqrs'],
            ]);

        $response = m::mock();

        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->withArgs(
                function (Clear $command): bool {
                    self::assertSame('cqrs', $command->getNamespace());

                    return true;
                }
            )
            ->andReturn($response);

        $response
            ->shouldReceive('isOk')
            ->once()
            ->andReturnFalse();

        $response
            ->shouldReceive('isClientError')
            ->once()
            ->andReturnFalse();

        $response
            ->shouldReceive('isServerError')
            ->once()
            ->andReturnTrue();

        $this->flashMessenger
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('Cache could not be cleared');

        $this->flashMessenger
            ->shouldNotReceive('addSuccessMessage');

        $redirect = m::mock(Redirect::class);

        $this->sut
            ->shouldReceive('redirect')
            ->once()
            ->andReturn($redirect);

        $redirect
            ->shouldReceive('toRoute')
            ->once()
            ->with('admin-dashboard/admin-cache-clear');

        $this->sut->indexAction();
    }

    public function testIndexActionForGetRequestReturnsFormView(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnFalse();

        $this->form->shouldNotReceive('isValid');
        $this->sut->shouldNotReceive('handleCommand');

        $result = $this->sut->indexAction();

        self::assertInstanceOf(ViewModel::class, $result);
        self::assertSame('pages/form', $result->getTemplate());
        self::assertSame($this->form, $result->getVariable('form'));
    }

    public function testIndexActionDoesNotClearCacheWhenFormIsInvalid(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnTrue();

        $postData = [
            'cacheTypes' => [],
            'form-actions' => [
                'submit' => '',
            ],
            'security' => 'test-token',
        ];

        $this->request
            ->shouldReceive('getPost')
            ->once()
            ->andReturn($postData);

        $this->form
            ->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturnFalse();

        $this->form->shouldNotReceive('getData');
        $this->sut->shouldNotReceive('handleCommand');
        $this->sut->shouldNotReceive('redirect');

        $result = $this->sut->indexAction();

        self::assertInstanceOf(ViewModel::class, $result);
        self::assertSame('pages/form', $result->getTemplate());
    }

    public function testIndexActionClearsSelectedCachesSuccessfully(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnTrue();

        $postData = [
            'cacheTypes' => [
                'translations',
                'system_parameters',
                'cqrs',
            ],
            'form-actions' => [
                'submit' => '',
            ],
            'security' => 'test-token',
        ];

        $this->request
            ->shouldReceive('getPost')
            ->once()
            ->andReturn($postData);

        $this->form
            ->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $this->form
            ->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();

        $this->form
            ->shouldReceive('getData')
            ->once()
            ->andReturn([
                'cacheTypes' => [
                    'translations',
                    'system_parameters',
                    'cqrs',
                ],
            ]);

        $response = m::mock();

        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->withArgs(
                function (Clear $command): bool {
                    self::assertSame(
                        'translation_key,translation_replacement,sys_param,sys_param_list,cqrs',
                        $command->getNamespace()
                    );
                    self::assertFalse($command->getDryRun());

                    return true;
                }
            )
            ->andReturn($response);

        $response
            ->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();

        $response->shouldNotReceive('isClientError');
        $response->shouldNotReceive('isServerError');

        $this->flashMessenger
            ->shouldReceive('addSuccessMessage')
            ->once()
            ->with('Cache cleared successfully');

        $this->flashMessenger
            ->shouldNotReceive('addErrorMessage');

        $redirect = m::mock(Redirect::class);

        $this->sut
            ->shouldReceive('redirect')
            ->once()
            ->andReturn($redirect);

        $redirect
            ->shouldReceive('toRoute')
            ->once()
            ->with('admin-dashboard/admin-cache-clear');

        $this->sut->indexAction();
    }
}
