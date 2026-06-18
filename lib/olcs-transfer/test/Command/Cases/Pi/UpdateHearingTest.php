<?php

namespace Dvsa\OlcsTest\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateHearing;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class UpdateHearingTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new UpdateHearing();
    }

    protected function getOptionalDtoFields()
    {
        return [
            'venue',
            'venueOther',
            'cancelledDate',
            'cancelledReason',
            'adjournedDate',
            'adjournedReason',
            'pubType',
            'details'
        ];
    }

    protected function getValidFieldValues()
    {
        return [
            'venue' => ['1'],
            'venueOther' => ['venue name'],
            'isFullDay' => ['Y'],
            'presidingTc' => ['1'],
            'presidedByRole' => ['tc_r_dhtru'],
            'witnesses' => ['1'],
            'drivers' => ['1'],
            'hearingDate' => ['2018-01-01T00:00:00+0000'],
            'isCancelled' => ['Y'],
            'isAdjourned' => ['Y'],
            'cancelledDate' => ['2018-01-01'],
            'cancelledReason' => ['cancelled reason'],
            'adjournedDate' => ['2018-01-01T00:00:00+0000'],
            'adjournedReason' => ['adjourned reason'],
            'trafficAreas' => [['B','C']],
            'pubType' => ['All'],
            'details' => ['test details'],
            'publish' => ['Y']
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'venue' => [['not a number']],
            'venueOther' => [['invalid' => 'array']],
            'isFullDay' => ['not Y oor N'],
            'presidingTc' => [['not a number']],
            'presidedByRole' => ['unexpected string'],
            'witnesses' => [['not a number']],
            'drivers' => [['not a number']],
            'hearingDate' => [['unexpected string']],
            'isCancelled' => ['not Y oor N'],
            'isAdjourned' => ['not Y oor N'],
            'cancelledDate' => [['unexpected string']],
            'cancelledReason' => [['invalid' => 'array']],
            'adjournedDate' => [['unexpected string']],
            'adjournedReason' => [['invalid' => 'array']],
            'trafficAreas' => [['unexpected string']],
            'pubType' => ['unexpected string'],
            'details' => [['invalid' => 'array']],
            'publish' => ['not Y oor N']
        ];
    }

    protected function getFilterTransformations()
    {
        return [
            'id' => [
                [' 222', '222']
            ]
        ];
    }
}
