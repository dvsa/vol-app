<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\Version;
use PHPUnit_Framework_TestCase;

/**
 * Class VersionTest
 * @package OlcsTest\View\Helper
 * @covers \OLCS\View\Helper\Version
 */
class VersionTest extends PHPUnit_Framework_TestCase
{
    public function testVersionViewHelper()
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
