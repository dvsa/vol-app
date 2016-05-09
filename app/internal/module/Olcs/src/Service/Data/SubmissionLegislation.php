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
    protected $sort = 'sectionCode';
    protected $order = 'ASC';

    /**
     * @var string
     */
    protected $serviceName = 'Reason';

    /**
     * Format data for drop down. Note data-in-office-revokation flag used to set attribute against the option. JS
     * then used to filter out.
     * 
     * @param array $data
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
