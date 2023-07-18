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

    /** @var CommonNoOfPermitsMapper */
    private $commonNoOfPermitsMapper;

    /**
     * Create service instance
     *
     * @param CommonNoOfPermitsMapper $commonNoOfPermitsMapper
     *
     * @return NoOfPermits
     */
    public function __construct(CommonNoOfPermitsMapper $commonNoOfPermitsMapper)
    {
        $this->commonNoOfPermitsMapper = $commonNoOfPermitsMapper;
    }

    /**
     * @param array $data
     * @param mixed $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
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
