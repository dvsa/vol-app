<?php

/**
 * Scanning Controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace AdminTest\Controller;

use OlcsTest\Bootstrap;
use Olcs\TestHelpers\Lva\Traits\LvaControllerTestTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Scanning Controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ScanningControllerTest extends MockeryTestCase
{
    use LvaControllerTestTrait;

    /**
     * Required by trait
     */
    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function testIndexActionPopulatesDefaultValues()
    {
        $this->mockController('\Admin\Controller\ScanningController');

        $form = $this->createMockForm('Scanning');

        $this->setService(
            'DataServiceManager',
            m::mock()
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\SubCategory')
            ->andReturn(
                m::mock()
                ->shouldReceive('setCategory')
                ->with(1)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\SubCategoryDescription')
            ->andReturn(
                m::mock()
                ->shouldReceive('setSubCategory')
                ->with(85)
                ->getMock()
            )
            ->getMock()
        );

        $data = [
            'details' => [
                'category' => 1,
                'subCategory' => 85
            ]
        ];

        $form->shouldReceive('setData')
            ->with($data)
            ->andReturn($form);

        $this->sut->indexAction();
    }

    public function testIndexActionWithInvalidPost()
    {
        $this->mockController('\Admin\Controller\ScanningController');

        $post = [
            'details' => [
                'category' => 1,
                'subCategory' => 2
            ]
        ];
        $this->setPost($post);

        $form = $this->createMockForm('Scanning');

        $form->shouldReceive('setData')
            ->with($post)
            ->andReturn($form)
            ->shouldReceive('isValid')
            ->andReturn(false);

        $this->sut->indexAction();
    }

    public function testIndexActionWithValidPostNoMatchingEntity()
    {
        $this->mockController('\Admin\Controller\ScanningController');

        $post = [
            'details' => [
                'category' => 1,
                'subCategory' => 2,
                'entityIdentifier' => 'ABC123'
            ]
        ];

        $this->setPost($post);

        $this->setService(
            'Processing\Entity',
            m::mock()
            ->shouldReceive('findEntityForCategory')
            ->with(1, 'ABC123')
            ->andReturn(false)
            ->getMock()
        );
        $form = $this->createMockForm('Scanning');

        $form->shouldReceive('setData')
            ->with($post)
            ->andReturn($form)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('setMessages')
            ->with(
                [
                    'details' => [
                        'entityIdentifier' => ['scanning.error.entity.1']
                    ]
                ]
            );

        $this->sut->indexAction();
    }

    public function testIndexActionWithValidPostMatchingEntityAndStandardDescription()
    {
        $this->mockController('\Admin\Controller\ScanningController');

        $post = [
            'details' => [
                'category' => 1,
                'subCategory' => 2,
                'description' => 3,
                'entityIdentifier' => 'ABC123'
            ]
        ];

        $this->setPost($post);

        $this->setService(
            'Processing\Entity',
            m::mock()
            ->shouldReceive('findEntityForCategory')
            ->with(1, 'ABC123')
            ->andReturn(true) // @NOTE: when this story is developed further, this test will need updating
            ->getMock()
        );

        $this->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('scanning.message.success')
            ->getMock()
        );

        $form = $this->createMockForm('Scanning');

        $form->shouldReceive('setData')
            ->with($post)
            ->andReturn($form)
            ->shouldReceive('setData')
            ->with(
                [
                    'details' => [
                        'category' => 1,
                        'entityIdentifier' => 'ABC123'
                    ]
                ]
            )
            ->andReturn($form)
            ->shouldReceive('isValid')
            ->andReturn(true);

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->otherDescription');

        $this->sut->indexAction();
    }
}
