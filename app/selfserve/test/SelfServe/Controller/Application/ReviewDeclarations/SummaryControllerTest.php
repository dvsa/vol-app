<?php

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\ReviewDeclarations;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\SelfServe\Controller\Application\ReviewDeclarations\SummaryController';

    protected $defaultRestResponse = array();

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
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with form alterations
     */
    public function testIndexActionWithFormAlterations()
    {
        $this->setUpAction('index');

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'prevConviction' => true,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => array(
                        'id' => ApplicationController::GOODS_OR_PSV_GOODS_VEHICLE
                    ),
                    'niFlag' => 0,
                    'licenceType' => array(
                        'id' => 'ltyp_sn'
                    ),
                    'organisation' => array(
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'documents' => array()
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test simpleAction
     */
    public function testSimpleAction()
    {
        $this->setUpAction('simple');

        $response = $this->controller->simpleAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction('index', null, array('foo' => 'bar'));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
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
        if ($service == 'Application' && $method == 'GET') {
            return array(
                'prevConviction' => true,
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => array(
                        'id' => ApplicationController::GOODS_OR_PSV_GOODS_VEHICLE
                    ),
                    'niFlag' => 0,
                    'licenceType' => array(
                        'id' => 'ltyp_sn'
                    ),
                    'organisation' => array(
                        'type' => array(
                            'id' => ApplicationController::ORG_TYPE_REGISTERED_COMPANY
                        )
                    )
                ),
                'documents' => array()
            );
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }

        $convictionDataBundle = array(
            'properties' => array(
                'id',
                'convictionDate',
                'notes',
                'court',
                'penalty'
            ),
            'children' => array(
                'person' => array(
                    'properties' => array(
                        'title',
                        'forename',
                        'familyName'
                    )
                )
            )
        );

        if ($service == 'Conviction' && $method === 'GET' && $bundle == $convictionDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'convictionDate' => '01/01/2014',
                        'notes' => 'No MOT',
                        'court' => 'Leeds court',
                        'penalty' => '100£',
                        'person' => array(
                            'title' => 'Mr',
                            'forename' => 'Alex',
                            'familyName' => 'P'
                        )
                    )
                )
            );
        }
    }
}
