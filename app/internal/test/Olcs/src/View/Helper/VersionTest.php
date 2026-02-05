<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\Version;

/**
 * Class VersionTest
 * @package OlcsTest\View\Helper
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\OLCS\View\Helper\Version::class)]
class VersionTest extends \PHPUnit\Framework\TestCase
{
    public function testVersionViewHelper(): void
    {
        $versionHelper = new Version();
        $versionHelper->setVersion(1);
        $this->assertEquals('1', $versionHelper->__invoke());

        $versionHelper = new Version();
        $versionHelper->setVersion('1.11.1232');
        $this->assertEquals('1.11.1232', $versionHelper->__invoke());

        $versionHelper = new Version();
        $versionHelper->setVersion('');
        $this->assertEquals('', $versionHelper->__invoke());

        $versionMultiLine = '4.13
Wed 31 May 12:10:37 BST 2017
';

        $versionHelper = new Version();
        $versionHelper->setVersion($versionMultiLine);
        $this->assertEquals('4.13 Wed 31 May 12:10:37 BST 2017', $versionHelper->__invoke());
    }
}
