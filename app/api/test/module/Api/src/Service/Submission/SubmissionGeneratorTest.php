<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefdataEntity;
use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator
 */
class SubmissionGeneratorTest extends MockeryTestCase
{
    public function testConstructor(): void
    {
        $mockConfig = ['cfg'];
        /** @var SectionGeneratorPluginManager $mockSectionGeneratorPluginManager */
        $mockSectionGeneratorPluginManager = m::mock(SectionGeneratorPluginManager::class);
        $sut = new SubmissionGenerator($mockConfig, $mockSectionGeneratorPluginManager);

        $this->assertInstanceOf(SubmissionGenerator::class, $sut);
    }

    /**
     * Test exception thrown for invalid exception types
     * @throws \Exception
     */
    public function testGenerateSubmissionInvalidSubmissionType(): void
    {
        $mockConfig = ['cfg'];

        /** @var SectionGeneratorPluginManager $mockSectionGeneratorPluginManager */
        $mockSectionGeneratorPluginManager = m::mock(SectionGeneratorPluginManager::class);
        $mockSubmission = m::mock(SubmissionEntity::class)->makePartial();

        /** @var CaseEntity | m\MockInterface $mockCase */
        $mockCase = m::mock(CaseEntity::class)->makePartial();
        $mockCase->setId(77);
        $mockSubmission->setSubmissionType(new RefdataEntity('invalid_submission_type'));

        $sections = [];

        $sut = new SubmissionGenerator($mockConfig, $mockSectionGeneratorPluginManager);

        $mockSubmission->shouldReceive('getCase')->andReturn($mockCase);

        $mockCase->shouldReceive('isTm')->andReturn(false);

        $this->expectException('Exception');

        $sut->generateSubmission($mockSubmission, $sections);
    }

    /**
     * Test valid submission is generated
     *
     * @param $submissionType
     * @param $expectedSections
     * @throws \Exception
     */
    public function testGenerateSubmissionValidSubmission(): void
    {
        $sectionId = 'valid_submission_type';
        $mockConfig = [
            'section-types' => [
                'valid_submission_type' => [
                    'section1',
                    'section2'
                ]
            ]
        ];

        /** @var SectionGeneratorPluginManager | m\MockInterface $mockSectionGeneratorPluginManager */
        $mockSectionGeneratorPluginManager = m::mock(SectionGeneratorPluginManager::class)
        ->shouldReceive('setRepos')
        ->getMock();

        /** @var SubmissionEntity | m\MockInterface $mockSubmission */
        $mockSubmission = m::mock(SubmissionEntity::class)->makePartial();

        /** @var CaseEntity | m\MockInterface $mockCase */
        $mockCase = m::mock(CaseEntity::class)->makePartial();
        $mockCase->setId(77);
        $mockSubmission->setSubmissionType(new RefdataEntity($sectionId));

        $sections = [];

        $sut = new SubmissionGenerator($mockConfig, $mockSectionGeneratorPluginManager);

        $mockSubmission->shouldReceive('getCase')->andReturn($mockCase);

        $mockCase->shouldReceive('isTm')->andReturn(false);

        $mockSectionGeneratorPluginManager->shouldReceive('get')
            ->with(m::type('string'))
            ->times(count($mockConfig['section-types'][$sectionId]))
            ->andReturnSelf()
            ->shouldReceive('generateSection')
            ->with($mockCase)
            ->andReturn('foo');

        $result = $sut->generateSubmission($mockSubmission, $sections);
        $this->assertEquals('{"section1":"foo","section2":"foo"}', $mockSubmission->getDataSnapshot());
        $this->assertArrayHasKey('section1', $mockSubmission->getSectionData());
        $this->assertEquals(count($mockConfig['section-types'][$sectionId]), count($mockSubmission->getSectionData()));
        $this->assertArrayHasKey('section2', $mockSubmission->getSectionData());
        $this->assertEquals('foo', $mockSubmission->getSectionData()['section1']);
        $this->assertSame($result, $mockSubmission);
    }

