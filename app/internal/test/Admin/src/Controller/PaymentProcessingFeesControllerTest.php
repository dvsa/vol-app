<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\PaymentProcessingFeesController;
use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\Redirect;
use Common\Service\Cqrs\Response;
use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Fee\CreateFee;
use Laminas\Form\Fieldset;
use Laminas\Form\FormInterface;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class PaymentProcessingFeesControllerTest extends MockeryTestCase
{
    private const string ROUTE = 'admin-dashboard/admin-payment-processing/misc-fees';

    private PaymentProcessingFeesController $sut;

    private LaminasSessionFlashMessenger&m\MockInterface $flashMessenger;

    private HandleCommand&m\MockInterface $handleCommand;

    private Redirect&m\MockInterface $redirect;

    public function setUp(): void
    {
        $this->sut = new PaymentProcessingFeesController(
            m::mock(ScriptFactory::class),
            m::mock(TableFactory::class),
            m::mock(FormHelperService::class),
            m::mock(UrlHelperService::class),
            m::mock(IdentityProviderInterface::class),
            m::mock(TranslationHelperService::class),
            m::mock(DateHelperService::class)
        );

        $this->flashMessenger = m::mock(LaminasSessionFlashMessenger::class);
        $this->handleCommand = m::mock(HandleCommand::class);
        $this->redirect = m::mock(Redirect::class);

        // A real plugin manager holding only the plugins the site has, so a call the controller
        // can't answer fails here the way it does on a server
        $plugins = new PluginManager(new ServiceManager());
        foreach (
            [
                'commonFlashMessenger' => $this->flashMessenger,
                'handleCommand' => $this->handleCommand,
                'redirect' => $this->redirect,
            ] as $name => $plugin
        ) {
            $plugin->allows('setController');
            $plugins->setService($name, $plugin);
        }
        $this->sut->setPluginManager($plugins);
    }

    /**
     * What the add fee form hands over once it is valid
     */
    private function createFee(FormInterface $form): void
    {
        $data = [
            'form' => $form,
            'validData' => [
                'fee-details' => ['createdDate' => '2026-11-17', 'feeType' => 40008, 'amount' => '25.00'],
            ],
        ];

        (fn (array $data) => $this->createFee($data))->call($this->sut, $data);
    }

    private function apiAnswers(bool $ok, array $result = []): void
    {
        $response = m::mock(Response::class);
        $response->allows('isOk')->andReturn($ok);
        $response->allows('getResult')->andReturn($result);

        $this->handleCommand
            ->expects('__invoke')
            ->with(m::on(static fn ($command): bool => $command instanceof CreateFee
                && $command->getFeeType() === 40008
                && $command->getAmount() === '25.00'
                && $command->getInvoicedDate() === '2026-11-17'))
            ->andReturn($response);
    }

    public function testCreatingAFeeSaysItWorkedAndGoesBackToTheList(): void
    {
        $this->apiAnswers(true);

        $this->flashMessenger->expects('addSuccessMessage')->with('fees.create.success');
        $this->redirect->expects('toRouteAjax')->with(self::ROUTE, [], ['query' => []]);

        $this->createFee(m::mock(FormInterface::class));
    }

    public function testAFeeTheApiTurnsDownShowsAnError(): void
    {
        $this->apiAnswers(false, ['messages' => ['general' => ['Fee type is not valid']]]);

        $form = m::mock(FormInterface::class);
        $form->expects('get')->with('fee-details')->andReturn(new Fieldset('fee-details'));

        $this->flashMessenger->expects('addErrorMessage')->with('fees.create.error');
        $this->redirect->expects('toRouteAjax')->never();

        $this->createFee($form);
    }
}
