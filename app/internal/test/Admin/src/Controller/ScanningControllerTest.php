<?php

/**
 * Scanning Controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace AdminTest\Controller;

use OlcsTest\Bootstrap;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Scanning Controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ScanningControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    public function setUp()
    {
        $this->markTestSkipped();
    }

    /**
     * Required by trait
     *
     * @todo These tests require a real service manager to run, as they are not mocking all dependencies,
     * these tests should be addresses
     */
    protected function getServiceManager()
    {
        return Bootstrap::getRealServiceManager();
    }

    protected function setupCreateSeparatorSheet(
        $categoryId,
        $subCategoryId,
        $entityIdentifier,
        $description,
        $descriptionId,
        $isOk
    ) {
        $this->sut->shouldReceive('handleCommand')
            ->with(m::type(\Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet::class))
            ->once()
            ->andReturnUsing(
                function (\Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet $command) use (
                    $categoryId,
                    $subCategoryId,
                    $entityIdentifier,
                    $description,
                    $descriptionId,
                    $isOk
                ) {
                    $this->assertSame($categoryId, $command->getCategoryId());
                    $this->assertSame($subCategoryId, $command->getSubCategoryId());
                    $this->assertSame($entityIdentifier, $command->getEntityIdentifier());
                    $this->assertSame($descriptionId, $command->getDescriptionId());
                    $this->assertSame($description, $command->getDescription());

                    return m::mock()->shouldReceive('isOk')->with()->once()->andReturn($isOk)->getMock();
                }
            );
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
            ->andReturn($form)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('cancel')
                ->getMock()
            );

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
            ->andReturn(false)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('cancel')
                ->getMock()
            );

        $this->sut->indexAction();
    }

    protected function createFormWithData($data, $mockFormHelper)
    {
        $mockForm = m::mock();

        $mockFormHelper->shouldReceive('createForm')->with('Scanning')->once()->andReturn($mockForm);
        $mockForm->shouldReceive('setData')->with($data)->once()->andReturnSelf();

        $mockFormActions = m::mock();
        $mockForm->shouldReceive('get')->with('form-actions')->once()->andReturn($mockFormActions);

        $mockFormActions->shouldReceive('remove')->with('cancel')->once();

        if (isset($data['details']['description'])) {
            $mockFormHelper->shouldReceive('remove')->with($mockForm, 'details->otherDescription')->once();
        }

        return $mockForm;
    }

    public function testIndexActionWithValidPostNoMatchingEntity()
    {
        $this->mockController('\Admin\Controller\ScanningController');

        $post = [
            'details' => [
                'category' => 1,
                'subCategory' => 2,
                'entityIdentifier' => 'ABC123',
                'otherDescription' => 'XX',
            ]
        ];

        $this->setPost($post);

        $mockFormHelper = m::mock();
        $this->setService('Helper\Form', $mockFormHelper);

        $form = $this->createFormWithData($post, $mockFormHelper);

        $this->setupCreateSeparatorSheet(1, 2, 'ABC123', 'XX', null, false);

        $form->shouldReceive('isValid')
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

        $mockFormHelper = m::mock();
        $this->setService('Helper\Form', $mockFormHelper);

        $form = $this->createFormWithData($post, $mockFormHelper);
        $form->shouldReceive('isValid')->andReturn(true);

        $this->setupCreateSeparatorSheet(1, 2, 'ABC123', null, 3, true);

        $this->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('scanning.message.success')
            ->getMock()
        );

        $this->createFormWithData(
            [
                'details' => [
                    'category' => 1,
                    'entityIdentifier' => 'ABC123',
                ]
            ],
            $mockFormHelper
        );

        $this->sut->indexAction();
    }

    public function testIndexActionWithValidPostMatchingEntityAndCustomDescription()
    {
        $this->mockController('\Admin\Controller\ScanningController');

        $post = [
            'details' => [
                'category' => 1,
                'subCategory' => 2,
                'otherDescription' => 'custom description',
                'entityIdentifier' => 'ABC123'
            ]
        ];

        $this->setPost($post);
        $mockFormHelper = m::mock();
        $this->setService('Helper\Form', $mockFormHelper);

        $form = $this->createFormWithData($post, $mockFormHelper);
        $form->shouldReceive('isValid')->andReturn(true);

        $this->setupCreateSeparatorSheet(1, 2, 'ABC123', 'custom description', null, true);

        $this->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('scanning.message.success')
            ->getMock()
        );

        $this->createFormWithData(
            [
                'details' => [
                    'category' => 1,
                    'entityIdentifier' => 'ABC123',
                ]
            ],
            $mockFormHelper
        );

        $this->sut->indexAction();
    }
}
