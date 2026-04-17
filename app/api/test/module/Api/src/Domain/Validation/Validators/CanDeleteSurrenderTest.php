<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanDeleteSurrender;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Mockery as m;

class CanDeleteSurrenderTest extends AbstractValidatorsTestCase
{
    /**
     * @var CanDeleteSurrender
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanDeleteSurrender();
        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $surrender, mixed $expected): void
    {
        $statusEntity = m::mock(RefData::class);

        $surrenderEntity = m::mock(Surrender::class);
        $surrenderEntity->shouldReceive('getStatus')->andReturn($statusEntity);

        $LicenceEntity = m::mock(Licence::class);

        $SurrenderRepo = $this->mockRepo('Surrender');
        $SurrenderRepo->shouldReceive('fetchOneByLicenceId')->with(1)->andReturn($surrenderEntity);

        $statusEntity->shouldReceive('getId')->andReturn($surrender['status']);

        $surrenderEntity->shouldReceive('getCreatedOn')->andReturn($surrender['createdOn']);
        $surrenderEntity->shouldReceive('getLastModifiedOn')->andReturn($surrender['lastModifiedOn']);

        $LicenceRepo = $this->mockRepo('Licence');

        if ($this->dataName() !== 'not_withdrawn_or_expired') {
            $this->setIsGranted(Permission::INTERNAL_USER, false);
            $this->auth->shouldReceive('getIdentity')->andReturn(null);
            $this->setIsValid('isOwner', [$LicenceEntity], true);
            $LicenceRepo->shouldReceive('get')->with('Licence');
            $LicenceRepo->shouldReceive('fetchById')->once()->andReturn($LicenceEntity);
        }

        $this->assertSame($expected, $this->sut->isValid(1));
    }

    public static function provider(): array
    {
        return [
            'is_withdrawn' => [
                'surrender' => [
                    'status' => RefData::SURRENDER_STATUS_WITHDRAWN,
                    'createdOn' => new \DateTime(),
                    'lastModifiedOn' => new \DateTime()
                ],
                'expected' => true
            ],
            'has_expired_created_on' => [
                'surrender' => [
                    'status' => RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE,
                    'createdOn' => date_create('3 days ago'),
                    'lastModifiedOn' => null
                ],
                'expected' => true
            ],
            'has_expired_last_modified' => [
                'surrender' => [
                    'status' => RefData::SURRENDER_STATUS_COMM_LIC_DOCS_COMPLETE,
                    'createdOn' => date_create('5 days ago'),
                    'lastModifiedOn' => date_create('4 days ago')
                ],
                'expected' => true
            ],
            'not_withdrawn_or_expired' => [
                'surrender' => [
                    'status' => RefData::SURRENDER_STATUS_DETAILS_CONFIRMED,
                    'createdOn' => new \DateTime(),
                    'lastModifiedOn' => new \DateTime()
                ],
                'expected' => false
            ],
        ];
    }
}
