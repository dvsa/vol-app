<?php

/**
 * LicenceType Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\TypeOfLicence;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * LicenceType Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTypeControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\TypeOfLicence\LicenceTypeController';
    protected $defaultRestResponse = array();

    private $goodsOrPsv;
    private $niFlag = 0;

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test back button with niFlag
     */
    public function testBackButtonNiFlag()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $this->niFlag = 1;

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction
     *
     * @dataProvider psvProvider
     */
    public function testIndexAction($goodsOrPsv, $hasSpecial)
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromView($response);

        $options = $form->get('licence-type')->get('licenceType')->getValueOptions();

        $this->assertEquals($hasSpecial, isset($options[ApplicationController::LICENCE_TYPE_SPECIAL_RESTRICTED]));
    }

    /**
     * Psv provider
     *
     * @return array
     */
    public function psvProvider()
    {
        return array(
            array('psv', true),
            array('goods', false)
        );
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData($this->goodsOrPsv, 'ltyp_sn', $this->niFlag);
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }
    }
}
