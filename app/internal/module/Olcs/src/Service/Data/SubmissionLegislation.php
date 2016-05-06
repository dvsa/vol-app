<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\FactoryInterface;
use Common\Service\Data\LicenceServiceTrait;
use Dvsa\Olcs\Transfer\Query\Reason\ReasonList as ReasonListDto;

/**
 * Class PublicInquiryReason
 * @package Olcs\Service\Data
 */
class SubmissionLegislation extends AbstractPublicInquiryData implements ListDataInterface, FactoryInterface
{
    protected $listDto = ReasonListDto::class;
    protected $sort = 'id';
    protected $order = 'ASC';

    /**
     * @var string
     */
    protected $serviceName = 'Reason';
}
