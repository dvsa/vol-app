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

    public function setUp(): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);
        $formHelper = m::mock(FormHelperService::class);
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);
        $navigation = m::mock(Navigation::class);

        $this->request = m::mock(Request::class);

        $this->sut = m::mock(
            CacheClearController::class,
            [
                $translationHelper,
                $formHelper,
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

        $this->sut
            ->shouldReceive('placeholder')
            ->andReturn($placeholder);

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);
    }

    public function testIndexActionForGetRequestReturnsViewModel(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnFalse();

        $this->sut
            ->shouldNotReceive('handleCommand');

        $result = $this->sut->indexAction();

        self::assertInstanceOf(ViewModel::class, $result);
    }

    public function testIndexActionClearsCacheSuccessfully(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnTrue();

        $response = m::mock();

        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->withArgs(
                function (Clear $command): bool {
                    self::assertSame(
                        'translation_key,translation_replacement,sys_param,sys_param_list',
                        $command->getNamespace()
                    );
                    self::assertFalse($command->getDryRun());

                    return true;
                }
            )
            ->andReturn($response);

        $response
            ->shouldReceive('isServerError')
            ->once()
            ->andReturnFalse();

        $response
            ->shouldReceive('isClientError')
            ->once()
            ->andReturnFalse();

        $response
            ->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();

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

    public function testIndexActionShowsErrorWhenCacheClearFails(): void
    {
        $this->request
            ->shouldReceive('isPost')
            ->once()
            ->andReturnTrue();

        $response = m::mock();

        $this->sut
            ->shouldReceive('handleCommand')
            ->once()
            ->with(m::type(Clear::class))
            ->andReturn($response);

        $response
            ->shouldReceive('isServerError')
            ->once()
            ->andReturnTrue();

        $response
            ->shouldReceive('isClientError')
            ->never();

        $response
            ->shouldReceive('isOk')
            ->once()
            ->andReturnFalse();

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
}
