<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Licence\Text1 as LicenceText1;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text1Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text1Test extends MockeryTestCase
{
    public function testProcess(): void
    {
        $sut = new LicenceText1();

        $input = [
            'previousPublication' => 9876
        ];

        $licence = new Licence(new Organisation(), new RefData());
        $licence->setLicNo('OB12345');
        $licence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $licence->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $publicationLink = new PublicationLink();
        $publicationLink->setLicence($licence);

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "OB12345 SN\n(9876)";
        $this->assertEquals($expectedString, $publicationLink->getText1());
    }

    public function testProcessNoPreviousPublication(): void
    {
        $sut = new LicenceText1();

        $input = [];

        $licence = new Licence(new Organisation(), new RefData());
        $licence->setLicNo('OB12345');
        $licence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $licence->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));

        $publicationLink = new PublicationLink();
        $publicationLink->setLicence($licence);

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "OB12345 SN";
        $this->assertEquals($expectedString, $publicationLink->getText1());
    }

    public function testProcessNotGoods(): void
    {
        $sut = new LicenceText1();

        $input = [
            'previousPublication' => 9876
        ];

        $licence = new Licence(new Organisation(), new RefData());
        $licence->setLicNo('OB12345');
        $licence->setLicenceType(new RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $publicationLink = new PublicationLink();
        $publicationLink->setLicence($licence);

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "OB12345 SN";
        $this->assertEquals($expectedString, $publicationLink->getText1());
    }
}
