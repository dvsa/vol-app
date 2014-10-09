<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

class EbsrPack extends AbstractData
{
    protected $serviceName = 'ebsr\pack';

    /**
     * Temp stub method
     * @return array
     */
    public function fetchPackList()
    {
        return [
            [
                'status' => 'Processing',
                'filename' => 'PB000678.zip',
                'submitted' => '2014-10-07'
            ]
        ];
    }
}