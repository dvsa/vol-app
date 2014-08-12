<?php

/**
 * Sole Trader Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace SelfServe\Test\Controller\Application\YourBusiness;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Sole Trader Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SoleTraderControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\YourBusiness\SoleTraderController';
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
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction(
            'index', null, array(
            'form-actions' => array('submit' => ''),
            'data' => array(
                'dateOfBirth' => array(
                    'month' => '02',
                    'day'   => '23',
                    'year' => '2014'
                ),
                'id' => 79,
                'version' => 4,
                'title' => 'Mrs',
                'firstName' => 'A',
                'surname' => 'P12',
                'otherNames' => 'other12'
            )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with back button
     */
    public function testIndexActionWithBack()
    {
        $this->setUpAction(
            'index', null, array(
            'form-actions' => array('back' => 'Back')
        )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

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
            $licenceDataBundle = array(
                'children' => array(
                    'licence' => array(
                        'properties' => array(
                            'id',
                            'version',
                            'niFlag'
                        ),
                        'children' => array(
                            'goodsOrPsv' => array(
                                'properties' => array(
                                    'id'
                                )
                            ),
                            'licenceType' => array(
                                'properties' => array(
                                    'id'
                                )
                            ),
                            'organisation' => array(
                                'children' => array(
                                    'type' => array(
                                        'properties' => array(
                                            'id'
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            );

            $orgTypeBundle = array(
                'children' => array(
                    'licence' => array(
                        'children' => array(
                            'organisation' => array(
                                'properties' => array(
                                    'id',
                                    'version'
                                ),
                                'children' => array(
                                    'type' => array(
                                        'properties' => array('id')
                                    )
                                )
                            )
                        )
                    )
                )
            );
            if ($bundle == $orgTypeBundle) {
                return array(
                    'licence' => array(
                        'organisation' => array(
                            'type' => array(
                                'id' => $this->organisation
                            ),
                        )
                    )
                );
            } elseif ($bundle == $licenceDataBundle) {
                return array(
                    'licence' => array(
                        'id' => 10,
                        'version' => 1,
                        'niFlag' => 0,
                        'goodsOrPsv' => array(
                            'id' => ApplicationController::GOODS_OR_PSV_GOODS_VEHICLE
                        ),
                        'licenceType' => array(
                            'id' => ApplicationController::LICENCE_TYPE_STANDARD_NATIONAL
                        ),
                        'organisation' => array(
                            'type' => array(
                                'id' => ApplicationController::ORG_TYPE_SOLE_TRADER
                            )
                        )
                    )
                );
            } else {
                return array(
                    'id' => 1,
                    'version' => 1,
                );
            }
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }

        $personDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'title',
                'firstName',
                'surname',
                'dateOfBirth',
                'otherNames'
            ),
        );

        if ($service == 'Person' && $method = 'GET' && $bundle == $personDataBundle) {
            return array(
                'Count'  => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'version' => 1,
                        'title' => 'Mr',
                        'firstName' => 'A',
                        'surname' => 'P',
                        'dateOfBirth' => '2014-01-01',
                        'otherNames' => 'other names'
                    )
                )
            );
        }
    }
}
