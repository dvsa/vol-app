<?php

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Submit;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;

class SubmitTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    protected function createBlankDto()
    {
        return new Submit();
    }

    protected function getOptionalDtoFields()
    {
        return [
            'version',
            'nextStatus'
        ];
    }

    protected function getValidFieldValues()
    {
        return [
            'nextStatus' => [
                'tmap_st_incomplete',
                'tmap_st_awaiting_signature',
                'tmap_st_tm_signed',
                'tmap_st_operator_signed',
                'tmap_st_postal_application',
                'tmap_st_received',
                'tmap_st_details_submitted',
                'tmap_st_details_checked',
                'tmap_st_operator_approved',
            ],
            'id' => ['5', '3']
        ];
    }

    protected function getInvalidFieldValues()
    {
        return [
            'id' => ['unexpected' => 'string'],
            'nextStatus' => ['unexpected' => 'string']
        ];
    }


    protected function getFilterTransformations()
    {

        return [
            'nextStatus' => [['tmap_st_operator_approved ', 'tmap_st_operator_approved']],
            'id' => [[8, '8']]
        ];
    }
}
