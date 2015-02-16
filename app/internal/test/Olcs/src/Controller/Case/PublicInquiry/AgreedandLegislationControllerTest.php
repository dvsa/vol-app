<?php

/**
 * AgreedAndLegislation Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;
use \Olcs\TestHelpers\ControllerRouteMatchHelper;
use \Olcs\Controller\Cases\PublicInquiry\AgreedAndLegislationController;

/**
 * AgreedAndLegislation Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AgreedAndLegislationControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->sut = new AgreedAndLegislationController();

        parent::setUp();
    }

    public function testProcessLoadAddsDateIfEmpty()
    {
        //we won't use a proper date as it would fail if run at bang on midnight, just check the key exists instead :)
        $returnedData = $this->sut->processLoad([]);

        $this->assertArrayHasKey('fields', $returnedData);
        $this->assertArrayHasKey('agreedDate', $returnedData['fields']);
    }

    //
    /**
     * There is nothing to test here as process load runs the same as it would in normal circumstances,
     * we're actually just testing that the parent has been called as expected
     */
    public function testProcessLoadCalledWhenDataPresent()
    {
        $id = 1;

        $expected = [
            'id' => $id,
            'fields' => [
                'id' => $id
            ],
            'base' => [
                'id' => $id,
                'fields' => [
                    'id' => $id
                ]
            ]
        ];

        $this->assertEquals($expected, $this->sut->processLoad(['id' => $id]));
    }
}
