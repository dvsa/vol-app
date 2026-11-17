<?php

declare(strict_types=1);

namespace PermitsTest\Controller;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Permits\Controller\IrhpApplicationFeeController;
use Permits\Data\Mapper\MapperManager;

final class IrhpApplicationFeeControllerTest extends MockeryTestCase
{
    private IrhpApplicationFeeController $sut;

    private HandleCommand&m\MockInterface $handleCommand;

    private HandleQuery&m\MockInterface $handleQuery;

    public function setUp(): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);
        $translationHelper->allows('translate')->andReturnArg(0);

        $this->sut = new IrhpApplicationFeeController(
            $translationHelper,
            m::mock(FormHelperService::class),
            m::mock(TableFactory::class),
            m::mock(MapperManager::class),
            m::mock(FlashMessengerHelperService::class)
        );

        $params = m::mock(Params::class);
        $params->allows('__invoke')->andReturnSelf();
        $params->allows('fromRoute')->with('id', -1)->andReturn(21);

        $url = m::mock(Url::class);
        $url->allows('fromRoute')->andReturn('https://ssweb/permits/application/21/payment-result');

        $placeholder = m::mock(AbstractPlugin::class);
        $placeholder->allows('setPlaceholder');

        $this->handleCommand = m::mock(HandleCommand::class);
        $this->handleQuery = m::mock(HandleQuery::class);

        // A real plugin manager holding only the plugins the site has, so a call the controller
        // can't answer fails here the way it does on a server
        $plugins = new PluginManager(new ServiceManager());
        foreach (
            [
                'params' => $params,
                'url' => $url,
                'placeholder' => $placeholder,
                'handleCommand' => $this->handleCommand,
                'handleQuery' => $this->handleQuery,
            ] as $name => $plugin
        ) {
            $plugin->allows('setController');
            $plugins->setService($name, $plugin);
        }
        $this->sut->setPluginManager($plugins);
    }

    private function answer(array $result): Response&m\MockInterface
    {
        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturn(true);
        $response->allows('getResult')->andReturn($result);

        return $response;
    }

    public function testPayingByCardHandsTheOperatorOverToThePaymentGateway(): void
    {
        $this->handleCommand
            ->expects('__invoke')
            ->with(m::on(static fn ($command): bool => $command instanceof PayOutstandingFees
                && $command->getIrhpApplication() === 21))
            ->andReturn($this->answer(['id' => ['transaction' => 77], 'messages' => []]));

        $this->handleQuery
            ->expects('__invoke')
            ->with(m::on(static fn ($query): bool => $query instanceof Transaction && $query->getId() === 77))
            ->andReturn($this->answer(['gatewayUrl' => 'https://gateway.example/pay', 'reference' => 'OLCS-01']));

        $view = $this->sut->paymentAction();

        $this->assertInstanceOf(ViewModel::class, $view);
        $this->assertSame('cpms/payment', $view->getTemplate());
        $this->assertSame('https://gateway.example/pay', $view->getVariable('gateway'));
        $this->assertSame(['receipt_reference' => 'OLCS-01'], $view->getVariable('data'));
    }
}
