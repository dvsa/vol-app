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
    /**
     * @var string
     */
    protected $listDto = DecisionListDto::class;

    /**
     * @var string
     */
    protected $sort = 'sectionCode, description';

    /**
     * @var string
     */
    protected $order = 'ASC, ASC';
}
