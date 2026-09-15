<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Doc;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Doc\DocTemplate::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Doc\AbstractDocTemplate::class)]
final class DocTemplateEntityTest extends EntityTester
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
        $actual = $sut->getDocTemplateBookmarks();

        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEmpty($actual);
    }
}
