<?php

declare(strict_types=1);

namespace OlcsTest\View\Model;

use Common\Test\MockeryTestCase;
use Olcs\View\Model\LvaOverview;
use Olcs\View\Model\LvaOverviewSection;

/**
 * @see LvaOverview
 */
class LvaOverviewTest extends MockeryTestCase
{
    protected const SECTIONS_VARIABLE_NAME = 'sections';
    protected const EMPTY_DATA = [];
    protected const SECTION_IDENTIFIER = 0;
    protected const SECTION_IDENTIFIER_PLUS_1 = 1;
    protected const DATA = ['id' => self::SECTION_IDENTIFIER, 'idIndex' => 2, 'organisation' => ['type' => ['id' => 'ORGANISATION TYPE']]];
    protected const SECTION_NUMBER_DATA = ['sectionNumber' => self::SECTION_IDENTIFIER_PLUS_1];
    protected const COLLECTION_OF_ONE_SECTION_FORMATTED_AS_AN_ARRAY = [self::FIRST_SECTION_ARRAY_KEY => ['SECTION DATA']];
    protected const SECTION_REFERENCE = 'SECTION REFERENCE';
    protected const COLLECTION_OF_ONE_SECTION_REFERENCE = [self::FIRST_SECTION_ARRAY_KEY => self::SECTION_REFERENCE];
    protected const FIRST_SECTION_ARRAY_KEY = 'FIRST SECTION ARRAY KEY';
    public const DEFAULT_MODE = 'DEFAULT MODE';
    protected const ANCHOR_REF_VARIABLE = 'anchorRef';

    /**
     * @var LvaOverview
     */
    protected $sut;

    /**
     * @test
     */
    public function __construct_InitialisesEmptySectionsVariable()
    {
        // Setup
        $this->setUpSut(static::EMPTY_DATA);

        // Assert
        $this->assertIsArray($this->sut->getVariable(static::SECTIONS_VARIABLE_NAME));
    }

    /**
     * @test
     * @depends __construct_InitialisesEmptySectionsVariable
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection(): LvaOverviewSection
    {
        // Setup
        $this->setUpSut(static::DATA, static::COLLECTION_OF_ONE_SECTION_FORMATTED_AS_AN_ARRAY);

        // Assert
        $sectionsVariable = $this->sut->getVariable(static::SECTIONS_VARIABLE_NAME);
        $this->assertNotEmpty($sectionsVariable);
        $this->assertInstanceOf(LvaOverviewSection::class, $sectionsVariable[0]);
        return $sectionsVariable[0];
    }

    /**
     * @test
     * @depends __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection_WithRef(LvaOverviewSection $section)
    {
        // Assert
        $this->assertEquals(static::FIRST_SECTION_ARRAY_KEY, $section->ref);
    }

    /**
     * @test
     * @depends __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection_WithMode(LvaOverviewSection $section)
    {
        // Assert
        $this->assertEquals(static::COLLECTION_OF_ONE_SECTION_FORMATTED_AS_AN_ARRAY[static::FIRST_SECTION_ARRAY_KEY], $section->mode);
    }

    /**
     * @test
     * @depends __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionWhichIsAnArray_WithSection_WithData(LvaOverviewSection $section)
    {
        // Assert
        $this->assertEquals(array_merge(static::DATA, static::SECTION_NUMBER_DATA), $section->data);
    }

    /**
     * @test
     * @depends __construct_InitialisesEmptySectionsVariable
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionReference_WithSection(): LvaOverviewSection
    {
        // Setup
        $this->setUpSutWithSectionModelThatDoesNotTakeAModeWhenConstructed(static::DATA, static::COLLECTION_OF_ONE_SECTION_REFERENCE);

        // Assert
        $sectionsVariable = $this->sut->getVariable(static::SECTIONS_VARIABLE_NAME);
        $this->assertNotEmpty($sectionsVariable);
        $this->assertInstanceOf(LvaOverviewSection::class, $overviewSection = $sectionsVariable[0]);
        return $overviewSection;
    }

    /**
     * @test
     * @depends __construct_InitialisesSectionsVariable_WhenProvidedSectionReference_WithSection
     * @param LvaOverviewSection $overviewSection
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionReference_WithSection_WithRef(LvaOverviewSection $overviewSection)
    {
        // Assert
        $this->assertEquals(static::SECTION_REFERENCE, $overviewSection->ref);
    }

    /**
     * @test
     * @depends __construct_InitialisesSectionsVariable_WhenProvidedSectionReference_WithSection
     * @param LvaOverviewSection $overviewSection
     */
    public function __construct_InitialisesSectionsVariable_WhenProvidedSectionReference_WithSection_WithData(LvaOverviewSection $overviewSection)
    {
        // Assert
        $this->assertEquals(static::DATA, $overviewSection->data);
    }

    protected function setUpSut(...$args): void
    {
        $this->sut = new class(...$args) extends LvaOverview {
            protected function newSectionModel(...$args): LvaOverviewSection
            {
                return new class(...$args) extends LvaOverviewSection
                {
                    public $ref;
                    public $data;
                    public $mode;

                    public function __construct($ref, $data, $mode)
                    {
                        $this->ref = $ref;
                        $this->data = $data;
                        $this->mode = $mode;
                    }
                };
            }
        };
    }

    /**
     * Sets up an sut which uses section models that don ot take a mode when constructed.
     *
     * @param mixed ...$args
     */
    protected function setUpSutWithSectionModelThatDoesNotTakeAModeWhenConstructed(...$args): void
    {
        $this->sut = new class(...$args) extends LvaOverview {
            protected function newSectionModel(...$args): LvaOverviewSection
            {
                return new class(...$args) extends LvaOverviewSection
                {
                    public $ref;
                    public $data;

                    public function __construct($ref, $data)
                    {
                        $this->ref = $ref;
                        $this->data = $data;
                    }
                };
            }
        };
    }
}
