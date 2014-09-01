<?php

/**
 * Safety Controller Test
 */
namespace OlcsTest\Controller\Licence\Details;

use OlcsTest\Controller\Licence\Details\AbstractLicenceDetailsControllerTestCase;
use Olcs\Controller\Licence\Details\AbstractLicenceDetailsController;

/**
 * Safety Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyControllerTest extends AbstractLicenceDetailsControllerTestCase
{
    protected $controllerName = 'Olcs\Controller\Licence\Details\SafetyController';
    protected $goodsOrPsv = AbstractLicenceDetailsController::LICENCE_CATEGORY_GOODS_VEHICLE;
    protected $routeName = 'licence/details/safety';

    /**
     * Test index action with goods and psv licences
     *
     * @group Licence_SafetyController
     * @dataProvider goodsOrPsvProvider
     */
    public function testIndexAction($goodsOrPsv, $hasTrailerElement)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        $this->setupAction('index');

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $response);

        $form = $this->getFormFromView($response);

        $this->assertEquals($hasTrailerElement, $form->get('licence')->has('safetyInsTrailers'));
    }

    /**
     * Test cancel button redirects
     *
     * @group Licence_SafetyController
     */
    public function testIndexActionWithCancel()
    {
        $this->setupAction('index', null, array('form-actions' => array('cancel' => 'cancel')));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test index action with submit
     *
     * @group Licence_SafetyController
     */
    public function testIndexActionWithSubmit()
    {
        $this->setupAction(
            'index',
            null,
            array(
                'licence' => array(
                    'safetyInsVehicles' => 'inspection_interval_vehicle.1',
                    'safetyInsTrailers' => 'inspection_interval_trailer.1',
                    'safetyInsVaries' => 'Y',
                    'tachographIns' => 'tach_internal',
                    'tachographInsName' => 'Foo',
                    'version' => 1,
                    'id' => 1
                ),
                'application' => array(
                    'isMaintenanceSuitable' => 'Y',
                    'safetyConfirmation' => 'Y',
                    'id' => 1,
                    'version' => 1
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Goods or psv provider
     *
     * @return array
     */
    public function goodsOrPsvProvider()
    {
        return array(
            array(
                AbstractLicenceDetailsController::LICENCE_CATEGORY_GOODS_VEHICLE,
                true
            ),
            array(
                AbstractLicenceDetailsController::LICENCE_CATEGORY_PSV,
                false
            )
        );
    }

    /**
     * Mock rest calls
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     * @return array
     */
    protected function mockRestCalls($service, $method, $data, $bundle)
    {
        $dataBundle = array(
            'properties' => array(
                'id',
                'version',
                'safetyInsVehicles',
                'safetyInsTrailers',
                'safetyInsVaries',
                'tachographInsName',
                'isMaintenanceSuitable',
            ),
            'children' => array(
                'goodsOrPsv' => array(
                    'properties' => array('id')
                ),
                'tachographIns' => array(
                    'properties' => array('id')
                ),
                'workshops' => array(
                    'properties' => array(
                        'id',
                        'isExternal'
                    ),
                    'children' => array(
                        'contactDetails' => array(
                            'properties' => array(
                                'fao'
                            ),
                            'children' => array(
                                'address' => array(
                                    'properties' => array(
                                        'addressLine1',
                                        'addressLine2',
                                        'addressLine3',
                                        'addressLine4',
                                        'town',
                                        'postcode'
                                    ),
                                    'children' => array(
                                        'countryCode' => array(
                                            'properties' => array('id')
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'Licence' && $method == 'GET' && $bundle == $dataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'safetyInsVehicles' => 1,
                'safetyInsTrailers' => 1,
                'safetyInsVaries' => 0,
                'tachographInsName' => 'Foo',
                'isMaintenanceSuitable' => 1,
                'goodsOrPsv' => array(
                    'id' => $this->goodsOrPsv
                ),
                'tachographIns' => array(
                    'id' => 'tach_internal'
                ),
                'workshops' => array(
                    array(
                        'id' => 1,
                        'isExternal' => 1,
                        'contactDetails' => array(
                            'fao' => 'Joe Bloggs',
                            'address' => array(
                                'addressLine1' => '123',
                                'addressLine2' => 'Foo lane',
                                'addressLine3' => '',
                                'addressLine4' => '',
                                'town' => 'Bobtown',
                                'postcode' => 'AB1 1AB',
                                'countryCode' => array(
                                    'id' => 'GB'
                                )
                            )
                        )
                    )
                )
            );
        }
    }
}
