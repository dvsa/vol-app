<?php

namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Reason\ReasonList as ReasonListDto;

/**
 * Class Public Inquiry Reason
 *
 * @package Olcs\Service\Data
 */
class PublicInquiryReason extends AbstractPublicInquiryData
{
    /**
     * @var string
     */
    protected $listDto = ReasonListDto::class;

    /**
     * @var string
     */
    protected $sort = 'sectionCode';

    /**
     * @var string
     */
    protected $order = 'ASC';
}
