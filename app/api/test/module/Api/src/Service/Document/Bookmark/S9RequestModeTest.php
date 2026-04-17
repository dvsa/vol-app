<?php

declare(strict_types=1);

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark as BookmarkNs;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class S9RequestModeTest extends TestCase
{
    public function testAlias(): void
    {
        $sut = new BookmarkNs\S9RequestMode();

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Service\Document\Bookmark\StatementContactType::class, $sut);
    }
}
