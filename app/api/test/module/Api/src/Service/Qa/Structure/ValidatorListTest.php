<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\Validator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * ValidatorListTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ValidatorListTest extends MockeryTestCase
{
    private $validator1Rule;

    private $validator2Rule;

    private $nonExistentRule;

    private $validator1;

    private $validator2;

    private $validatorList;

    public function setUp(): void
    {
        $this->validator1Rule = 'Between';

        $this->validator2Rule = 'GreaterThan';

        $this->nonExistentRule = 'NonExistentRule';

        $this->validator1 = m::mock(Validator::class);
        $this->validator1->shouldReceive('hasRule')
            ->with($this->validator1Rule)
            ->andReturn(true);
        $this->validator1->shouldReceive('hasRule')
            ->with($this->validator2Rule)
            ->andReturn(false);
        $this->validator1->shouldReceive('hasRule')
            ->with($this->nonExistentRule)
            ->andReturn(false);

        $this->validator2 = m::mock(Validator::class);
        $this->validator2->shouldReceive('hasRule')
            ->with($this->validator1Rule)
            ->andReturn(false);
        $this->validator2->shouldReceive('hasRule')
            ->with($this->validator2Rule)
            ->andReturn(true);
        $this->validator2->shouldReceive('hasRule')
            ->with($this->nonExistentRule)
            ->andReturn(false);

        $this->validatorList = new ValidatorList();
        $this->validatorList->addValidator($this->validator1);
        $this->validatorList->addValidator($this->validator2);
    }

    public function testGetRepresentation(): void
    {
        $validator1Representation = ['validator1Representation'];

        $validator2Representation = ['validator2Representation'];

        $this->validator1->shouldReceive('getRepresentation')
            ->andReturn($validator1Representation);

        $this->validator2->shouldReceive('getRepresentation')
            ->andReturn($validator2Representation);

        $expectedRepresentation = [
            $validator1Representation,
            $validator2Representation
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $this->validatorList->getRepresentation()
        );
    }

    public function testGetValidatorByRule(): void
    {
        $this->assertSame(
            $this->validator1,
            $this->validatorList->getValidatorByRule($this->validator1Rule)
        );

        $this->assertSame(
            $this->validator2,
            $this->validatorList->getValidatorByRule($this->validator2Rule)
        );
    }

    public function testGetValidatorByRuleNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Validator with rule name NonExistentRule not found');

        $this->validatorList->getValidatorByRule($this->nonExistentRule);
    }
}
