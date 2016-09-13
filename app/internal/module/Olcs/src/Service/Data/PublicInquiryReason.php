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
    protected $listDto = ReasonListDto::class;
    protected $sort = 'sectionCode';
    protected $order = 'ASC';
}
