<?php

declare(strict_types=1);

namespace CommonTest\Controller\Traits;

/**
 * Generic Section Index Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GenericSectionIndexActionTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('controller_traits')]
    #[\PHPUnit\Framework\Attributes\Group('generic_section_controller_traits')]
    public function testIndexAction(): void
    {
        $sut = new class {
            use \Common\Controller\Traits\GenericSectionIndexAction;

            public int $goToFirstSubSectionCalls = 0;

            public function goToFirstSubSection()
            {
                $this->goToFirstSubSectionCalls++;
            }
        };

        $sut->indexAction();

        $this->assertSame(1, $sut->goToFirstSubSectionCalls);
    }
}
