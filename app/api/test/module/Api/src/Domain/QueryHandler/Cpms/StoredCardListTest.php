<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cpms;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Service\CpmsHelperInterface as CpmsHelper;
use Dvsa\Olcs\Transfer\Query\Cpms\StoredCardList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * StoredCardListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class StoredCardListTest extends QueryHandlerTestCase
{
    protected $mockCpmsService;

    public function setUp(): void
    {
        $this->mockCpmsService = m::mock(CpmsHelper::class);

        $this->mockedSmServices = [
            'CpmsHelperService' => $this->mockCpmsService,
        ];

        $this->sut = new \Dvsa\Olcs\Api\Domain\QueryHandler\Cpms\StoredCardList();

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Qry::create(['isNi' => 'Y']);
        $data = [
            'items' => [
                [
                    "mask_pan" => "PAN1",
                    "card_scheme" => "SCHEME1",
                    "card_reference" => "REF1"
                ],
                [
                    "mask_pan" => "PAN2",
                    "card_scheme" => "SCHEME2",
                    "card_reference" => "REF2"
                ],
            ],
        ];

        $this->mockCpmsService
            ->shouldReceive('getListStoredCards')
            ->once()
            ->andReturn($data);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                [
                    "maskedPan" => "PAN1",
                    "cardScheme" => "SCHEME1",
                    "cardReference" => "REF1"
                ],
                [
                    "maskedPan" => "PAN2",
                    "cardScheme" => "SCHEME2",
                    "cardReference" => "REF2"
                ],
            ],
            'count' => 2,
        ];

        $this->assertEquals($expected, $result);
    }
}
