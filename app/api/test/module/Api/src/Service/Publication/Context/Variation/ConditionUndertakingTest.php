<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Variation;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ConditionUndertakingTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ConditionUndertakingTest extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Context\Variation\ConditionUndertaking
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Context\Variation\ConditionUndertaking(
            m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class)
        );

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestProvideOnlyRecievedOrGrantSections')]
    public function testProvideOnlyRecievedOrGrantSections(mixed $publicationSectionId): void
    {
        $publicationLink = $this->getPublicationLink();
        $context = new \ArrayObject();

        $publicationLink->getPublicationSection()->setId($publicationSectionId);
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'A', 'NOTES')
        );

        $this->sut->provide($publicationLink, $context);

        if (
            $publicationSectionId === PublicationSection::VAR_GRANTED_SECTION ||
            $publicationSectionId === PublicationSection::VAR_NEW_SECTION
        ) {
            $this->assertNotEmpty($context);
        } else {
            $this->assertEmpty($context);
        }
    }

    public static function dataProviderTestProvideOnlyRecievedOrGrantSections(): array
    {
        return [
            [PublicationSection::VAR_GRANTED_SECTION],
            [PublicationSection::VAR_NEW_SECTION],
            [PublicationSection::VAR_REFUSED_SECTION],
            [PublicationSection::APP_GRANTED_SECTION],
            [PublicationSection::BUS_VAR_SECTION],
            [PublicationSection::LIC_REVOKED_SECTION],
        ];
    }

    public function testProvideAttachedToLicence(): void
    {
        $publicationLink = $this->getPublicationLink();
        $context = new \ArrayObject();

        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'A', 'NOTES')
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(false, 'A', 'NOTES1')
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'U', 'NOTES2')
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(false, 'U', 'NOTES3')
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'D', 'NOTES4')
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(false, 'D', 'NOTES5')
        );
        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'conditionUndertaking' => [
                    'New CONDITION NOTES. Attached to licence',
                    'New UNDERTAKING NOTES1. Attached to licence',
                    'Current UNDERTAKING NOTES2_ORIG. Attached to licence. Amended to: NOTES2',
                    'Current CONDITION NOTES3_ORIG. Attached to licence. Amended to: NOTES3',
                    'CONDITION to be removed: NOTES4. Attached to licence',
                    'UNDERTAKING to be removed: NOTES5. Attached to licence',
                ]
            ],
            $context->getArrayCopy()
        );
    }

    public function testProvideAttachedToOperatingCentre(): void
    {
        $publicationLink = $this->getPublicationLink();
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $address1 = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $address2 = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $address3 = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $address4 = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $address5 = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $address6 = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'A', 'NOTES', $address1)
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(false, 'A', 'NOTES1', $address2)
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'U', 'NOTES2', $address3)
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(false, 'U', 'NOTES3', $address4)
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(true, 'D', 'NOTES4', $address5)
        );
        $publicationLink->getApplication()->addConditionUndertakings(
            $this->getConditionUndertaking(false, 'D', 'NOTES5', $address6)
        );

        $mockAddressFormatter->shouldReceive('format')->with($address1)->once()->andReturn('ADDRESS1_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($address2)->once()->andReturn('ADDRESS2_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($address3)->once()->andReturn('ADDRESS3_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($address4)->once()->andReturn('ADDRESS4_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($address5)->once()->andReturn('ADDRESS5_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($address6)->once()->andReturn('ADDRESS6_FORMATTED');

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'conditionUndertaking' => [
                    'New CONDITION NOTES. Attached to Operating: ADDRESS1_FORMATTED',
                    'New UNDERTAKING NOTES1. Attached to Operating: ADDRESS2_FORMATTED',
                    'Current UNDERTAKING NOTES2_ORIG. Attached to Operating: ADDRESS3_FORMATTED. Amended to: NOTES2',
                    'Current CONDITION NOTES3_ORIG. Attached to Operating: ADDRESS4_FORMATTED. Amended to: NOTES3',
                    'CONDITION to be removed: NOTES4. Attached to Operating: ADDRESS5_FORMATTED',
                    'UNDERTAKING to be removed: NOTES5. Attached to Operating: ADDRESS6_FORMATTED',
                ]
            ],
            $context->getArrayCopy()
        );
    }

    /**
     * @return PublicationLink
     */
    private function getPublicationLink(): mixed
    {
        $publicationLink = new PublicationLink();

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::VAR_NEW_SECTION);
        $publicationLink->setPublicationSection($publicationSection);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setName('ORG_NAME');

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new RefData());
        $publicationLink->setLicence($licence);

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), true);
        $publicationLink->setApplication($application);

        return $publicationLink;
    }

    /**
     *
     * @param PublicationLink $publicationLink
     * @param bool $isCondition
     * @param string $action
     * @param string $notes
     * @param string $ocAddress
     */
    private function getConditionUndertaking(
        mixed $isCondition,
        mixed $action,
        mixed $notes,
        mixed $ocAddress = null
    ): ConditionUndertaking {
        if ($isCondition) {
            $conditionType = new RefData(ConditionUndertaking::TYPE_CONDITION);
            $conditionType->setDescription('CONDITION');
        } else {
            $conditionType = new RefData(ConditionUndertaking::TYPE_UNDERTAKING);
            $conditionType->setDescription('UNDERTAKING');
        }

        $conditionUndertaking = new ConditionUndertaking($conditionType, false, false);
        $conditionUndertaking->setAction($action);
        $conditionUndertaking->setNotes($notes);

        if ($ocAddress === null) {
            $conditionUndertaking->setAttachedTo(new RefData(ConditionUndertaking::ATTACHED_TO_LICENCE));
        } else {
            $conditionUndertaking->setAttachedTo(new RefData(ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE));
            $operatingCentre = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
            $operatingCentre->setAddress($ocAddress);
            $conditionUndertaking->setOperatingCentre($operatingCentre);
        }

        if ($action == 'U') {
            $origConditionUndertaking =
                $this->getConditionUndertaking(!$isCondition, 'A', $notes . '_ORIG', $ocAddress);
            $conditionUndertaking->setLicConditionVariation($origConditionUndertaking);
        }

        return $conditionUndertaking;
    }
}
