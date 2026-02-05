<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Address as AddressRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Mockery as m;

/**
 * Although this trait contains only private methods
 * we are testing them
 * to ensure changes in the trait are highlighted early
 * as potential impact could be high
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DeleteContactDetailsAndAddressTraitTest extends AbstractCommandHandlerTestCase
{
    /** @var DeleteContactDetailsAndAddressTraitStub $sut */
    protected $sut;
    public function setUp(): void
    {
        $this->sut = new DeleteContactDetailsAndAddressTraitStub();
        $this->mockRepo('Address', AddressRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);

        parent::setUp();
    }

    public function testInjectReposWhenNotSet(): void
    {
        $this->sut = new DeleteContactDetailsAndAddressTraitStub();
        $this->sut->injectReposStub();
        $this->assertEquals(['ContactDetails', 'Address'], $this->sut->getExtraRepos());
    }

    public function testInjectRepos(): void
    {
        $this->sut = new DeleteContactDetailsAndAddressTraitStub();
        $this->sut->setExtraRepos(['ContactDetails', 'Address']);
        $this->sut->injectReposStub();
        $this->assertEquals(['ContactDetails', 'Address'], $this->sut->getExtraRepos());
    }

    public function testMaybeDeleteContactDetailsAndAddressWithAddressNull(): void
    {
        $contactDetails = m::mock(ContactDetailsEntity::class);
        $contactDetails->shouldReceive('getAddress')->andReturn(null);
        $this->repoMap['ContactDetails']->shouldReceive('delete')->with($contactDetails);

        $this->sut->maybeDeleteContactDetailsAndAddressStub($contactDetails);
    }

    public function testMaybeDeleteContactDetailsAndAddressWithCdNull(): void
    {
        $contactDetails = null;
        $this->sut->maybeDeleteContactDetailsAndAddressStub($contactDetails);
    }

    public function testMaybeDeleteContactDetailsAndAddress(): void
    {
        $address = m::mock(AddressEntity::class);
        $this->repoMap['Address']->shouldReceive('delete')
                                 ->once()
                                 ->with($address);
        $this->repoMap['Address']->shouldReceive('refresh')
                                 ->once()
                                 ->with($address);
        $contactDetails = m::mock(ContactDetailsEntity::class);
        $contactDetails->shouldReceive('getAddress')->andReturn($address);
        $this->repoMap['ContactDetails']->shouldReceive('delete')->with($contactDetails);

        $this->sut->maybeDeleteContactDetailsAndAddressStub($contactDetails);
    }
}
