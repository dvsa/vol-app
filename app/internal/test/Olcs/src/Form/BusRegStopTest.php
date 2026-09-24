<?php

declare(strict_types=1);

namespace OlcsTest\Form;

use Common\Form\Element\DynamicSelect;
use Common\Form\Element\DynamicSelectFactory;
use Common\Service\Data\Interfaces\ListData;
use Common\Service\Data\PluginManager;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\Form\View\Helper\FormSelect;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Form\Model\Fieldset\BusRegStop;

final class BusRegStopTest extends MockeryTestCase
{
    public function testSubsidySelectorsRenderInOrderWithSavedValues(): void
    {
        $form = $this->createForm();
        $names = array_keys($form->getElements());
        $position = array_search('notFixedStopDetail', $names, true);
        $this->assertSame(
            ['notFixedStopDetail', 'subsidyTrafficAreas', 'subsidyLocalAuthorities', 'subsidised', 'subsidyDetail'],
            array_slice($names, $position, 5)
        );
        $this->assertSame('TAOs providing subsidies', $form->get('subsidyTrafficAreas')->getLabel());
        $this->assertSame('Local authorities providing subsidies', $form->get('subsidyLocalAuthorities')->getLabel());
        $form->setData([
            'subsidyTrafficAreas' => [['id' => 'F']],
            'subsidyLocalAuthorities' => [['id' => 87]],
            'subsidyDetail' => 'Historical comments',
        ]);
        $this->assertSame(['F'], $form->get('subsidyTrafficAreas')->getValue());
        $this->assertSame([87], $form->get('subsidyLocalAuthorities')->getValue());
        $this->assertSame('Historical comments', $form->get('subsidyDetail')->getValue());
        $html = (new FormSelect())->render($form->get('subsidyLocalAuthorities'));
        $this->assertStringContainsString('selected="selected"', $html);
        $this->assertStringContainsString('Milton Keynes Council', $html);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('subsidyCommentsProvider')]
    public function testSubsidyCommentsLength(string $comments, bool $valid): void
    {
        $form = $this->createForm();
        $input = $form->getInputFilter()->get('subsidyDetail');
        $input->setValue($comments);

        $this->assertSame($valid, $input->isValid());
    }

    public static function subsidyCommentsProvider(): array
    {
        return [
            'multiple long names' => [str_repeat('a', 128) . "\n" . str_repeat('b', 128), true],
            'at limit' => [str_repeat('é', 1000), true],
            'over limit' => [str_repeat('a', 1001), false],
        ];
    }

    private function createForm(): \Laminas\Form\FormInterface
    {
        $data = m::mock(ListData::class);
        $data->shouldReceive('fetchListOptions')->andReturn([
            'F' => ['label' => 'East of England', 'options' => [87 => 'Milton Keynes Council']],
        ]);
        $manager = m::mock(PluginManager::class);
        $manager->shouldReceive('get')->andReturn($data);
        $services = new ServiceManager(['services' => ['DataServiceManager' => $manager]]);
        $elements = new FormElementManager($services, [
            'factories' => [DynamicSelect::class => DynamicSelectFactory::class],
            'aliases' => ['DynamicSelect' => DynamicSelect::class, 'TextArea' => \Laminas\Form\Element\Textarea::class],
        ]);
        $builder = new AnnotationBuilder();
        $builder->setFormFactory(new Factory($elements));
        return $builder->createForm(BusRegStop::class);
    }
}
