<?php

namespace Olcs\Service\Data;

/**
 * Class PublicInquiryReason
 * @package Olcs\Service\Data
 */
class SubmissionLegislation extends AbstractPublicInquiryData
{
    /**
     * @var string
     */
    protected $serviceName = 'Reason';

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
