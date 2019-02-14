<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\Permits\NoOfPermits as CommonNoOfPermitsMapper;
use Common\Service\Helper\TranslationHelperService;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Controller\Config\DataSource\IrhpFeePerPermit as IrhpFeePerPermitDataSource;
use Permits\Controller\Config\DataSource\IrhpMaxStockPermits as IrhpMaxStockPermitsDataSource;

/**
 * No of permits mapper
 */
class NoOfPermits
{
    /**
     * @param array $data
     * @param $form
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form, TranslationHelperService $translator)
    {
        return CommonNoOfPermitsMapper::mapForFormOptions(
            $data,
            $form,
            $translator,
            IrhpApplicationDataSource::DATA_KEY,
            IrhpMaxStockPermitsDataSource::DATA_KEY,
            IrhpFeePerPermitDataSource::DATA_KEY
        );
    }
}
