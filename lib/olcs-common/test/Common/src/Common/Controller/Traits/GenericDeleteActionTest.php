<?php

declare(strict_types=1);

namespace CommonTest\Controller\Traits;

/**
 * Generic Delete Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GenericDeleteActionTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('controller_traits')]
    #[\PHPUnit\Framework\Attributes\Group('generic_section_controller_traits')]
    public function testIndexAction(): void
    {
        $sut = new class {
            use \Common\Controller\Traits\GenericDeleteAction;

            public int $deleteCalls = 0;

            public function delete()
            {
                $this->deleteCalls++;
            }
        };

        $sut->deleteAction();

        $this->assertSame(1, $sut->deleteCalls);
    }
}
