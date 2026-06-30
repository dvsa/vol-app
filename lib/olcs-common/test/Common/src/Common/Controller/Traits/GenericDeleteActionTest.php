<?php

/**
 * Generic Delete Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Controller\Traits;

/**
 * Generic Delete Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenericDeleteActionTest extends \PHPUnit\Framework\TestCase
{
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = $this->getMockForTrait(
            \Common\Controller\Traits\GenericDeleteAction::class,
            [],
            '',
            true,
            true,
            true,
            ['delete']
        );
    }

    /**
     * @group controller_traits
     * @group generic_section_controller_traits
     */
    public function testIndexAction(): void
    {
        $this->sut->expects($this->once())
            ->method('delete');

        $this->sut->deleteAction();
    }
}
