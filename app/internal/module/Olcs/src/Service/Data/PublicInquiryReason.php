<?php

namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Reason\ReasonList as ReasonListDto;
use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\FactoryInterface;
use Common\Service\Data\LicenceServiceTrait;

/**
 * Class PublicInquiryReason
 *
 * @package Olcs\Service\Data
 */
class PublicInquiryReason extends AbstractPublicInquiryData implements ListDataInterface, FactoryInterface
{
    protected $listDto = ReasonListDto::class;
    protected $sort = 'sectionCode';
    protected $order = 'ASC';

    /**
     * @var string
     */
    protected $serviceName = 'Reason';
}
