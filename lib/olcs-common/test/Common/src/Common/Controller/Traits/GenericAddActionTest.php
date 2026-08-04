<?php

declare(strict_types=1);

namespace CommonTest\Controller\Traits;

/**
 * Generic Add Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GenericAddActionTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('controller_traits')]
    #[\PHPUnit\Framework\Attributes\Group('generic_section_controller_traits')]
    public function testIndexAction(): void
    {
        $sut = new class {
            use \Common\Controller\Traits\GenericAddAction;

            public int $renderSectionCalls = 0;

            public function renderSection()
            {
                $this->renderSectionCalls++;
            }
        };

        $sut->addAction();

        $this->assertSame(1, $sut->renderSectionCalls);
    }
}
