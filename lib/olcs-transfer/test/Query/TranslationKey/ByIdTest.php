<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\Query\TranslationKey\ById;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\TranslationKey\ById::class)]
final class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
              'id' => 2
            ]
        );
        $this->assertEquals([
        'id' => 2,
        'previewEditorJs' => null
        ], $sut->getArrayCopy());
    }

    public function testPreviewFlag(): void
    {
        $query = ById::create(['id' => 2, 'previewEditorJs' => true]);

        $this->assertTrue($query->getPreviewEditorJs());
        $this->assertTrue($query->getArrayCopy()['previewEditorJs']);
    }

    private function createContainer($dto)
    {
        $serviceManager = new ServiceManager();
        $builder = new AnnotationBuilder();
        $builder->setFilterManager(new FilterPluginManager($serviceManager));
        $builder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        return $builder->createQuery($dto);
    }

    public function testPreviewFlagIsOptional(): void
    {
        $container = $this->createContainer(ById::create(['id' => 1]));

        $this->assertTrue($container->isValid(), json_encode($container->getMessages()));
    }
}
