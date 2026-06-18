<?php

declare(strict_types=1);

namespace CommonTest\Form\Element\Attribute;

use Common\Test\MockeryTestCase;
use Common\Form\Element\Attribute\ClassList;

/**
 * @covers \Common\Form\Element\Attribute\ClassList
 */
class ClassListTest extends MockeryTestCase
{
    protected const A_CLASS = 'A_CLASS';

    protected const A_CLASS_ARRAY = [self::A_CLASS];

    protected const B_CLASS = 'B_CLASS';

    protected const B_CLASS_ARRAY = [self::B_CLASS];

    protected const AB_CLASS_STRING = self::A_CLASS . ' ' . self::B_CLASS;

    protected const AB_CLASS_ARRAY = [self::A_CLASS, self::B_CLASS];

    protected const EMPTY_CLASS_STRING = '';

    protected const AA_CLASS_ARRAY = [self::A_CLASS, self::A_CLASS];

    /**
     * @var ClassList|null
     */
    protected $sut;

    /**
     * @test
     */
    public function toStringIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, '__toString']);
    }

    /**
     * @test
     * @depends toStringIsCallable
     */
    public function toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded(): void
    {
        // Setup
        $this->setUpSut();

        $result = (string) $this->sut;

        // Assert
        $this->assertIsString($result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function addIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'add']);
    }

    /**
     * @test
     * @depends addIsCallable
     */
    public function addReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->add(static::A_CLASS);

        // Assert
        $this->assertEquals($this->sut, $result);
    }

    /**
     * @test
     * @depends addIsCallable
     * @depends toStringIsCallable
     */
    public function addAddsAClassWhenPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::A_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     */
    public function addAddsMultipleClassesWhenPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::AB_CLASS_STRING);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addIsCallable
     * @depends toStringIsCallable
     */
    public function addAddsAClassWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::A_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     */
    public function addAddsMultipleClassesWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::AB_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAnArray
     */
    public function addAddsMultipleClassesWhenPassedOneAtATime(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::A_CLASS_ARRAY);
        $this->sut->add(static::B_CLASS_ARRAY);

        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAnArray
     */
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
        $this->assertEquals(static::A_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedOneAtATime
     */
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
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedAString
     */
    public function addDoesNotAddDuplicateClasses(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add([static::A_CLASS, static::A_CLASS, static::B_CLASS]);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addIsCallable
     * @depends toStringIsCallable
     */
    public function constructAddsAClassWhenPassedAString(): void
    {
        // Execute
        $this->setUpSut(static::A_CLASS);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::A_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     */
    public function constructAddsMultipleClassesWhenPassedAString(): void
    {
        // Execute
        $this->setUpSut(static::AB_CLASS_STRING);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addIsCallable
     * @depends toStringIsCallable
     */
    public function constructAddsAClassWhenPassedAnArray(): void
    {
        // Execute
        $this->setUpSut(static::A_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::A_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     */
    public function constructAddsMultipleClassesWhenPassedAnArray(): void
    {
        // Execute
        $this->setUpSut(static::AB_CLASS_ARRAY);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAnArray
     */
    public function constructAddsAClassWhenPassedAClassList(): void
    {
        // Setup
        $otherClassList = new ClassList();
        $otherClassList->add(static::A_CLASS_ARRAY);

        // Execute
        $this->setUpSut($otherClassList);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::A_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedOneAtATime
     */
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
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedAString
     */
    public function constructDoesNotAddDuplicateClasses(): void
    {
        // Execute
        $this->setUpSut([static::A_CLASS, static::A_CLASS, static::B_CLASS]);
        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::AB_CLASS_STRING, $result);
    }

    /**
     * @test
     */
    public function removeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'remove']);
    }

    /**
     * @test
     * @depends removeIsCallable
     */
    public function removeRemovesClassThatWasNeverAdded(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->remove(static::A_CLASS);

        // Assert
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     * @depends removeIsCallable
     */
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

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     * @depends removeIsCallable
     */
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
        $this->assertEquals(static::B_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     * @depends removeIsCallable
     * @depends toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded
     */
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
        $this->assertEquals(static::EMPTY_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedAnArray
     * @depends toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded
     */
    public function removeRemovesAPreviouslyAddedClassWhenPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->add(static::AB_CLASS_ARRAY);
        $this->sut->remove(static::A_CLASS_ARRAY);

        $result = (string) $this->sut;

        // Assert
        $this->assertEquals(static::B_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsAClassWhenPassedAString
     * @depends toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded
     */
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
        $this->assertEquals(static::EMPTY_CLASS_STRING, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedAnArray
     * @depends toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded
     */
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
        $this->assertEquals(static::B_CLASS, $result);
    }

    /**
     * @test
     * @depends addAddsMultipleClassesWhenPassedAnArray
     * @depends toStringReturnsAnEmptyStringWhenNoClassesHaveBeenAdded
     */
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
        $this->assertEquals(static::EMPTY_CLASS_STRING, $result);
    }

    /**
     * @test
     */
    public function toArrayIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'toArray']);
    }

    /**
     * @test
     * @depends toArrayIsCallable
     */
    public function toArrayReturnsAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * @test
     * @depends toArrayReturnsAnArray
     * @depends constructAddsMultipleClassesWhenPassedAnArray
     */
    public function toArrayReturnsAnArrayOfClassNames(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertEquals(static::AB_CLASS_ARRAY, $result);
    }

    /**
     * @test
     * @depends toArrayReturnsAnArrayOfClassNames
     */
    public function toArrayReturnsAnArrayIndexedNumerically(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertEquals(array_values($result), $result);
    }

    /**
     * @test
     * @depends toArrayReturnsAnArrayOfClassNames
     */
    public function toArrayRemovesDuplicateClassNames(): void
    {
        // Setup
        $this->setUpSut(static::AA_CLASS_ARRAY);

        // Execute
        $result = $this->sut->toArray();

        // Assert
        $this->assertEquals(static::A_CLASS_ARRAY, $result);
    }

    /**
     * @test
     */
    public function fromStringIsCallable(): void
    {
        // Assert
        $this->assertIsCallable(static fn(string $str): \Common\Form\Element\Attribute\ClassList => \Common\Form\Element\Attribute\ClassList::fromString($str));
    }

    /**
     * @test
     * @depends fromStringIsCallable
     */
    public function fromStringReturnsAClassList(): void
    {
        // Execute
        $result = ClassList::fromString(static::AB_CLASS_STRING);

        // Assert
        $this->assertInstanceOf(ClassList::class, $result);
    }

    /**
     * @test
     * @depends fromStringReturnsAClassList
     */
    public function fromStringReturnsAClassListWithEachClassInAString(): void
    {
        // Execute
        $result = ClassList::fromString(static::AB_CLASS_STRING);

        // Assert
        $this->assertEquals(new ClassList(static::AB_CLASS_ARRAY), $result);
    }

    /**
     * @test
     */
    public function hasIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'has']);
    }

    /**
     * @test
     * @depends hasIsCallable
     * @depends addAddsAClassWhenPassedAString
     */
    public function hasReturnsFalseWhenAClassListIsEmptyAndPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->has(static::A_CLASS);

        // Assert
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     * @depends hasIsCallable
     * @depends addAddsAClassWhenPassedAString
     */
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

    /**
     * @test
     * @depends hasIsCallable
     */
    public function hasReturnsFalseWhenAClassListIsEmptyAndPassedAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->has(static::A_CLASS_ARRAY);

        // Assert
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     * @depends hasIsCallable
     * @depends addAddsMultipleClassesWhenPassedAnArray
     */
    public function hasReturnsTrueWhenAllClassesInAnArrayOfClassesAreInAClassList(): void
    {
        // Setup
        $this->setUpSut(static::AB_CLASS_ARRAY);

        // Execute
        $result = $this->sut->has(static::A_CLASS_ARRAY);

        // Assert
        $this->assertEquals(true, $result);
    }

    /**
     * @test
     * @depends hasIsCallable
     * @depends constructAddsMultipleClassesWhenPassedAnArray
     */
    public function hasReturnsFalseWhenAClassListIsEmptyAndPassedAClassList(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->has(new ClassList(static::A_CLASS_ARRAY));

        // Assert
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     * @depends hasIsCallable
     * @depends addAddsMultipleClassesWhenPassedAnArray
     * @depends constructAddsMultipleClassesWhenPassedAnArray
     */
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
