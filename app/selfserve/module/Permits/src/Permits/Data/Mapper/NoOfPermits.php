<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Data\Mapper\Permits\NoOfPermits as CommonNoOfPermitsMapper;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Controller\Config\DataSource\IrhpFeePerPermit as IrhpFeePerPermitDataSource;
use Permits\Controller\Config\DataSource\IrhpMaxStockPermits as IrhpMaxStockPermitsDataSource;

/**
 * No of permits mapper
 */
class NoOfPermits implements MapperInterface
{
    use MapFromResultTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermits
     */
    public function __construct(private CommonNoOfPermitsMapper $commonNoOfPermitsMapper)
    {
    }

    /**
     *
     * @return array
     */
    public function mapForFormOptions(array $data, mixed $form)
    {
        return $this->commonNoOfPermitsMapper->mapForFormOptions(
            $data,
            $form,
            IrhpApplicationDataSource::DATA_KEY,
            IrhpMaxStockPermitsDataSource::DATA_KEY,
            IrhpFeePerPermitDataSource::DATA_KEY
        );
    }
}
