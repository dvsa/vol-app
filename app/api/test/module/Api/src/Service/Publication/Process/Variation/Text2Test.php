<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Variation;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Text2Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text2Test extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text2
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Variation\Text2();

        parent::setUp();
    }

    /**
     * @param string $organisationType
     *
     * @return PublicationLink
     */
    private function getPublicationLink(mixed $organisationType): mixed
    {
        $publicationLink = new PublicationLink();

        $organisation = new Organisation();
        $organisation->setName('ORG_NAME');
        $organisation->setType(new RefData($organisationType));

        $licence = new Licence($organisation, new RefData());
        $publicationLink->setLicence($licence);

        return $publicationLink;
    }

    private function addTradingName(PublicationLink $publicationLink, mixed $id, mixed $name): void
    {
        $organisation = $publicationLink->getLicence()->getOrganisation();
        $tradingName = new \Dvsa\Olcs\Api\Entity\Organisation\TradingName($name, $organisation);
        $tradingName->setId($id);
        $organisation->addTradingNames($tradingName);
    }

    public function testSoleTrader(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_SOLE_TRADER);
        $context = new ImmutableArrayObject([]);

        $this->sut->process($publicationLink, $context);

        $expectedText2 = "ORG_NAME";

        $this->assertSame($expectedText2, $publicationLink->getText2());
    }

    public function testTradingName(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_SOLE_TRADER);
        $this->addTradingName($publicationLink, 12, 'TRADING_NAME_12');
        $this->addTradingName($publicationLink, 120, 'TRADING_NAME_120');
        $this->addTradingName($publicationLink, 4, 'TRADING_NAME_5');
        $context = new ImmutableArrayObject([]);

        $this->sut->process($publicationLink, $context);

        $expectedText2 = "ORG_NAME T/A TRADING_NAME_5";

        $this->assertSame($expectedText2, $publicationLink->getText2());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestPeople')]
    public function testPeople(mixed $organisationTypeId, mixed $peoplePrefix): void
    {
        $publicationLink = $this->getPublicationLink($organisationTypeId);
        $context = new ImmutableArrayObject(
            [
                'applicationPeople' => [
                    (new \Dvsa\Olcs\Api\Entity\Person\Person())->setForename('Randy')->setFamilyName('Couture'),
                    (new \Dvsa\Olcs\Api\Entity\Person\Person())->setForename('Rachel')->setFamilyName('Jones'),
                ]
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText2 = "ORG_NAME\n{$peoplePrefix}Randy Couture, Rachel Jones";

        $this->assertSame($expectedText2, $publicationLink->getText2());
    }

    public static function dataProviderTestPeople(): array
    {
        return [
            [Organisation::ORG_TYPE_LLP, 'Partner(s): '],
            [Organisation::ORG_TYPE_OTHER, ''],
            [Organisation::ORG_TYPE_PARTNERSHIP, 'Partner(s): '],
            [Organisation::ORG_TYPE_REGISTERED_COMPANY, 'Director(s): '],
        ];
    }

    public function testPeopleMissingContext(): void
    {
        $publicationLink = $this->getPublicationLink(Organisation::ORG_TYPE_OTHER);
        $context = new ImmutableArrayObject(
            [
                'applicationPeople' => 'Foo'
            ]
        );

        $this->sut->process($publicationLink, $context);

        $expectedText2 = "ORG_NAME";

        $this->assertSame($expectedText2, $publicationLink->getText2());
    }
}
