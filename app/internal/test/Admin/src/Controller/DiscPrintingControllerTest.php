<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\DiscPrintingController;
use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Form\Element\Select;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class DiscPrintingControllerTest extends MockeryTestCase
{
    /**
     * A post that arrives with nothing in it is what PHP hands over when the request was bigger
     * than it accepts. The shared form code answers that with an error message
     */
    public function testAnEmptyPostShowsAnError(): void
    {
        $sut = new DiscPrintingController(
            m::mock(ScriptFactory::class),
            m::mock(TableFactory::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(FormHelperService::class)
        );

        $flashMessenger = m::mock(LaminasSessionFlashMessenger::class);
        $flashMessenger->allows('setController');
        $flashMessenger->expects('addErrorMessage')->with('message.post_max_size_exceeded');

        $params = m::mock(Params::class);
        $params->allows('setController');
        $params->allows('__invoke')->andReturnSelf();
        $params->allows('fromPost')->with('operator-location')->andReturn(['niFlag' => 'N']);

        // a real plugin manager, so a call the controller can't answer fails as it does on a server
        $plugins = new PluginManager(new ServiceManager());
        $plugins->setService('commonFlashMessenger', $flashMessenger);
        $plugins->setService('params', $params);
        $sut->setPluginManager($plugins);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        (function () use ($request): void {
            $this->request = $request;
        })->call($sut);

        $licenceType = new Fieldset('licence-type');
        $licenceType->add(new Select('licenceType'));
        $form = new Form();
        $form->add($licenceType);

        $this->assertSame($form, $sut->formPost($form));
    }
}
