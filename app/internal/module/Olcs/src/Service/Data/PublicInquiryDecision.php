<?php

namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Decision\DecisionList as DecisionListDto;

/**
 * Class Public Inquiry Decision
 *
 * @package Olcs\Service\Data
 */
class PublicInquiryDecision extends AbstractPublicInquiryData
{
    protected $listDto = DecisionListDto::class;
    protected $sort = 'sectionCode';
    protected $order = 'ASC';
}
