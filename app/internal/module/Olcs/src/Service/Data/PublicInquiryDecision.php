<?php

namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Decision\DecisionList as DecisionListDto;
use Common\Service\Data\ListDataInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * Class PublicInquiryDecision
 * @package Olcs\Service\Data
 */
class PublicInquiryDecision extends AbstractPublicInquiryData implements ListDataInterface, FactoryInterface
{
    protected $listDto = DecisionListDto::class;
    protected $sort = 'sectionCode';
    protected $order = 'ASC';

    /**
     * @var string
     */
    protected $serviceName = 'Decision';
}
