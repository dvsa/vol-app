<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Api\Service\Submission\Sections\Stub\AbstractSectionStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Submission\Sections\AbstractSection
 */
class AbstractSectionTest extends MockeryTestCase
{
    /** @var  AbstractSectionStub */
    private $sut;

    /** @var  \Dvsa\Olcs\Api\Domain\QueryHandlerManager | m\MockInterface */
    private $mockQueryHandler;
    /** @var  \Laminas\View\Renderer\PhpRenderer | m\MockInterface */
    private $mockViewRenderer;

    public function setUp(): void
    {
        $this->mockQueryHandler = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class);
        $this->mockViewRenderer = m::mock(\Laminas\View\Renderer\PhpRenderer::class);

        $this->sut = new AbstractSectionStub($this->mockQueryHandler, $this->mockViewRenderer);
    }

    public function testHandleQuery(): void
    {
        $query = m::mock(QueryInterface::class);

        $this->mockQueryHandler
            ->shouldReceive('handleQuery')
            ->with($query)
            ->once()
            ->andReturn('EXPECT');

        static::assertEquals('EXPECT', $this->sut->handleQuery($query));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestExtractPerson')]
    public function testExtractPerson(mixed $contactDetails, mixed $expect): void
    {
        static::assertEquals($expect, $this->sut->extractPerson($contactDetails));
    }

    public static function dpTestExtractPerson(): array
    {
        $personData = [
            'title' => '',
            'forename' => '',
            'familyName' => '',
            'birthDate' => '',
            'birthPlace' => ''
        ];

        return [
            [
                'contactDetails' => null,
                'expect' => $personData,
            ],
            [
                'contactDetails' => (new ContactDetails(new RefData()))
                    ->setPerson(
                        (new Person())
                            ->setTitle(
                                (new RefData())->setDescription('unit_Title')
                            )
                    ),
                'expect' => ['title' => 'unit_Title'] + $personData,
            ],
            [
                'contactDetails' => (new ContactDetails(new RefData()))
                    ->setPerson(
                        (new Person())
                            ->setForename('unit_ForeName')
                            ->setFamilyName('unit_FamilyName')
                            ->setBirthDate(new \DateTime('2010-09-08'))
                            ->setBirthPlace('unit_BirthPlace')
                    ),
                'expect' =>
                    [
                        'forename' => 'unit_ForeName',
                        'familyName' => 'unit_FamilyName',
                        'birthDate' => '08/09/2010',
                        'birthPlace' => 'unit_BirthPlace',
                    ] + $personData,
            ],

        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFormatDate')]
    public function testFormatDate(mixed $dateTime, mixed $expect): void
    {
        static::assertEquals($expect, $this->sut->formatDate($dateTime));
    }

    public static function dpTestFormatDate(): array
    {
        return [
            [
                'dateTime' => '',
                'expect' => '',
            ],
            [
                'dateTime' => '01-02-2003',
                'expect' => '01/02/2003',
            ],
            [
                'dateTime' => new \DateTime('2013-12-11'),
                'expect' => '11/12/2013',
            ],
        ];
    }

    public function testGetViewRenderer(): void
    {
        static::assertSame($this->mockViewRenderer, $this->sut->getViewRenderer());
    }

    public function testGetRepos(): void
    {
        $repos = ['a' => 'aRepo','b' => 'bRepo'];
        $this->sut->setRepos($repos);
        $this->assertEquals($repos, $this->sut->getRepos());
    }

    public function testGetRepo(): void
    {
        $repos = ['a' => 'aRepo','b' => 'bRepo'];
        $this->sut->setRepos($repos);
        $this->assertEquals($repos['a'], $this->sut->getRepo('a'));
    }

    public function testGetRepoException(): void
    {
        $this->expectException(RuntimeException::class);
        $repos = ['a' => 'aRepo','b' => 'bRepo'];
        $this->sut->setRepos($repos);
        $this->sut->getRepo('c');
    }
}
