<?php

namespace Dvsa\OlcsTest\Transfer\Query;

use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\OlcsTest\Transfer\DtoTest;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArraySerializableInterface;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;

trait QueryTest
{
    use DtoTest;

    protected function createDtoContainer(ArraySerializableInterface $dto)
    {
        $serviceManager = m::mock(ServiceManager::class);

        $annotationBuilder = new AnnotationBuilder();

        $annotationBuilder->setFilterManager(new FilterPluginManager($serviceManager));
        $annotationBuilder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        return $annotationBuilder->createQuery($dto);
    }
}
