<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;

/**
 * Class EbsrPack
 * @package Olcs\Service\Data
 */
class EbsrPack extends AbstractData
{
    /**
     * @var string
     */
    protected $serviceName = 'ebsr\pack';

    /**
     * Temp stub method
     * @return array
     */
    public function fetchPackList()
    {
        return [
            [
                'status' => 'Recieved',
                'filename' => 'PB000679.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Processing',
                'filename' => 'PB000678.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Distributing',
                'filename' => 'PB000677.zip',
                'submitted' => '2014-10-07'
            ],
            [
                'status' => 'Complete',
                'filename' => 'PB000676.zip',
                'submitted' => '2014-10-07'
            ]

        ];
    }
}
