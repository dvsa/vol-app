<?php

declare(strict_types=1);

/**
 * Client Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Address\Service;

use Dvsa\Olcs\Address\Service\Client;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Client Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ClientTest extends MockeryTestCase
{
    public function testSetUri(): void
    {
        $sut = new Client('/foo/bar/');
        $sut->setUri('/cake/');

        $this->assertEquals('/foo/bar/cake/', $sut->getUri()->getPath());
    }
}
