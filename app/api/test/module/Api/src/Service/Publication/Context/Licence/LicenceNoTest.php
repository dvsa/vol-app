<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class LicenceNoTest
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class LicenceNoTest extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceNo
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceNo(
            m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class)
        );

        parent::setUp();
    }

    public function testProvideWithLicence(): void
    {
        $publicationLink = $this->getPublicationLinkWithLicence();
        $context = new \ArrayObject();

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'licenceNo' => 'UB1234567'
            ],
            $context->getArrayCopy()
        );
    }

    public function testProvideWithApplication(): void
    {
        $publicationLink = $this->getPublicationLinkWithApplication();
        $context = new \ArrayObject();

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'licenceNo' => 'UB1234588'
            ],
            $context->getArrayCopy()
        );
    }

    /**
     * @return PublicationLink
     */
    private function getPublicationLinkWithLicence(): mixed
    {
        $publicationLink = new PublicationLink();
        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new RefData());
        $licence->setLicNo('UB1234567');
        $publicationLink->setLicence($licence);

        return $publicationLink;
    }

    /**
     * @return PublicationLink
     */
    private function getPublicationLinkWithApplication(): mixed
    {
        $publicationLink = new PublicationLink();

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new RefData());
        $licence->setLicNo('UB1234588');
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), false);

        $publicationLink->setApplication($application);

        return $publicationLink;
    }
}
