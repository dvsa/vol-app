<?php

declare(strict_types=1);

namespace OlcsTest\Service\WebDav;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\WebDav\VirtualDirectory;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\INode;

#[\PHPUnit\Framework\Attributes\CoversClass(VirtualDirectory::class)]
class VirtualDirectoryTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function getNameReturnsTheName(): void
    {
        $sut = new VirtualDirectory('test-directory');

        $this->assertEquals('test-directory', $sut->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getChildrenReturnsChildren(): void
    {
        $child1 = Mockery::mock(INode::class);
        $child1->shouldReceive('getName')->andReturn('child1');

        $child2 = Mockery::mock(INode::class);
        $child2->shouldReceive('getName')->andReturn('child2');

        $sut = new VirtualDirectory('parent', [$child1, $child2]);

        $children = $sut->getChildren();

        $this->assertCount(2, $children);
        $this->assertSame($child1, $children[0]);
        $this->assertSame($child2, $children[1]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getChildrenReturnsEmptyArrayWhenNoChildren(): void
    {
        $sut = new VirtualDirectory('empty-dir');

        $children = $sut->getChildren();

        $this->assertIsArray($children);
        $this->assertEmpty($children);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getChildReturnsNamedChild(): void
    {
        $child1 = Mockery::mock(INode::class);
        $child1->shouldReceive('getName')->andReturn('file1.rtf');

        $child2 = Mockery::mock(INode::class);
        $child2->shouldReceive('getName')->andReturn('file2.doc');

        $sut = new VirtualDirectory('parent', [$child1, $child2]);

        $result = $sut->getChild('file2.doc');

        $this->assertSame($child2, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getChildThrowsNotFoundForMissingChild(): void
    {
        $child = Mockery::mock(INode::class);
        $child->shouldReceive('getName')->andReturn('existing-file.rtf');

        $sut = new VirtualDirectory('parent', [$child]);

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('Node not found: nonexistent-file.rtf');

        $sut->getChild('nonexistent-file.rtf');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function childExistsReturnsTrueForExistingChild(): void
    {
        $child = Mockery::mock(INode::class);
        $child->shouldReceive('getName')->andReturn('existing-file.rtf');

        $sut = new VirtualDirectory('parent', [$child]);

        $this->assertTrue($sut->childExists('existing-file.rtf'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function childExistsReturnsFalseForMissingChild(): void
    {
        $child = Mockery::mock(INode::class);
        $child->shouldReceive('getName')->andReturn('existing-file.rtf');

        $sut = new VirtualDirectory('parent', [$child]);

        $this->assertFalse($sut->childExists('nonexistent-file.rtf'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function childExistsReturnsFalseWhenNoChildren(): void
    {
        $sut = new VirtualDirectory('empty-dir');

        $this->assertFalse($sut->childExists('any-file.rtf'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFileThrowsForbidden(): void
    {
        $sut = new VirtualDirectory('parent');

        $this->expectException(Forbidden::class);
        $this->expectExceptionMessage('Creating files via WebDAV is not permitted');

        $sut->createFile('new-file.rtf');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createDirectoryThrowsForbidden(): void
    {
        $sut = new VirtualDirectory('parent');

        $this->expectException(Forbidden::class);
        $this->expectExceptionMessage('Creating directories via WebDAV is not permitted');

        $sut->createDirectory('new-dir');
    }
}
