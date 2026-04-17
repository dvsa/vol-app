<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\PublicationLink as Sut;
use Laminas\Form\FormInterface;

/**
 * PublicationLink Test
 */
class PublicationLinkTest extends MockeryTestCase
{
    /**
     * Tests mapFromForm
     */
    public function testMapFromForm(): void
    {
        $inData = ['fields' => ['field' => 'data']];
        $expected = ['field' => 'data'];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    /**
     * Tests mapFromErrors
     */
    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }

    /**
     * test map from result
     *
     *
     * @param $inputData
     * @param $expectedOutput
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultProvider')]
    public function testMapFromResultStatusIsNew(mixed $inputData, mixed $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, Sut::mapFromResult($inputData));
    }

    /**
     * Data provider for mapFromResult
     *
     * @return array
     */
    public static function mapFromResultProvider(): array
    {
        $pubType = 'pub type';
        $trafficArea = 'trafficArea';
        $publicationNo = 12345;
        $pubStatus = 'status';
        $pubSection = 'section';
        $pubDate = '2015-12-25';
        $formatPubDate = date('d/m/Y', strtotime($pubDate));
        $text1 = 'text1';
        $text2 = 'text2';
        $text3 = 'text3';
        $id = 11;
        $version = 22;

        $inputDataNew = [
            'id' => $id,
            'version' => $version,
            'publication' => [
                'pubType' => $pubType,
                'trafficArea' => [
                    'name' => $trafficArea
                ],
                'publicationNo' => $publicationNo,
                'pubStatus' => [
                    'description' => $pubStatus
                ],
                'pubDate' => $pubDate
            ],
            'publicationSection' => [
                'description' => $pubSection
            ],
            'text1' => $text1,
            'text2' => $text2,
            'text3' => $text3,
            'isNew' => true
        ];

        $inputDataNotNew = $inputDataNew;
        $inputDataNotNew['isNew'] = false;

        $readOnly = [
            'typeArea' => $pubType . ' / ' . $trafficArea,
            'publicationNo' => $publicationNo,
            'status' => $pubStatus,
            'section' => $pubSection,
            'trafficArea' => $trafficArea,
            'publicationDate' => $formatPubDate
        ];

        $expectedIsNewOutput = [
            'fields' => [
                'id' => $id,
                'version' => $version,
                'text1' => $text1,
                'text2' => $text2,
                'text3' => $text3
            ],
            'readOnly' => $readOnly
        ];

        $expectedNotNewOutput = [
            'readOnlyText' => [
                'text1' => $text1,
                'text2' => $text2,
                'text3' => $text3
            ],
            'readOnly' => $readOnly
        ];

        return [
            [$inputDataNew, $expectedIsNewOutput],
            [$inputDataNotNew, $expectedNotNewOutput]
        ];
    }
}
