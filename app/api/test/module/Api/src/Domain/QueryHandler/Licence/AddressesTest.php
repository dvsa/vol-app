<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Addresses;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Addresses
 */
class AddressesTest extends QueryHandlerTestCase
{
    public const CONTACT_DETAILS_ID = 8888;

    /** @var  Addresses */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Addresses();

        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('PhoneContact', Repository\PhoneContact::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        /** @var Entity\Licence\Licence $licence */
        $mockLicenceEntity = m::mock(Entity\Licence\Licence::class);
        $mockLicenceEntity
            ->shouldReceive('serialize')
            ->with(m::type('array'))
            ->once()
            ->andReturn(
                [
                    'correspondenceCd' => [
                        'phoneContacts' => 'EXPECTED',
                    ],
                    'LicenceData' => 'EXPECTED',
                ]
            );

        $query = TransferQry\Licence\Addresses::create([]);

        $this->repoMap['Licence']->shouldReceive('fetchWithAddressesUsingId')
            ->with($query)
            ->andReturn($mockLicenceEntity);

        //  call & check
        $result = $this->sut->handleQuery($query);

        static::assertInstanceOf(Result::class, $result);

        $expected = [
            'correspondenceCd' => [
                'phoneContacts' => 'EXPECTED',
            ],
            'LicenceData' => 'EXPECTED',
        ];

        static::assertEquals($expected, $result->serialize());
    }
}
