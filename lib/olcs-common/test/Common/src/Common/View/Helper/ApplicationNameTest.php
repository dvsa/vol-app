<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\ApplicationName;

/**
 * Test ApplicationName view helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test render without version
     */
    public function testRenderWithoutApplicationName(): void
    {
        $config = [];
        $sut = new ApplicationName($config);

        $this->assertEquals('', $sut->__invoke());
        $this->assertEquals('', $sut->render());
    }

    /**
     * Test render with version
     */
    public function testRenderWithApplicationName(): void
    {
        $config = ['application-name' => 'Yo'];
        $sut = new ApplicationName($config);

        $this->assertEquals('Yo', $sut->__invoke());
        $this->assertEquals('Yo', $sut->render());
    }
}
