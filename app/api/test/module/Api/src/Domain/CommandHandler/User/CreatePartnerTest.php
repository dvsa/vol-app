<?php

declare(strict_types=1);

/**
 * CreatePartner Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Repository\ContactDetails;
use Dvsa\Olcs\Api\Domain\Repository\Partner;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Command\User\CreatePartner as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\CreatePartner;
use Mockery as m;

/**
 * CreatePartner Test
 */
class CreatePartnerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePartner();
        $this->mockRepo('Partner', Partner::class);
        $this->mockRepo('ContactDetails', ContactDetails::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_PARTNER
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'description' => 'description',
            'address' => [
                'addressLine1' => 'a1',
                'addressLine2' => 'a2',
                'addressLine3' => 'a3',
                'addressLine4' => 'a4',
                'town' => 'town',
                'postcode' => 'LS1 2AB',
                'countryCode' => m::mock(Country::class),
            ],
        ];

        $command = Cmd::create($data);

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
                    $contactDetails->setId(111);
                    $savedPartner = $contactDetails;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'partner' => 111,
            ],
            'messages' => [
                'Partner created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(ContactDetailsEntity::class, $savedPartner);
        $this->assertEquals($data['description'], $savedPartner->getDescription());
        $this->assertEquals($data['address']['addressLine1'], $savedPartner->getAddress()->getAddressLine1());
        $this->assertEquals($data['address']['addressLine2'], $savedPartner->getAddress()->getAddressLine2());
        $this->assertEquals($data['address']['addressLine3'], $savedPartner->getAddress()->getAddressLine3());
        $this->assertEquals($data['address']['addressLine4'], $savedPartner->getAddress()->getAddressLine4());
        $this->assertEquals($data['address']['town'], $savedPartner->getAddress()->getTown());
        $this->assertEquals($data['address']['postcode'], $savedPartner->getAddress()->getPostcode());
    }
}
