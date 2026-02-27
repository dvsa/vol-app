<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Doc;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Doc\DocBookmark as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * @covers Dvsa\Olcs\Api\Entity\Doc\DocBookmark
 * @covers Dvsa\Olcs\Api\Entity\Doc\AbstractDocBookmark
 */
class DocBookmarkEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstructor(): void
    {
        $sut = new Entity();

        $actual = $sut->getDocParagraphBookmarks();
        static::assertInstanceOf(ArrayCollection::class, $actual);
        static::assertEmpty($actual);
    }
}
