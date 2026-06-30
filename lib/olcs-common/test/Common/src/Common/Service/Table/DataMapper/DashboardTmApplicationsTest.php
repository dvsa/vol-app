<?php

/**
 * DashboardTmApplicationsTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace CommonTest\Service\Table\DataMapper;

use Common\Service\Table\DataMapper\DashboardTmApplications;

/**
 * DashboardTmApplicationsTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardTmApplicationsTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new DashboardTmApplications();
    }

    public function testMap(): void
    {
        $data = [
            [
                'id' => 776,
                'tmApplicationStatus' => [
                    'id' => 'status',
                    'description' => 'Status description'
                ],
                'application' => [
                    'id' => 34,
                    'licence' => [
                        'licNo' => 'LIC001',
                        'organisation' => [
                            'name' => 'operator name'
                        ],
                    ],
                    'isVariation' => 1
                ]
            ]
        ];

        $formattedData = [
            [
                'transportManagerApplicationId' => $data[0]['id'],
                'transportManagerApplicationStatus' =>
                    $data[0]['tmApplicationStatus'],
                'licNo' => $data[0]['application']['licence']['licNo'],
                'applicationId' => $data[0]['application']['id'],
                'isVariation' => $data[0]['application']['isVariation'],
                'operatorName' => $data[0]['application']['licence']['organisation']['name']
            ]
        ];

        $this->assertEquals($formattedData, $this->sut->map($data));
    }
}
