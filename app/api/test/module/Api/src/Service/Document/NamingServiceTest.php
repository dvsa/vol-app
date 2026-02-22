<?php

declare(strict_types=1);

/**
 * Naming Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Naming Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class NamingServiceTest extends MockeryTestCase
{
    /**
     * @var NamingService
     */
    protected $sut;

    public function setUp(): void
    {
        $config = [
            'document_share' => [
                'path' => 'documents/'
                    . '{Category}/{SubCategory}/{Date:Y}/{Date:m}/{Date:YmdHis}_{Context}_{Description}.{Extension}'
            ]
        ];

        $sm = m::mock(ServiceManager::class);

        $sm->shouldReceive('setService')
            ->andReturnUsing(
                function ($alias, $service) use ($sm) {
                    $sm->shouldReceive('get')->with($alias)->andReturn($service);
                    $sm->shouldReceive('has')->with($alias)->andReturn(true);
                    return $sm;
                }
            );

        $sm->setService('config', $config);

        $this->sut = new NamingService();
        $this->sut->__invoke($sm, NamingService::class);
    }

    public function testInvokeFail(): void
    {
        $this->expectException('\RuntimeException');

        $config = [];

        $sm = m::mock(ServiceManager::class);

        $sm->shouldReceive('setService')
            ->andReturnUsing(
                function ($alias, $service) use ($sm) {
                    $sm->shouldReceive('get')->with($alias)->andReturn($service);
                    $sm->shouldReceive('has')->with($alias)->andReturn(true);
                    return $sm;
                }
            );

        $sm->setService('config', $config);

        $this->sut = new NamingService();
        $this->sut->__invoke($sm, NamingService::class);
    }

    public function testGenerateName(): void
    {
        $date = new DateTime();

        /** @var Category $category */
        $category = m::mock(Category::class)->makePartial();
        $category->setDescription('Cat');

        /** @var SubCategory $subCategory */
        $subCategory = m::mock(SubCategory::class)->makePartial();
        $subCategory->setSubCategoryName('Sub Cat');

        $name = $this->sut->generateName('Some Desc', 'rtf', $category, $subCategory);

        $expected = sprintf(
            'documents/Cat/Sub_Cat/%s/%s/%s__Some_Desc.rtf',
            $date->format('Y'),
            $date->format('m'),
            $date->format('YmdHis')
        );

        $this->assertEquals($expected, $name);
    }

    public function testGenerateNameWithUnknown(): void
    {
        $date = new DateTime();

        $name = $this->sut->generateName('Some :/] Desc', 'rtf');

        $expected = sprintf(
            'documents/Unknown/Unknown/%s/%s/%s__Some___Desc.rtf',
            $date->format('Y'),
            $date->format('m'),
            $date->format('YmdHis')
        );

        $this->assertEquals($expected, $name);
    }

    public function testGenerateNameWithEntity(): void
    {
        $date = new DateTime();

        /** @var Category $category */
        $category = m::mock(Category::class)->makePartial();
        $category->setDescription('Cat');

        /** @var SubCategory $subCategory */
        $subCategory = m::mock(SubCategory::class)->makePartial();
        $subCategory->setSubCategoryName('Sub Cat');

        $entity = m::mock(ContextProviderInterface::class);
        $entity->shouldReceive('getContextValue')
            ->andReturn('12345');

        $name = $this->sut->generateName('[Some :Desc\/]', 'rtf', $category, $subCategory, $entity);

        $expected = sprintf(
            'documents/Cat/Sub_Cat/%s/%s/%s_12345_Some_Desc_.rtf',
            $date->format('Y'),
            $date->format('m'),
            $date->format('YmdHis')
        );

        $this->assertEquals($expected, $name);
    }
}
