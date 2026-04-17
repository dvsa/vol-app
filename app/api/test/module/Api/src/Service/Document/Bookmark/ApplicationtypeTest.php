<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Applicationtype as Sut;

/**
 * ApplicationtypeTest
 */
class ApplicationtypeTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQueryApplication(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['application' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertSame(123, $query->getId());
        $this->assertSame(null, $query->getCase());
        $this->assertSame(['licenceType'], $query->getBundle());
    }

    public function testGetQueryCase(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['case' => 12399]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertSame(null, $query->getId());
        $this->assertSame(12399, $query->getCase());
        $this->assertSame(['licenceType'], $query->getBundle());
    }

    public function testGetQueryNull(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery([]);

        $this->assertSame(null, $query);
    }

    public function testRender(): void
    {
        $bookmark = new Sut();
        $bookmark->setData(['licenceType' => ['description' => 'DESCRIPTION']]);

        $this->assertEquals('DESCRIPTION', $bookmark->render());
    }
}
