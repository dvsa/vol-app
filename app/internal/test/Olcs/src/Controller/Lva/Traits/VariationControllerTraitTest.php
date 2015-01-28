<?php

/**
 * Variation Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\VariationCompletionEntityService;

/**
 * Variation Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationControllerTraitTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new Stubs\VariationControllerTraitStub();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetSectionsForView()
    {
        // Params
        $id = 3;
        $accessibleSections = [
            'type_of_licence' => [
                'foo' => '123'
            ],
            'business_type' => [
                'foo' => '456'
            ],
            'business_details' => [
                'foo' => '789'
            ]
        ];

        $mockStatuses = [
            'type_of_licence' => VariationCompletionEntityService::STATUS_UPDATED,
            'business_type' => VariationCompletionEntityService::STATUS_UNCHANGED,
            'business_details' => VariationCompletionEntityService::STATUS_REQUIRES_ATTENTION,
        ];

        $expected = [
            'overview' => [
                'class' => 'no-background',
                'route' => 'lva-variation'
            ],
            'type_of_licence' => [
                'foo' => '123',
                'class' => 'edited',
                'route' => 'lva-variation/type_of_licence'
            ],
            'business_type' => [
                'foo' => '456',
                'class' => '',
                'route' => 'lva-variation/business_type'
            ],
            'business_details' => [
                'foo' => '789',
                'class' => 'incomplete',
                'route' => 'lva-variation/business_details'
            ]
        ];

        // Setup
        $this->sut->setApplicationId($id);
        $this->sut->setAccessibleSections($accessibleSections);

        // Mocks
        $mockVariationCompletion = m::mock();
        $this->sm->setService('Entity\VariationCompletion', $mockVariationCompletion);

        // Expectations
        $mockVariationCompletion->shouldReceive('getCompletionStatuses')
            ->with($id)
            ->andReturn($mockStatuses);

        $response = $this->sut->callGetSectionsForView();

        $this->assertEquals($expected, $response);
    }
}
