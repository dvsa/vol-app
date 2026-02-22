<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Formatter;

use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\Formatter\People as Formatter;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PeopleTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
    }

    public function testSoletrader(): void
    {
        $organisation = new Organisation();
        $organisation->setType(new RefData(Organisation::ORG_TYPE_SOLE_TRADER));

        $this->assertNull(Formatter::format($organisation, []));
    }

    public function testNotPersons(): void
    {
        $organisation = new Organisation();
        $organisation->setType(new RefData(Organisation::ORG_TYPE_LLP));

        $this->assertNull(Formatter::format($organisation, []));
    }

    public function testLtd(): void
    {
        $organisation = new Organisation();
        $organisation->setType(new RefData(Organisation::ORG_TYPE_REGISTERED_COMPANY));

        $person1 = (new Person())->setForename('Reggie')->setFamilyName('Kray');
        $person2 = (new Person())->setForename('Ronnie')->setFamilyName('May');

        $this->assertSame(
            'Director(s): Reggie Kray, Ronnie May',
            Formatter::format($organisation, [$person1, $person2])
        );
    }

    public function testLlp(): void
    {
        $organisation = new Organisation();
        $organisation->setType(new RefData(Organisation::ORG_TYPE_LLP));

        $person1 = (new Person())->setForename('Reggie')->setFamilyName('Kray');
        $person2 = (new Person())->setForename('Ronnie')->setFamilyName('May');

        $this->assertSame(
            'Partner(s): Reggie Kray, Ronnie May',
            Formatter::format($organisation, [$person1, $person2])
        );
    }

    public function testPartnership(): void
    {
        $organisation = new Organisation();
        $organisation->setType(new RefData(Organisation::ORG_TYPE_PARTNERSHIP));

        $person1 = (new Person())->setForename('Reggie')->setFamilyName('Kray');
        $person2 = (new Person())->setForename('Ronnie')->setFamilyName('May');

        $this->assertSame(
            'Partner(s): Reggie Kray, Ronnie May',
            Formatter::format($organisation, [$person1, $person2])
        );
    }

    public function testOther(): void
    {
        $organisation = new Organisation();
        $organisation->setType(new RefData(Organisation::ORG_TYPE_OTHER));

        $person1 = (new Person())->setForename('Reggie')->setFamilyName('Kray');
        $person2 = (new Person())->setForename('Ronnie')->setFamilyName('May');

        $this->assertSame(
            'Reggie Kray, Ronnie May',
            Formatter::format($organisation, [$person1, $person2])
        );
    }
}
