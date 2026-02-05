<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Dvsa\Olcs\Api\Service\Publication\Context\Application\ConditionUndertaking as ConditionUndertakingContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ConditionUndertakingTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ConditionUndertakingTest extends MockeryTestCase
{
    /**
     * @param $action
     * @param $expectedActionString
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTestProvider')]
    public function testProvideWithOperatingCentre(mixed $action, mixed $expectedActionString): void
    {
        $conditionTypeDescription = 'condition type description';
        $notes = 'notes';
        $ocAddress = 'oc address';

        $addressEntityMock = m::mock(AddressEntity::class);
        $operatingCentre = m::mock(OperatingCentreEntity::class);
        $operatingCentre->shouldReceive('getAddress')->andReturn($addressEntityMock);

        $conditionUndertaking = m::mock(ConditionUndertakingEntity::class);
        $conditionUndertaking->shouldReceive('getNotes')->andReturn($notes);
        $conditionUndertaking->shouldReceive('getOperatingCentre')->andReturn($operatingCentre);
        $conditionUndertaking->shouldReceive('getConditionType->getDescription')->andReturn($conditionTypeDescription);
        $conditionUndertaking->shouldReceive('getAction')->andReturn($action);

        $conditionUndertakings = new ArrayCollection([$conditionUndertaking]);

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $mockAddressFormatter->shouldReceive('format')->andReturn($ocAddress);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getApplication->getConditionUndertakings')->andReturn($conditionUndertakings);

        $sut = new ConditionUndertakingContext(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $sut->setAddressFormatter($mockAddressFormatter);

        $output = [
            'conditionUndertaking' => [
                0 => sprintf($expectedActionString, $conditionTypeDescription, $notes) .
                    ' Attached to Operating Centre: ' . $ocAddress
            ]
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }

    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the application condition undertakings filter')]
    public function testProvideUpdateNoOperatingCentre(): void
    {
        $sut = new ConditionUndertakingContext(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $conditionTypeDescription = 'condition type description';
        $notes = 'notes';
        $action = 'U';
        $expectedActionString = $sut::COND_UPDATE;
        $operatingCentre = null;

        $conditionUndertaking = m::mock(ConditionUndertakingEntity::class);
        $conditionUndertaking->shouldReceive('getNotes')->andReturn($notes);
        $conditionUndertaking->shouldReceive('getOperatingCentre')->andReturn($operatingCentre);
        $conditionUndertaking->shouldReceive('getConditionType->getDescription')->andReturn($conditionTypeDescription);
        $conditionUndertaking->shouldReceive('getAction')->andReturn($action);

        $conditionUndertakings = new ArrayCollection([$conditionUndertaking]);

        $publication = m::mock(PublicationLink::class);
        $publication->shouldReceive('getApplication->getConditionUndertakings')->andReturn($conditionUndertakings);

        $output = [
            'conditionUndertaking' => [
                0 => sprintf($expectedActionString, $conditionTypeDescription, $notes) .
                    ' Attached to Licence. ' . sprintf($sut::COND_AMENDED, $notes)
            ]
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publication, new \ArrayObject()));
    }

    /**
     * Provider for testProvide
     *
     * @return array
     */
    public static function provideTestProvider(): array
    {
        $sut = new ConditionUndertakingContext(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        return [
            ['A', $sut::COND_NEW],
            ['D', $sut::COND_REMOVE],
            ['ZZZ', $sut::COND_NEW]
        ];
    }
}
