<?php

declare(strict_types=1);

/**
 * UpdatePartner Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\Partner;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Command\User\UpdatePartner as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\UpdatePartner;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdatePartner Test
 */
class UpdatePartnerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdatePartner();
        $this->mockRepo('Partner', Partner::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'description' => 'updated description',
            'address' => [
                'addressLine1' => 'updated a1',
                'addressLine2' => 'updated a2',
                'addressLine3' => 'updated a3',
                'addressLine4' => 'updated a4',
                'town' => 'updated town',
                'postcode' => 'LS1 2AB',
                'countryCode' => m::mock(Country::class),
            ],
        ];

        $command = Cmd::create($data);

        /** @var ContactDetailsEntity $contactDetails */
        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->setId(111);
        $contactDetails->shouldReceive('update')
            ->once()
            ->with($data)
            ->andReturnSelf();

        $this->repoMap['Partner']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($contactDetails);

        $this->repoMap['ContactDetails']->shouldReceive('populateRefDataReference')
            ->once()
            ->with($data)
            ->andReturn($data);

        /** @var ContactDetailsEntity $savedPartner */
        $savedPartner = null;

        $this->repoMap['Partner']->shouldReceive('save')
            ->once()
            ->with(m::type(ContactDetailsEntity::class))
            ->andReturnUsing(
                function (ContactDetailsEntity $contactDetails) use (&$savedPartner) {
                    $savedPartner = $contactDetails;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'partner' => 111,
            ],
            'messages' => [
                'Partner updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($contactDetails, $savedPartner);
    }
}
