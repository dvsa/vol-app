<?php

declare(strict_types=1);

namespace CommonTest\Form\Element\Attribute;

use Common\Test\MockeryTestCase;
use Common\Form\Element\Attribute\ClassList;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Element\Attribute\ClassList::class)]
final class ClassListTest extends MockeryTestCase
{
    protected const string A_CLASS = 'A_CLASS';

    protected const array A_CLASS_ARRAY = [self::A_CLASS];

    protected const string B_CLASS = 'B_CLASS';

    protected const array B_CLASS_ARRAY = [self::B_CLASS];

    protected const string AB_CLASS_STRING = self::A_CLASS . ' ' . self::B_CLASS;

    protected const array AB_CLASS_ARRAY = [self::A_CLASS, self::B_CLASS];

    protected const string EMPTY_CLASS_STRING = '';

    protected const array AA_CLASS_ARRAY = [self::A_CLASS, self::A_CLASS];

    /**
     * @var ClassList|null
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function toStringIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__toString']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('toStringIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded(): void
    {
        // Setup
        $this->setUpSut();

        $result = (string) $this->sut;

        // Assert
        $this->assertIsString($result);
        $this->assertEmpty($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function addIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'add']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->add(static::A_CLASS);

        // Assert
        $this->assertEquals($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsAClassWhenPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::A_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsMultipleClassesWhenPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::AB_CLASS_STRING);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsAClassWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::A_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsMultipleClassesWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::AB_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsMultipleClassesWhenPassedOneAtATime(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS_ARRAY);
        $this->sut->add(static::B_CLASS_ARRAY);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsAClassWhenPassedAClassList(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $otherClassList = new ClassList();
        $otherClassList->add(static::A_CLASS_ARRAY);

        $this->sut->add($otherClassList);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::A_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedOneAtATime')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addAddsMultipleClassesWhenPassedAClassList(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $otherClassList = new ClassList();
        $otherClassList->add(static::A_CLASS_ARRAY);
        $otherClassList->add(static::B_CLASS_ARRAY);

        $this->sut->add($otherClassList);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function addDoesNotAddDuplicateClasses(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add([static::A_CLASS, static::A_CLASS, static::B_CLASS]);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAddsAClassWhenPassedAString(): void
    {
        // Execute
        $this->setUpSut(static::A_CLASS);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::A_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAddsMultipleClassesWhenPassedAString(): void
    {
        // Execute
        $this->setUpSut(static::AB_CLASS_STRING);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAddsAClassWhenPassedAnArray(): void
    {
        // Execute
        $this->setUpSut(static::A_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::A_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAddsMultipleClassesWhenPassedAnArray(): void
    {
        // Execute
        $this->setUpSut(static::AB_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAddsAClassWhenPassedAClassList(): void
    {
        // Setup
        $otherClassList = new ClassList();
        $otherClassList->add(static::A_CLASS_ARRAY);

        // Execute
        $this->setUpSut($otherClassList);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::A_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedOneAtATime')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAddsMultipleClassesWhenPassedAClassList(): void
    {
        // Setup
        $otherClassList = new ClassList();
        $otherClassList->add(static::A_CLASS_ARRAY);
        $otherClassList->add(static::B_CLASS_ARRAY);

        // Execute
        $this->setUpSut($otherClassList);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructDoesNotAddDuplicateClasses(): void
    {
        // Execute
        $this->setUpSut([static::A_CLASS, static::A_CLASS, static::B_CLASS]);
        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::AB_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function removeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'remove']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('removeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesClassThatWasNeverAdded(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->remove(static::A_CLASS);

        // Assert
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Depends('removeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS);
        $result = $this->sut->remove(static::A_CLASS);

        // Assert
        $this->assertEquals($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Depends('removeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesAPreviouslyAddedClassWhenPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS);
        $this->sut->add(static::B_CLASS);
        $this->sut->remove(static::A_CLASS);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::B_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Depends('removeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesMultiplePreviouslyAddedClassesWhenPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS);
        $this->sut->add(static::B_CLASS);
        $this->sut->remove(static::AB_CLASS_STRING);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::EMPTY_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesAPreviouslyAddedClassWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::AB_CLASS_ARRAY);
        $this->sut->remove(static::A_CLASS_ARRAY);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::B_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesMultiplePreviouslyAddedClassesWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS);
        $this->sut->add(static::B_CLASS);
        $this->sut->remove(static::AB_CLASS_ARRAY);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::EMPTY_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesAPreviouslyAddedClassWhenPassedAClassList(): void
    {
        // Setup
        $this->setUpSut();
        $otherClassList = new ClassList();
        $otherClassList->add(static::A_CLASS_ARRAY);

        $this->sut->add(static::AB_CLASS_ARRAY);

        // Execute
        $this->sut->remove($otherClassList);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::B_CLASS, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function removeRemovesMultiplePreviouslyAddedClassesWhenPassedAClassList(): void
    {
        // Setup
        $this->setUpSut();
        $otherClassList = new ClassList();
        $otherClassList->add(static::AB_CLASS_ARRAY);

        $this->sut->add(static::AB_CLASS_ARRAY);

        // Execute
        $this->sut->remove($otherClassList);

        $result = (string) $this->sut;

        // Assert
        $this->assertSame(static::EMPTY_CLASS_STRING, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function toArrayIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'toArray']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('toArrayIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function toArrayReturnsAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertIsArray($result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('toArrayReturnsAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('constructAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function toArrayReturnsAnArrayOfClassNames(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertEquals(static::AB_CLASS_ARRAY, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('toArrayReturnsAnArrayOfClassNames')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function toArrayReturnsAnArrayIndexedNumerically(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertEquals(array_values($result), $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('toArrayReturnsAnArrayOfClassNames')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function toArrayRemovesDuplicateClassNames(): void
    {
        // Setup
        $this->setUpSut(static::AA_CLASS_ARRAY);

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertEquals(static::A_CLASS_ARRAY, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function fromStringIsCallable(): void
    {
        // Assert
        $this->assertIsCallable(static fn(string $str): \Common\Form\Element\Attribute\ClassList => \Common\Form\Element\Attribute\ClassList::fromString($str));
    }

    #[\PHPUnit\Framework\Attributes\Depends('fromStringIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function fromStringReturnsAClassList(): void
    {
        // Execute
        $result = ClassList::fromString(static::AB_CLASS_STRING);

        // Assert
        $this->assertInstanceOf(ClassList::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('fromStringReturnsAClassList')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function fromStringReturnsAClassListWithEachClassInAString(): void
    {
        // Execute
        $result = ClassList::fromString(static::AB_CLASS_STRING);

        // Assert
        $this->assertEquals(new ClassList(static::AB_CLASS_ARRAY), $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function hasIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'has']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('hasIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsFalseWhenAClassListIsEmptyAndPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->has(static::A_CLASS);

        // Assert
        $this->assertEquals(false, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('hasIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('addAddsAClassWhenPassedAString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsTrueWhenAStringClassIsInAClassList(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->add(static::A_CLASS);

        // Execute
        $result = $this->sut->has(static::A_CLASS);

        // Assert
        $this->assertEquals(true, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('hasIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsFalseWhenAClassListIsEmptyAndPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->has(static::A_CLASS_ARRAY);

        // Assert
        $this->assertEquals(false, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('hasIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsTrueWhenAllClassesInAnArrayOfClassesAreInAClassList(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->has(static::A_CLASS_ARRAY);

        // Assert
        $this->assertEquals(true, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('hasIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('constructAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsFalseWhenAClassListIsEmptyAndPassedAClassList(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->has(new ClassList(static::A_CLASS_ARRAY));

        // Assert
        $this->assertEquals(false, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('hasIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('addAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('constructAddsMultipleClassesWhenPassedAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function hasReturnsTrueWhenAllClassesOfAClassListAreInAClassList(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->has(new ClassList(static::A_CLASS_ARRAY));

        // Assert
        $this->assertTrue($result);
    }

    protected function setUpSut(array|ClassList|string ...$args): void
    {
        $this->sut = new ClassList(...$args);
    }
}
