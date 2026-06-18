<?php

namespace CommonTest\View\Helper\Navigation;

use Common\View\Helper\Navigation\MenuRbac;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;

/**
 * @covers \Common\View\Helper\Navigation\MenuRbac
 */
class MenuRbacTest extends MockeryTestCase
{
    public function testFilter(): void
    {
        $mockPage1 = m::mock(AbstractPage::class)->makePartial();
        $mockPage2 = m::mock(AbstractPage::class)->makePartial();
        $mockPage3 = m::mock(AbstractPage::class)->makePartial();

        $mockPage1->setVisible(false);
        $mockPage2->setVisible(true);
        $mockPage3->setVisible(false);

        /** @var AbstractContainer | m\MockInterface $mockCntr */
        $mockCntr = m::mock(AbstractContainer::class)->makePartial();
        $mockCntr->setPages(
            [
                $mockPage1,
                $mockPage2,
                $mockPage3,
            ]
        );

        $sut = new MenuRbac();

        $sut->setContainer($mockCntr);

        $actual = $sut();
        static::assertSame($sut, $actual);

        $pages = $actual->getContainer()->getPages();
        static::assertCount(1, $actual->getContainer()->getPages());
        static::assertSame($mockPage2, current($pages));
    }
}
