<?php

namespace CommonTest\Service\Qa\Custom\Common;

use Common\Form\Annotation\CustomAnnotationBuilder;
use Common\Form\Model\Fieldset\MultipleFileUpload;
use Common\Service\Qa\Custom\Common\FileUploadFieldsetGenerator;
use Laminas\Form\Annotation\AbstractBuilder;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Factory as FormFactory;
use Laminas\Form\InputFilterProviderFieldset;

class FileUploadFieldsetGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $multipleFileUploadSpec = [
            'input_filter' => [
                'fileCount' => [
                    'fileCountAttribute1' => 'fileCountValue1',
                    'fileCountAttribute2' => 'fileCountValue2'
                ]
            ],
            'otherAttribute1' => 'otherValue1',
            'otherAttribute2' => 'otherValue2',
        ];

        $updatedInputFilter = [
            'fileCount' => [
                'fileCountAttribute1' => 'fileCountValue1',
                'fileCountAttribute2' => 'fileCountValue2',
                'continue_if_empty' => true,
            ]
        ];

        $fieldset = m::mock(ElementInterface::class);
        $fieldset->shouldReceive('setInputFilterSpecification')
            ->with($updatedInputFilter)
            ->once();

        $formFactory = m::mock(FormFactory::class);
        $formFactory->shouldReceive('create')
            ->with(m::type(\ArrayObject::class))
            ->once()
            ->andReturn($fieldset);

        $customAnnotationBuilder = m::mock(AbstractBuilder::class);
        $customAnnotationBuilder->shouldReceive('getFormSpecification')
            ->with(MultipleFileUpload::class)
            ->once()
            ->andReturn(new \ArrayObject($multipleFileUploadSpec));

        $fileUploadFieldsetGenerator = new FileUploadFieldsetGenerator($formFactory, $customAnnotationBuilder);

        $this->assertSame(
            $fieldset,
            $fileUploadFieldsetGenerator->generate()
        );
    }
}
