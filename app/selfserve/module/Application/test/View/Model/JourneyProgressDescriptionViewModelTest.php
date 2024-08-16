<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\View\Model;

use Common\Test\MockeryTestCase;
use InvalidArgumentException;

/**
 * @see JourneyProgressDescriptionViewModel
 */
class JourneyProgressDescriptionViewModelTest extends MockeryTestCase
{
    protected const AN_UNKNOWN_SECTION_ID = 'UNKNOWN SECTION ID';
    protected const AN_EMPTY_SECTIONS_ARRAY = [];
    protected const A_SECTION_ID = 'A SECTION ID';
    protected const A_SECTION = 'A SECTION';
    protected const A_SECTIONS_NUMBER_IN_A_JOURNEY = 1;
    protected const AN_ARRAY_WITH_A_SECTION = [self::A_SECTION_ID => self::A_SECTION];
    protected const VIEW_MODEL_TEMPLATE = 'partials/translated-text';
    protected const TEXT_VARIABLE_NAME = 'text';
    protected const APPLICATION_STEPS_TRANSLATION_KEY = 'application.steps';
    protected const DATA_VARIABLE_NAME = 'data';

    /**
     * @var JourneyProgressDescriptionViewModel
     */
    protected $sut;

    /**
     * @test
     */
    public function constructThrowsExceptionIfCurrentSectionIsNotInSections()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);

        // Execute
        $this->setUpSut(static::AN_UNKNOWN_SECTION_ID, static::AN_EMPTY_SECTIONS_ARRAY);
    }

    /**
     * @test
     */
    public function constructHasCorrectTemplate()
    {
        // Setup
        $this->setUpSut(static::A_SECTION_ID, static::AN_ARRAY_WITH_A_SECTION);

        // Assert
        $this->assertEquals(static::VIEW_MODEL_TEMPLATE, $this->sut->getTemplate());
    }

    /**
     * @test
     */
    public function constructSetsTextVariable()
    {
        // Setup
        $this->setUpSut(static::A_SECTION_ID, static::AN_ARRAY_WITH_A_SECTION);

        // Assert
        $this->assertEquals(static::APPLICATION_STEPS_TRANSLATION_KEY, $this->sut->getVariable(static::TEXT_VARIABLE_NAME));
    }

    /**
     * @test
     */
    public function constructSetsDataVariable(): array
    {
        // Setup
        $this->setUpSut(static::A_SECTION_ID, static::AN_ARRAY_WITH_A_SECTION);
        $dataVariable = $this->sut->getVariable(static::DATA_VARIABLE_NAME, []);

        // Assert
        $this->assertNotEmpty($dataVariable);

        return $dataVariable;
    }

    /**
     * @test
     * @depends __construct_SetsDataVariable
     */
    public function constructSetsDataVariableWithCurrentSectionNumber(array $data)
    {
        // Assert
        $this->assertEquals(static::A_SECTIONS_NUMBER_IN_A_JOURNEY, $data[0]);
    }

    /**
     * @test
     * @depends __construct_SetsDataVariable
     */
    public function constructSetsDataVariableWithNumberOfSections(array $data)
    {
        // Assert
        $this->assertEquals(count(static::AN_ARRAY_WITH_A_SECTION), $data[1]);
    }

    protected function setUpSut(mixed ...$args): void
    {
        $this->sut = new JourneyProgressDescriptionViewModel(...$args);
    }
}
