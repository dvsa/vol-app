<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\Applicationtype;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\CaseBundle;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Stlstandardlicparagraph as Sut;
use Mockery as m;

/**
 * StlstandardlicparagraphTest
 */
class StlstandardlicparagraphTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQueryLicence(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(LicenceBundle::class, $query);
        $this->assertSame(123, $query->getId());
        $this->assertSame(['licenceType'], $query->getBundle());
    }

    public function testGetQueryCaseNewApplication(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['licence' => 123, 'case' => 99]);

        $this->assertInstanceOf(CaseBundle::class, $query);
        $this->assertSame(99, $query->getId());
        $this->assertSame(['application' => ['licenceType'], 'licence' => ['licenceType']], $query->getBundle());
    }

    public function testGetQueryApplication(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['application' => 456]);

        $this->assertInstanceOf(ApplicationBundle::class, $query);
        $this->assertSame(456, $query->getId());
        $this->assertSame(['licenceType'], $query->getBundle());
    }

    public function testGetQueryCase(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['case' => 456]);

        $this->assertInstanceOf(CaseBundle::class, $query);
        $this->assertSame(456, $query->getId());
        $this->assertSame(['application' => ['licenceType'], 'licence' => ['licenceType']], $query->getBundle());
    }

    public function testGetQueryNull(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery([]);

        $this->assertSame(null, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderDataProvider')]
    public function testRender(mixed $expectSnippet, mixed $licenceTypeId): void
    {
        $bookmark = new Sut();
        $bookmark->setData(['licenceType' => ['id' => $licenceTypeId]]);

        if ($expectSnippet) {
            $mockParser = m::mock();
            $mockParser->shouldReceive('getFileExtension')->with()->once()->andReturn('rtf');
            $bookmark->setParser($mockParser);
            $this->assertStringStartsWith('(If a standard licence – delete as appropriate)', $bookmark->render());
        } else {
            $this->assertNull($bookmark->render());
        }
    }

    public static function dpRenderDataProvider(): array
    {
        return [
            [true, Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
            [true, Licence::LICENCE_TYPE_STANDARD_NATIONAL],
            [false, Licence::LICENCE_TYPE_RESTRICTED],
            [false, Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderCaseDataDataProvider')]
    public function testRenderCaseData(mixed $expectSnippet, mixed $data): void
    {
        $bookmark = new Sut();
        $bookmark->setData($data);

        if ($expectSnippet) {
            $mockParser = m::mock();
            $mockParser->shouldReceive('getFileExtension')->with()->once()->andReturn('rtf');
            $bookmark->setParser($mockParser);
            $this->assertStringStartsWith('(If a standard licence – delete as appropriate)', $bookmark->render());
        } else {
            $this->assertNull($bookmark->render());
        }
    }

    public static function dpRenderCaseDataDataProvider(): array
    {
        return [
            [
                true,
                [
                    'application' => ['licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]],
                    'licence' => ['licenceType' => ['id' => Licence::LICENCE_TYPE_RESTRICTED]]
                ]
            ],
            [
                true,
                [
                    'application' => null,
                    'licence' => ['licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]],
                ],
            ],
            [
                true,
                [
                    'application' => ['licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]],
                    'licence' => null,
                ],
            ],
            [
                false,
                ['application' => null, 'licence' => null]
            ],
            [
                false,
                ['application' => ['licenceType' => 'X'], 'application' => ['licenceType' => 'Z']]
            ],
            [
                false,
                [
                    'application' => ['licenceType' => ['id' => Licence::LICENCE_TYPE_RESTRICTED]],
                    'licence' => ['licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]],
                ],
            ],
        ];
    }
}
