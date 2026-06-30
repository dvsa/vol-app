<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\DataTransformer\ApplicationStepsPostDataTransformer;
use Common\Service\Qa\DataTransformer\DataTransformerInterface;
use Common\Service\Qa\DataTransformer\DataTransformerProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationStepsPostDataTransformerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationStepsPostDataTransformerTest extends MockeryTestCase
{
    public function testGetTransformed(): void
    {
        $fieldset43Slug = 'permit-usage';
        $fieldset72Slug = 'no-of-permits';

        $applicationSteps = [
            [
                'fieldsetName' => 'fieldset43',
                'slug' => $fieldset43Slug
            ],
            [
                'fieldsetName' => 'fieldset72',
                'slug' => $fieldset72Slug
            ]
        ];

        $fieldset43PostData = [
            'field3' => 'value3',
            'field4' => 'value4'
        ];

        $preTransformedFieldset72PostData = [
            'field1' => 'value1',
            'field2' => 'value2'
        ];

        $postTransformedFieldset72PostData = [
            'transformedField1' => 'transformedValue1',
            'transformedField2' => 'transformedValue2'
        ];

        $postData = [
            'fieldset43' => $fieldset43PostData,
            'fieldset72' => $preTransformedFieldset72PostData
        ];

        $expectedTransformedPostData = [
            'fieldset43' => $fieldset43PostData,
            'fieldset72' => $postTransformedFieldset72PostData
        ];

        $fieldset72DataTransformer = m::mock(DataTransformerInterface::class);
        $fieldset72DataTransformer->shouldReceive('getTransformed')
            ->with($preTransformedFieldset72PostData)
            ->andReturn($postTransformedFieldset72PostData);

        $dataTransformerProvider = m::mock(DataTransformerProvider::class);
        $dataTransformerProvider->shouldReceive('getTransformer')
            ->with($fieldset43Slug)
            ->andReturnNull();
        $dataTransformerProvider->shouldReceive('getTransformer')
            ->with($fieldset72Slug)
            ->andReturn($fieldset72DataTransformer);

        $sut = new ApplicationStepsPostDataTransformer($dataTransformerProvider);

        $this->assertEquals(
            $expectedTransformedPostData,
            $sut->getTransformed($applicationSteps, $postData)
        );
    }
}
