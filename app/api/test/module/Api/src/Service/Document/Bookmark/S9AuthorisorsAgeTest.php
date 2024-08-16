<?php

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\S9AuthorisorsAge;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class S9AuthorisorsAgeTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $bookmark = new S9AuthorisorsAge();

        $this->assertEquals('', $bookmark->render());
    }
}
