<?php

/**
 * CreateTrailerTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Trailer;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Trailer as TrailerRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Trailer\CreateTrailer;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Trailer;
use Dvsa\Olcs\Transfer\Command\Trailer\CreateTrailer as Cmd;

/**
 * Class CreateTrailerTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CreateTrailerTest extends AbstractCommandHandlerTestCase
{
    private $licence;

    public function setUp(): void
    {
        $this->sut = new CreateTrailer();
        $this->mockRepo('Trailer', TrailerRepo::class);

        $this->licence = m::mock(Licence::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->references = [
            Licence::class => [
                7 => $this->licence
            ],
        ];

        parent::initReferences();
    }

    /**
     * @dataProvider dpIsLongerSemiTrailer
     */
    public function testHandleCommand($isLongerSemiTrailer, $expectedIsLongerSemiTrailer)
    {
        $data = [
            'trailerNo' => 'A1000',
            'isLongerSemiTrailer' => $isLongerSemiTrailer,
            'licence' => '7',
            'specifiedDate' => '2015-01-01'
        ];

        $command = Cmd::create($data);

        $this->repoMap['Trailer']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Trailer::class))
            ->andReturnUsing(
                function (Trailer $trailer) use ($data, $expectedIsLongerSemiTrailer) {
                    $this->assertEquals($data['trailerNo'], $trailer->getTrailerNo());
                    $this->assertEquals($expectedIsLongerSemiTrailer, $trailer->getIsLongerSemiTrailer());
                    $this->assertSame($this->licence, $trailer->getLicence());
                    $this->assertEquals($data['specifiedDate'], $trailer->getSpecifiedDate()->format('Y-m-d'));
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'trailer' => null
            ],
            'messages' => [
                'Trailer created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function dpIsLongerSemiTrailer()
    {
        return [
            ['Y', true],
            ['N', false],
        ];
    }
}
