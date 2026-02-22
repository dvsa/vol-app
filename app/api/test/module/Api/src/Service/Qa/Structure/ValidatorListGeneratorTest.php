<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationValidation as ApplicationValidationEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Validator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorList;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorListFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\ValidatorListGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ValidatorListGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ValidatorListGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $applicationValidation1 = m::mock(ApplicationValidationEntity::class);
        $applicationValidation2 = m::mock(ApplicationValidationEntity::class);

        $applicationValidations = [
            $applicationValidation1,
            $applicationValidation2
        ];

        $validator1 = m::mock(Validator::class);
        $validator2 = m::mock(Validator::class);

        $validatorGenerator = m::mock(ValidatorGenerator::class);
        $validatorGenerator->shouldReceive('generate')
            ->with($applicationValidation1)
            ->andReturn($validator1);
        $validatorGenerator->shouldReceive('generate')
            ->with($applicationValidation2)
            ->andReturn($validator2);

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity->shouldReceive('getQuestion->getApplicationValidations')
            ->andReturn($applicationValidations);

        $validatorList = m::mock(ValidatorList::class);
        $validatorList->shouldReceive('addValidator')
            ->with($validator1)
            ->once()
            ->ordered();
        $validatorList->shouldReceive('addValidator')
            ->with($validator2)
            ->once()
            ->ordered();

        $validatorListFactory = m::mock(ValidatorListFactory::class);
        $validatorListFactory->shouldReceive('create')
            ->andReturn($validatorList);

        $validatorListGenerator = new ValidatorListGenerator($validatorListFactory, $validatorGenerator);

        $this->assertSame(
            $validatorList,
            $validatorListGenerator->generate($applicationStepEntity)
        );
    }
}
