<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Data\Mapper\Permits\NoOfPermits as CommonNoOfPermitsMapper;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Controller\Config\DataSource\IrhpFeePerPermit as IrhpFeePerPermitDataSource;
use Permits\Controller\Config\DataSource\IrhpMaxStockPermits as IrhpMaxStockPermitsDataSource;
use Permits\Data\Mapper\NoOfPermits;
use Laminas\Form\Form;

/**
 * NoOfPermitsTest
 */
class NoOfPermitsTest extends TestCase
{
    public function testMapForFormOptions(): void
    {
        $data = [
            'inputDataKey1' => 'inputDataValue1',
            'inputDataKey2' => 'inputDataValue2',
        ];

        $returnedData = [
            'returnedDataKey1' => 'returnedDataValue1',
            'returnedDataKey2' => 'returnedDataValue2',
        ];

        $form = m::mock(Form::class);

        $commonNoOfPermitsMapper = m::mock(CommonNoOfPermitsMapper::class);
        $commonNoOfPermitsMapper->shouldReceive('mapForFormOptions')
            ->with(
                $data,
                $form,
                IrhpApplicationDataSource::DATA_KEY,
                IrhpMaxStockPermitsDataSource::DATA_KEY,
                IrhpFeePerPermitDataSource::DATA_KEY
            )
            ->once()
            ->andReturn($returnedData);

        $noOfPermits = new NoOfPermits($commonNoOfPermitsMapper);

        $this->assertEquals(
            $returnedData,
            $noOfPermits->mapForFormOptions($data, $form)
        );
    }
}
