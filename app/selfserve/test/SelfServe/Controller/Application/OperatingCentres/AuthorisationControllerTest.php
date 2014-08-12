<?php

/**
 * Authorisation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Test\Controller\Application\OperatingCentres;

use SelfServe\Test\Controller\Application\AbstractApplicationControllerTestCase;
use SelfServe\Controller\Application\ApplicationController;

/**
 * Authorisation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationControllerTest extends AbstractApplicationControllerTestCase
{

    protected $controllerName = '\SelfServe\Controller\Application\OperatingCentres\AuthorisationController';

    protected $defaultRestResponse = array(
        'OperatingCentre' => array(
            'POST' => array(
                'id' => 1
            )
        ),
        'ApplicationOperatingCentre' => array(
            'POST' => array(
                'id' => 2
            )
        )
    );

    private $goodsOrPsv;

    protected $mockedMethods = array('getUploader', 'getFileSizeValidator');

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
     *
     * @dataProvider psvProvider
     */
    public function testIndexAction($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals($hasTrailers, (boolean) strstr($tableHtml, 'trailer'));
        //$this->assertEquals($hasTrailers, $form->get('data')->has('totAuthTrailers'));
        $this->assertEquals($hasTrailers, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals($hasTrailers, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction standard national
     */
    public function testIndexActionStandardNational()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(false, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(false, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(true, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(true, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(true, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction standard internation
     */
    public function testIndexActionStandardInternational()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-international',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(false, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(false, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(true, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(true, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(true, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction restricted
     */
    public function testIndexActionRestricted()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'restricted',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(false, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(false, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(true, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(true, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(true, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction standard national
     */
    public function testIndexActionStandardNationalPsv()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'psv';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(false, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(true, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(false, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(false, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(false, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction standard internation
     */
    public function testIndexActionStandardInternationalPsv()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'psv';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-international',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(true, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(true, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(false, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(false, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(false, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction restricted
     */
    public function testIndexActionRestrictedPsv()
    {
        $this->setUpAction('index');

        $this->goodsOrPsv = 'psv';

        $this->setRestResponse(
            'Application',
            'GET',
            array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'restricted',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            )
        );

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $tableHtml = $main->getVariable('table');
        $form = $main->getVariable('form');

        $this->assertEquals(true, $form->get('data')->has('totCommunityLicences'));
        $this->assertEquals(false, $form->get('data')->has('totAuthLargeVehicles'));

        $this->assertEquals(false, (boolean) strstr($tableHtml, 'trailer'));
        $this->assertEquals(false, $form->get('data')->has('minTrailerAuth'));
        $this->assertEquals(false, $form->get('data')->has('maxTrailerAuth'));
    }

    /**
     * Test indexAction with crud action
     */
    public function testIndexActionWithCrudAction()
    {
        $this->setUpAction('index', null, array('action' => 'Add'));

        $this->goodsOrPsv = 'goods';

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexActionSubmit
     *
     * @dataProvider psvProvider
     */
    public function testIndexActionSubmit($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction(
            'index', null, array(
                'data' => array(
                    'id' => 1,
                    'version' => 6,
                    'totAuthVehicles' => 10,
                    'noOfOperatingCentres' => 1,
                    'minVehicleAuth' => 10,
                    'maxVehicleAuth' => 10,
                    'minTrailerAuth' => 10,
                    'maxTrailerAuth' => 10
                )
            )
        );

        $this->goodsOrPsv = $goodsOrPsv;

        $this->controller->setEnabledCsrf(false);

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction
     *
     * @dataProvider psvProvider
     */
    public function testAddAction($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction('add');

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->addAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $form = $main->getVariable('form');
        $this->assertEquals($hasTrailers, $form->get('data')->has('noOfTrailersPossessed'));
    }

    /**
     * Test addAction with file upload
     */
    public function testAddActionWithFileUpload()
    {
        $file = array(
            'tmp_name' => 'ngipushfdlgjk',
            'name' => 'ajkhdfjklah',
            'type' => 'image/png',
            'error' => 0
        );

        $post = array(
            'advertisements' => array(
                'file' => array(
                    'file-controls' => array(
                        'upload' => 'Upload'
                    )
                )
            )
        );

        $files = array(
            'advertisements' => array(
                'file' => array(
                    'file-controls' => array(
                        'file' => $file
                    )
                )
            )
        );

        $this->setUpAction('add', null, $post, $files);

        $this->goodsOrPsv = 'goods';

        $mockUploader = $this->getMock('\Common\Service\File\DiskStoreFileUploader', array('upload'));

        $mockUploader->expects($this->once())
            ->method('upload');

        $this->controller->expects($this->any())
            ->method('getUploader')
            ->will($this->returnValue($mockUploader));

        $mockValidator = $this->getMock('\stdClass', array('isValid'));
        $mockValidator->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->controller->expects($this->any())
            ->method('getFileSizeValidator')
            ->will($this->returnValue($mockValidator));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test addAction with submit
     *
     * @dataProvider psvProvider
     */
    public function testAddActionWithSubmit($goodsOrPsv, $hasTrailers)
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'town' => 'City',
                'postcode' => 'AN1 1ND',
                'countryCode' => 'GB'
            ),
            'data' => array(
                'noOfVehiclesPossessed' => 10,
                'noOfTrailersPossessed' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            ),
            'advertisements' => array(
                'adPlaced' => 'N',
                'file' => array(
                    'list' => array(
                        'file-1' => array(
                            'id' => 1,
                            'version' => 1
                        )
                    )
                )
            )
        );

        $files = array(
            'advertisements' => array(
                'file' => array(

                )
            )
        );

        $this->setUpAction('add', null, $post, $files);

        $this->goodsOrPsv = $goodsOrPsv;

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with cancel
     */
    public function testAddActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('add', null, $post);

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test editAction with cancel
     */
    public function testEditActionWithCancel()
    {
        $post = array(
            'form-actions' => array(
                'cancel' => 'Cancel'
            )
        );

        $this->setUpAction('edit', 1, $post);

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with add another
     *
     * @dataProvider psvProvider
     */
    public function testAddActionWithSubmitWithAddAnother($goodsOrPsv, $hasTrailers)
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'town' => 'City',
                'postcode' => 'AN1 1ND',
                'countryCode' => 'GB'
            ),
            'data' => array(
                'noOfVehiclesPossessed' => 10,
                'noOfTrailersPossessed' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            ),
            'form-actions' => array(
                'addAnother' => 'Add another'
            ),
            'advertisements' => array(
                'adPlaced' => 'N',
                'file' => array(
                    'list' => array(
                        'file-1' => array(
                            'id' => 1,
                            'version' => 1
                        )
                    )
                )
            )
        );

        $files = array(
            'advertisements' => array(
                'file' => array(

                )
            )
        );

        $this->setUpAction('add', null, $post, $files);

        $this->goodsOrPsv = $goodsOrPsv;

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->addAction();

        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testAddActionWithSubmitWithFailure()
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'town' => 'City',
                'postcode' => 'AN1 1ND',
                'countryCode' => 'GB'
            ),
            'data' => array(
                'noOfVehiclesPossessed' => 10,
                'noOfTrailersPossessed' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            ),
            'advertisements' => array(
                'adPlaced' => 'N',
                'file' => array(
                    'list' => array(
                        'file-1' => array(
                            'id' => 1,
                            'version' => 1
                        )
                    )
                )
            )
        );

        $files = array(
            'advertisements' => array(
                'file' => array(

                )
            )
        );

        $this->setUpAction('add', null, $post, $files);

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'OperatingCentre', 'POST', ''
        );

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test addAction with submit with failure
     *
     * @expectedException \Exception
     */
    public function testAddActionWithSubmitWithFailure2()
    {
        $post = array(
            'address' => array(
                'id' => '',
                'version' => '',
                'addressLine1' => 'Some street',
                'town' => 'City',
                'postcode' => 'AN1 1ND',
                'countryCode' => 'GB'
            ),
            'data' => array(
                'noOfVehiclesPossessed' => 10,
                'noOfTrailersPossessed' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            ),
            'advertisements' => array(
                'adPlaced' => 'N',
                'file' => array(
                    'list' => array(
                        'file-1' => array(
                            'id' => 1,
                            'version' => 1
                        )
                    )
                )
            )
        );

        $files = array(
            'advertisements' => array(
                'file' => array(

                )
            )
        );

        $this->setUpAction('add', null, $post, $files);

        $this->goodsOrPsv = 'goods';

        $this->setRestResponse(
            'ApplicationOperatingCentre', 'POST', ''
        );

        $this->controller->setEnabledCsrf(false);
        $this->controller->addAction();
    }

    /**
     * Test editAction
     *
     * @dataProvider psvProvider
     */
    public function testEditAction($goodsOrPsv, $hasTrailers)
    {
        $this->setUpAction('edit', 3);

        $this->goodsOrPsv = $goodsOrPsv;

        $response = $this->controller->editAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);

        // We should have 2 children (Navigation and Main)
        $children = $response->getChildren();
        $this->assertEquals(2, count($children));

        $main = null;
        $navigation = null;

        foreach ($children as $child) {
            if ($child->captureTo() == 'navigation') {
                $navigation = $child;
                continue;
            }

            if ($child->captureTo() == 'main') {
                $main = $child;
            }
        }

        // Assert that we have Main and Navigation views
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $main);
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $navigation);

        // We are not psv, so should have trailer related content
        $form = $main->getVariable('form');
        $this->assertEquals($hasTrailers, $form->get('data')->has('noOfTrailersPossessed'));
    }

    /**
     * Test editAction with submit
     */
    public function testEditActionWithSubmit()
    {
        $post = array(
            'address' => array(
                'id' => 3,
                'version' => 1,
                'addressLine1' => 'Some street',
                'town' => 'City',
                'postcode' => 'AN1 1ND',
                'countryCode' => 'GB'
            ),
            'data' => array(
                'noOfVehiclesPossessed' => 10,
                'noOfTrailersPossessed' => 10,
                'sufficientParking' => '1',
                'permission' => '1'
            ),
            'advertisements' => array(
                'adPlaced' => 'N',
                'file' => array(
                    'list' => array(
                        'file-1' => array(
                            'id' => 1,
                            'version' => 1
                        )
                    )
                )
            )
        );

        $files = array(
            'advertisements' => array(
                'file' => array(
                )
            )
        );

        $this->setUpAction('edit', 3, $post, $files);

        $this->goodsOrPsv = 'goods';

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->editAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test deleteAction
     */
    public function testDeleteAction()
    {
        $this->setUpAction('delete', 3);

        $response = $this->controller->deleteAction();

        // Assert that the response redirects to Authorisation
        $this->assertInstanceOf('\Zend\Http\Response', $response);
    }

    /**
     * Provider for indexAction
     *
     * @return array
     */
    public function psvProvider()
    {
        return array(
            array('psv', false),
            array('goods', true)
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

            return array(
                'licence' => array(
                    'id' => 10,
                    'version' => 1,
                    'goodsOrPsv' => $this->goodsOrPsv,
                    'niFlag' => 0,
                    'licenceType' => 'standard-national',
                    'organisation' => array(
                        'type' => 'org_type.lc'
                    )
                )
            );
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'version' => 1,
                        'application' => '1',
                        'sectionTypeOfLicenceStatus' => 2,
                        'sectionTypeOfLicenceOperatorLocationStatus' => 2,
                        'sectionTypeOfLicenceOperatorTypeStatus' => 2,
                        'sectionTypeOfLicenceLicenceTypeStatus' => 2,
                        'sectionYourBusinessStatus' => 2,
                        'sectionYourBusinessBusinessTypeStatus' => 2,
                        'sectionYourBusinessBusinessDetailsStatus' => 2,
                        'sectionYourBusinessAddressesStatus' => 2,
                        'sectionYourBusinessPeopleStatus' => 2,
                        'sectionTaxiPhvStatus' => 2,
                        'sectionOperatingCentresStatus' => 2,
                        'sectionOperatingCentresAuthorisationStatus' => 2,
                        'sectionOperatingCentresFinancialEvidenceStatus' => 2,
                        'sectionTransportManagersStatus' => 2,
                        'sectionVehicleSafetyStatus' => 2,
                        'sectionVehicleSafetyVehicleStatus' => 2,
                        'sectionVehicleSafetySafetyStatus' => 2,
                        'sectionPreviousHistoryStatus' => 2,
                        'sectionPreviousHistoryFinancialHistoryStatus' => 2,
                        'sectionPreviousHistoryLicenceHistoryStatus' => 2,
                        'sectionPreviousHistoryConvictionPenaltiesStatus' => 2,
                        'sectionReviewDeclarationsStatus' => 2,
                        'sectionPaymentSubmissionStatus' => 2,
                        'sectionPaymentSubmissionPaymentStatus' => 0,
                        'sectionPaymentSubmissionSummaryStatus' => 0,
                        'lastSection' => ''
                    )
                )
            );
        }

        if ($service == 'Document' && $method == 'GET') {
            return array(
                'Count' => 0,
                'Results' => array()
            );
        }

        $actionDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'noOfTrailersPossessed',
                'noOfVehiclesPossessed',
                'sufficientParking',
                'permission',
                'adPlaced',
                'adPlacedIn',
                'adPlacedDate'
            ),
            'children' => array(
                'operatingCentre' => array(
                    'properties' => array(
                        'id',
                        'version'
                    ),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'addressLine1',
                                'addressLine2',
                                'addressLine3',
                                'addressLine4',
                                'postcode',
                                'town'
                            ),
                            'children' => array(
                                'countryCode' => array(
                                    'properties' => array('id')
                                )
                            )
                        ),
                        'adDocuments' => array(
                            'properties' => array(
                                'id',
                                'version',
                                'filename',
                                'identifier',
                                'size'
                            )
                        )
                    )
                )
            )
        );

        if ($service == 'ApplicationOperatingCentre'
            && $method == 'GET'
            && $bundle === $actionDataBundle && isset($data['id'])) {

            return array(
                'id' => 1,
                'version' => 2,
                'noOfTrailersPossessed' => 10,
                'noOfVehiclesPossessed' => 10,
                'sufficientParking' => 1,
                'permission' => 1,
                'adPlaced' => 0,
                'adPlacedIn' => null,
                'adPlacedDate' => null,
                'operatingCentre' => array(
                    'id' => 3,
                    'version' => 1,
                    'address' => array(
                        'id' => 1,
                        'version' => 1,
                        'addressLine1' => 'Some street 1',
                        'addressLine2' => 'Some street 2',
                        'addressLine3' => 'Some street 3',
                        'addressLine4' => 'Some street 4',
                        'postcode' => 'AB1 1AB',
                        'town' => 'City',
                        'countryCode' => 'GB'
                    ),
                    'adDocuments' => array(
                        array(
                            'id' => 1,
                            'identifier' => 'adfasdadsag',
                            'version' => 1,
                            'filename' => 'nfjosjnfos',
                            'size' => 10
                        )
                    )
                )
            );
        }

        if ($service == 'ApplicationOperatingCentre' && $method == 'GET') {
            return array(
                'Count' => 1,
                'Results' => array(
                    array(
                        'id' => 1,
                        'adPlaced' => 1,
                        'permission' => 1,
                        'noOfVehiclesPossessed' => 10,
                        'noOfTrailersPossessed' => 10,
                        'operatingCentre' => array(
                            'address' => array(
                                'id' => 1,
                                'addressLine1' => '123 Street',
                                'addressLine2' => 'Address 2',
                                'addressLine3' => 'Address 3',
                                'addressLine4' => 'Address 4',
                                'town' => 'City',
                                'countryCode' => 'GB',
                                'postcode' => 'AB1 1AB'
                            )
                        )
                    )
                )
            );
        }

        $controllerDataBundle = array(
            'properties' => array(
                'id',
                'version',
                'totAuthSmallVehicles',
                'totAuthMediumVehicles',
                'totAuthLargeVehicles',
                'totCommunityLicences',
                'totAuthVehicles',
                'totAuthTrailers'
            )
        );

        if ($service == 'Application' && $method == 'GET' && $bundle == $controllerDataBundle) {
            return array(
                'id' => 1,
                'version' => 1,
                'totAuthVehicles' => 10,
                'totAuthTrailers' => 10
            );
        }
    }
}
