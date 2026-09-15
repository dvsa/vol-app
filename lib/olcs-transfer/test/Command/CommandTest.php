<?php

namespace Dvsa\OlcsTest\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\OlcsTest\Transfer\DtoTest;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArraySerializableInterface;
use Mockery as m;
use Laminas\Filter\FilterPluginManager;
use Laminas\Validator\ValidatorPluginManager;

trait CommandTest
{
    use DtoTest;

    protected function createDtoContainer(ArraySerializableInterface $dto)
    {
        $serviceManager = m::mock(ServiceManager::class);

        $annotationBuilder = new AnnotationBuilder();

        $annotationBuilder->setFilterManager(new FilterPluginManager($serviceManager));
        $annotationBuilder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        return $annotationBuilder->createCommand($dto);
    }
}
