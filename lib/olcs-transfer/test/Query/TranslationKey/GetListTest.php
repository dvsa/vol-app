<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\TranslationKey;

use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\TranslationKey\GetList::class)]
final class GetListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = GetList::create(
            [
                'translationSearch' => 'search term',
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );
        $this->assertEquals([
            'translationSearch' => 'search term',
            'page' => 1,
            'limit' => 10,
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => [],
            'markupOnly' => null,
            'format' => null
        ], $sut->getArrayCopy());
    }

    public function testRichAndMarkupFilters(): void
    {
        $query = GetList::create(['format' => 'editorjs', 'markupOnly' => true]);

        $this->assertSame('editorjs', $query->getFormat());
        $this->assertTrue($query->getMarkupOnly());
    }

    private function createContainer($dto)
    {
        $serviceManager = new ServiceManager();
        $builder = new AnnotationBuilder();
        $builder->setFilterManager(new FilterPluginManager($serviceManager));
        $builder->setValidatorManager(new ValidatorPluginManager($serviceManager));

        return $builder->createQuery($dto);
    }

    public function testFormatAndMarkupOnlyAreOptional(): void
    {
        $container = $this->createContainer(GetList::create(['page' => 1, 'limit' => 10, 'sort' => 'id', 'order' => 'ASC']));

        $this->assertTrue($container->isValid(), json_encode($container->getMessages()));
    }

    public function testFormatMustBeTextOrEditorjs(): void
    {
        $container = $this->createContainer(GetList::create(['page' => 1, 'limit' => 10, 'sort' => 'id', 'order' => 'ASC', 'format' => 'html']));

        $this->assertFalse($container->isValid());
    }
}
