<?php

declare(strict_types=1);

namespace OlcsTest\View\Model;

use Common\Test\MockeryTestCase;
use Olcs\View\Model\LvaOverview;
use Olcs\View\Model\LvaOverviewSection;

/**
 * @see LvaOverview
 */
final class LvaOverviewTest extends MockeryTestCase
{
    protected const string SECTIONS_VARIABLE_NAME = 'sections';
    protected const array EMPTY_DATA = [];
    protected const int SECTION_IDENTIFIER = 0;
    protected const int SECTION_IDENTIFIER_PLUS_1 = 1;
    protected const array DATA = ['id' => self::SECTION_IDENTIFIER, 'idIndex' => 2, 'organisation' => ['type' => ['id' => 'ORGANISATION TYPE']]];
    protected const array SECTION_NUMBER_DATA = ['sectionNumber' => self::SECTION_IDENTIFIER_PLUS_1];
    protected const array COLLECTION_OF_ONE_SECTION_FORMATTED_AS_AN_ARRAY = [self::FIRST_SECTION_ARRAY_KEY => ['SECTION DATA']];
    protected const string SECTION_REFERENCE = 'SECTION REFERENCE';
    protected const array COLLECTION_OF_ONE_SECTION_REFERENCE = [self::FIRST_SECTION_ARRAY_KEY => self::SECTION_REFERENCE];
    protected const string FIRST_SECTION_ARRAY_KEY = 'FIRST SECTION ARRAY KEY';
    public const string DEFAULT_MODE = 'DEFAULT MODE';
    protected const string ANCHOR_REF_VARIABLE = 'anchorRef';

    /**
     * @var LvaOverview
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesEmptySectionsVariable(): void
    {
        // Setup
        $this->setUpSut(static::EMPTY_DATA);

        // Assert
        $this->assertIsArray($this->sut->getVariable(static::SECTIONS_VARIABLE_NAME));
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesEmptySectionsVariable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSection(): LvaOverviewSection
    {
        // Setup
        $this->setUpSut(static::DATA, static::COLLECTION_OF_ONE_SECTION_FORMATTED_AS_AN_ARRAY);

        // Assert
        $sectionsVariable = $this->sut->getVariable(static::SECTIONS_VARIABLE_NAME);
        $this->assertNotEmpty($sectionsVariable);
        $this->assertInstanceOf(LvaOverviewSection::class, $sectionsVariable[0]);
        return $sectionsVariable[0];
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSection')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSectionWithRef(LvaOverviewSection $section): void
    {
        // Assert
        $this->assertEquals(static::FIRST_SECTION_ARRAY_KEY, $section->ref);
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSection')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSectionWithMode(LvaOverviewSection $section): void
    {
        // Assert
        $this->assertEquals(static::COLLECTION_OF_ONE_SECTION_FORMATTED_AS_AN_ARRAY[static::FIRST_SECTION_ARRAY_KEY], $section->mode);
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSection')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionWhichIsAnArrayWithSectionWithData(LvaOverviewSection $section): void
    {
        // Assert
        $this->assertEquals(array_merge(static::DATA, static::SECTION_NUMBER_DATA), $section->data);
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesEmptySectionsVariable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionReferenceWithSection(): LvaOverviewSection
    {
        // Setup
        $this->setUpSutWithSectionModelThatDoesNotTakeAModeWhenConstructed(static::DATA, static::COLLECTION_OF_ONE_SECTION_REFERENCE);

        // Assert
        $sectionsVariable = $this->sut->getVariable(static::SECTIONS_VARIABLE_NAME);
        $this->assertNotEmpty($sectionsVariable);
        $this->assertInstanceOf(LvaOverviewSection::class, $overviewSection = $sectionsVariable[0]);
        return $overviewSection;
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesSectionsVariableWhenProvidedSectionReferenceWithSection')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionReferenceWithSectionWithRef(LvaOverviewSection $overviewSection): void
    {
        // Assert
        $this->assertEquals(static::SECTION_REFERENCE, $overviewSection->ref);
    }

    #[\PHPUnit\Framework\Attributes\Depends('constructInitialisesSectionsVariableWhenProvidedSectionReferenceWithSection')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function constructInitialisesSectionsVariableWhenProvidedSectionReferenceWithSectionWithData(LvaOverviewSection $overviewSection): void
    {
        // Assert
        $this->assertEquals(static::DATA, $overviewSection->data);
    }

    protected function setUpSut(mixed ...$args): void
    {
        $this->sut = new class (...$args) extends LvaOverview {
            protected function newSectionModel(mixed ...$args): LvaOverviewSection
            {
                return new class (...$args) extends LvaOverviewSection
                {
                    public function __construct(public mixed $ref, public mixed $data, public mixed $mode)
                    {
                    }
                };
            }
        };
    }

    /**
     * Sets up an sut which uses section models that don ot take a mode when constructed.
     */
    protected function setUpSutWithSectionModelThatDoesNotTakeAModeWhenConstructed(mixed ...$args): void
    {
        $this->sut = new class (...$args) extends LvaOverview {
            protected function newSectionModel(mixed ...$args): LvaOverviewSection
            {
                return new class (...$args) extends LvaOverviewSection
                {
                    public function __construct(public mixed $ref, public mixed $data)
                    {
                    }
                };
            }
        };
    }
}
