<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationStepTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationStepTest extends MockeryTestCase
{
    public const TYPE = 'checkbox';

    public const FIELDSET_NAME = 'fieldset123';

    public const SHORT_NAME = 'Cabotage';

    public const SLUG = 'slug';

    private $elementRepresentation;

    private $validatorListRepresentation;

    private $element;

    private $validatorList;

    public function setUp(): void
    {
        $this->elementRepresentation = ['elementRepresentation'];
        $this->validatorListRepresentation = ['validatorListRepresentation'];

        $this->element = m::mock(ElementInterface::class);
        $this->element->shouldReceive('getRepresentation')
            ->andReturn($this->elementRepresentation);

        $this->validatorList = m::mock(ValidatorList::class);
        $this->validatorList->shouldReceive('getRepresentation')
            ->andReturn($this->validatorListRepresentation);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetRepresentation')]
    public function testGetRepresentation(mixed $enabled): void
    {
        $expectedRepresentation = [
            'type' => self::TYPE,
            'fieldsetName' => self::FIELDSET_NAME,
            'shortName' => self::SHORT_NAME,
            'slug' => self::SLUG,
            'enabled' => $enabled,
            'element' => $this->elementRepresentation,
            'validators' => $this->validatorListRepresentation
        ];

        $applicationStep = new ApplicationStep(
            self::TYPE,
            self::FIELDSET_NAME,
            self::SHORT_NAME,
            self::SLUG,
            $enabled,
            $this->element,
            $this->validatorList
        );

        $this->assertEquals(
            $expectedRepresentation,
            $applicationStep->getRepresentation()
        );
    }

    public static function dpGetRepresentation(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetValidatorList(): void
    {
        $applicationStep = new ApplicationStep(
            self::TYPE,
            self::FIELDSET_NAME,
            self::SHORT_NAME,
            self::SLUG,
            true,
            $this->element,
            $this->validatorList
        );

        $this->assertSame(
            $this->validatorList,
            $applicationStep->getValidatorList()
        );
    }
}
