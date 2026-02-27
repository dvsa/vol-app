<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Exception;

use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;

/**
 * Class DisabledHandlerExceptionTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class DisabledHandlerExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testMessageContainsClassName(): void
    {
        $className = (new \stdClass())::class;
        $sut = new DisabledHandlerException($className);
        $this->assertStringContainsString($className, $sut->getMessage());
    }
}