    /**
     * Branch test for TM submissions that remove sections
     */
    public function testGenerateSubmissionValidTmSubmission(): void
    {
        $sectionId = 'valid_submission_type';
        $mockConfig = [
            'excluded-tm-sections' => ['case-summary', 'outstanding-applications', 'people'],
            'section-types' => [
                'valid_submission_type' => [
                    'section1',
                    'section2',
                    'case-summary',
                    'outstanding-applications',
                    'people',
                    'section6',
                ]
            ]
        ];

        $expectSections = [
            'section1' => 'foo',
            'section2' => 'foo',
            'section6' => 'unit_FromSnapshot',
        ];

        /** @var CaseEntity | m\MockInterface $mockCase */
        $mockCase = m::mock(CaseEntity::class)->makePartial();
        $mockCase->setId(77);
        $mockCase->shouldReceive('isTm')->andReturn(true);

        /** @var SectionGeneratorPluginManager $mockSectionGeneratorPluginManager */
        $mockSectionGeneratorPluginManager = m::mock(SectionGeneratorPluginManager::class);
        $mockSectionGeneratorPluginManager->shouldReceive('get')
            ->with(m::type('string'))
            ->andReturnSelf()
            ->shouldReceive('generateSection')
            ->with($mockCase)
            ->andReturn('foo')
            ->shouldReceive('setRepos');

        /** @var SubmissionEntity | m\MockInterface $mockSubmission */
        $mockSubmission = m::mock(SubmissionEntity::class)->makePartial();
        $mockSubmission->setSubmissionType(new RefdataEntity($sectionId));
        $mockSubmission
            ->shouldReceive('getCase')->times(3)->andReturn($mockCase)
            ->shouldReceive('getDataSnapshot')->once()->andReturn('{"section6":"unit_FromSnapshot", "people":"unitT"}')
            ->shouldReceive('setDataSnapshot')->once()->with(json_encode($expectSections));

        $sections = [];

        //  call & check
        $sut = new SubmissionGenerator($mockConfig, $mockSectionGeneratorPluginManager);
        $actual = $sut->generateSubmission($mockSubmission, $sections);

        static::assertSame($actual, $mockSubmission);

        $actualSectionData = $mockSubmission->getSectionData();
        static::assertEquals($expectSections, $actualSectionData);

        static::assertNotContains('case-outline', $actualSectionData);
        static::assertNotContains('outstanding-applications', $actualSectionData);
        static::assertNotContains('people', $actualSectionData);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerateSubmissionSectionData')]
    public function testGenerateSubmissionSectionData(mixed $subSection, mixed $data, mixed $expect): void
    {
        $sectionId = 8888;

        $mockCase = m::mock(CaseEntity::class);

        /** @var SubmissionEntity $mockSubmission */
        $mockSubmission = m::mock(SubmissionEntity::class)
            ->shouldReceive('getCase')->once()->andReturn($mockCase)
            ->getMock();

        /** @var SectionGeneratorPluginManager $mockSerctionGenPluginMngr */
        $mockSerctionGenPluginMngr = m::mock(SectionGeneratorPluginManager::class)
            ->shouldReceive('get')->once()->with($sectionId)->andReturnSelf()
            ->shouldReceive('generateSection')->with($mockCase)->andReturn($data)
            ->shouldReceive('setRepos')
            ->getMock();

        $sut = new SubmissionGenerator([], $mockSerctionGenPluginMngr);

        static::assertEquals($expect, $sut->generateSubmissionSectionData($mockSubmission, $sectionId, $subSection));
    }

    public static function dpTestGenerateSubmissionSectionData(): array
    {
        return [
            [
                'subSection' => null,
                'data' => ['EXPECTED'],
                'expect' => ['EXPECTED'],
            ],
            [
                'subSection' => 'unit_SubSectKey',
                'data' => [
                    'unit_SubSectKey' => 'SUB_EXPECTED',
                ],
                'expect' => 'SUB_EXPECTED',
            ],
        ];
    }
}
