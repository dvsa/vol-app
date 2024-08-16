<?php

namespace Olcs\Service\Data;

use Dvsa\Olcs\Transfer\Query\Reason\ReasonList as ReasonListDto;

/**
 * Class Submission Legislation
 *
 * @package Olcs\Service\Data
 */
class SubmissionLegislation extends AbstractPublicInquiryData
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

    /**
     * Format data for drop down. Note data-in-office-revokation flag used to set attribute against the option. JS
     * then used to filter out.
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[] =
                [
                    'label' => $datum['description'],
                    'value' => $datum['id'],
                    'attributes' => [
                        'data-in-office-revokation' => $datum['isProposeToRevoke']
                    ]
                ];
        }

        return $optionData;
    }
}
