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
            'DataServiceManager',
            m::mock()
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\Category')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDescriptionFromId')
                ->with(1)
                ->andReturn('Category description')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\SubCategory')
            ->andReturn(
                m::mock()
                ->shouldReceive('setCategory')
                ->with(1)
                ->shouldReceive('getDescriptionFromId')
                ->with(2)
                ->andReturn('Subcategory description')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\SubCategoryDescription')
            ->andReturn(
                m::mock()
                ->shouldReceive('setSubCategory')
                ->with(2)
                ->shouldReceive('getDescriptionFromId')
                ->with(3)
                ->andReturn('A description')
                ->getMock()
            )
            ->getMock()
        );

        $entity = [
            'id' => 123,
            'licNo' => 'L1234B'
        ];

        $this->setService(
            'Processing\Entity',
            m::mock()
            ->shouldReceive('findEntityForCategory')
            ->with(1, 'ABC123')
            ->andReturn($entity)
            ->shouldReceive('findEntityNameForCategory')
            ->with(1)
            ->andReturn('Licence')
            ->shouldReceive('extractRelationsForCategory')
            ->with(1, $entity)
            ->andReturn(['foo' => 'bar'])
            ->getMock()
        );

        $this->mockEntity('Scan', 'save')
            ->with(
                [
                    'category' => 1,
                    'subCategory' => 2,
                    'description' => 'A description',
                    'foo' => 'bar'
                ]
            )
            ->andReturn(['id' => 456]);

        $this->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('scanning.message.success')
            ->getMock()
        );

        $values = [
            'DOC_CATEGORY_ID_SCAN'        => 1,
            'DOC_CATEGORY_NAME_SCAN'      => 'Category description',
            'LICENCE_NUMBER_SCAN'         => 'L1234B',
            'LICENCE_NUMBER_REPEAT_SCAN'  => 'L1234B',
            'ENTITY_ID_TYPE_SCAN'         => 'Licence',
            'ENTITY_ID_SCAN'              => 123,
            'ENTITY_ID_REPEAT_SCAN'       => 123,
            'DOC_SUBCATEGORY_ID_SCAN'     => 2,
            'DOC_SUBCATEGORY_NAME_SCAN'   => 'Subcategory description',
            'DOC_DESCRIPTION_ID_SCAN'     => 456,
            'DOC_DESCRIPTION_NAME_SCAN'   => 'A description'
        ];

        $this->setService(
            'Helper\DocumentGeneration',
            m::mock()
            ->shouldReceive('generateFromTemplate')
            ->with('Scanning_SeparatorSheet', [], $values)
            ->andReturn('content')
            ->shouldReceive('uploadGeneratedContent')
            ->with('content', 'documents', 'Scanning Separator Sheet')
            ->andReturn('file')
            ->getMock()
        );

        $this->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->with('file', 'Scanning Separator Sheet')
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

        $this->setService(
            'DataServiceManager',
            m::mock()
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\Category')
            ->andReturn(
                m::mock()
                ->shouldReceive('getDescriptionFromId')
                ->with(1)
                ->andReturn('Category description')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\SubCategory')
            ->andReturn(
                m::mock()
                ->shouldReceive('setCategory')
                ->with(1)
                ->shouldReceive('getDescriptionFromId')
                ->with(2)
                ->andReturn('Subcategory description')
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\SubCategoryDescription')
            ->andReturn(
                m::mock()
                ->shouldReceive('setSubCategory')
                ->with(2)
                ->getMock()
            )
            ->getMock()
        );

        $entity = [
            'id' => 123,
            'licNo' => 'L1234B'
        ];

        $this->setService(
            'Processing\Entity',
            m::mock()
            ->shouldReceive('findEntityForCategory')
            ->with(1, 'ABC123')
            ->andReturn($entity)
            ->shouldReceive('findEntityNameForCategory')
            ->with(1)
            ->andReturn('Licence')
            ->shouldReceive('extractRelationsForCategory')
            ->with(1, $entity)
            ->andReturn(['foo' => 'bar'])
            ->getMock()
        );

        $this->mockEntity('Scan', 'save')
            ->with(
                [
                    'category' => 1,
                    'subCategory' => 2,
                    'description' => 'custom description',
                    'foo' => 'bar'
                ]
            )
            ->andReturn(['id' => 456]);

        $this->setService(
            'Helper\FlashMessenger',
            m::mock()
            ->shouldReceive('addSuccessMessage')
            ->with('scanning.message.success')
            ->getMock()
        );

        $values = [
            'DOC_CATEGORY_ID_SCAN'        => 1,
            'DOC_CATEGORY_NAME_SCAN'      => 'Category description',
            'LICENCE_NUMBER_SCAN'         => 'L1234B',
            'LICENCE_NUMBER_REPEAT_SCAN'  => 'L1234B',
            'ENTITY_ID_TYPE_SCAN'         => 'Licence',
            'ENTITY_ID_SCAN'              => 123,
            'ENTITY_ID_REPEAT_SCAN'       => 123,
            'DOC_SUBCATEGORY_ID_SCAN'     => 2,
            'DOC_SUBCATEGORY_NAME_SCAN'   => 'Subcategory description',
            'DOC_DESCRIPTION_ID_SCAN'     => 456,
            'DOC_DESCRIPTION_NAME_SCAN'   => 'custom description'
        ];

        $this->setService(
            'Helper\DocumentGeneration',
            m::mock()
            ->shouldReceive('generateFromTemplate')
            ->with('Scanning_SeparatorSheet', [], $values)
            ->andReturn('content')
            ->shouldReceive('uploadGeneratedContent')
            ->with('content', 'documents', 'Scanning Separator Sheet')
            ->andReturn('file')
            ->getMock()
        );

        $this->setService(
            'PrintScheduler',
            m::mock()
            ->shouldReceive('enqueueFile')
            ->with('file', 'Scanning Separator Sheet')
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

        $this->sut->indexAction();
    }
}
