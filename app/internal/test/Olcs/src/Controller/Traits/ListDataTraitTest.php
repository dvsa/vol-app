<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversTrait(\Olcs\Controller\Traits\ListDataTrait::class)]
final class ListDataTraitTest extends MockeryTestCase
{
    /**
     * @var \OlcsTest\Controller\Traits\Stub\StubListDataTrait
     */
    private $sut;
    /** @var  m\MockInterface */
    private $mockResponse;

    #[\Override]
    public function setUp(): void
    {
        $this->mockResponse = m::mock(\Common\Service\Cqrs\Response::class);

        $this->sut = new \OlcsTest\Controller\Traits\Stub\StubListDataTrait();
        $this->sut->setHandleQueryResponse($this->mockResponse);
    }

    public function testGetListDataEnforcementArea(): void
    {
        $this->mockResponse->shouldReceive('isOk')->andReturn(true);
        $this->mockResponse->shouldReceive('getResult')->andReturn(
            [
                'trafficAreaEnforcementAreas' => [
                    ['enforcementArea' => ['id' => 'V15', 'name' => 'Foo']],
                    ['enforcementArea' => ['id' => 'X16', 'name' => 'Bar']],
                ]
            ]
        );

        $result = $this->sut->getListDataEnforcementArea('X', 'Option1');

        $this->assertSame(['' => 'Option1', 'V15' => 'Foo', 'X16' => 'Bar'], $result);
    }

    public function testGetListDataEnforcementAreaError(): void
    {
        $this->mockResponse->shouldReceive('isOk')->andReturn(false);

        $result = $this->sut->getListDataEnforcementArea('X');

        $this->assertSame([], $result);
    }

    public function testGetListDataOptionsApiErr(): void
    {
        $this->mockResponse->shouldReceive('isOk')->andReturn(false);

        $this->assertEquals([], $this->sut->getListDataUser());
    }

    public function testGetListDataOptions(): void
    {
        $respResult = [
            'results' => [
                [
                    'id' => 'unit_Id1',
                    'loginId' => 'unit_Val1',
                ],
            ],
        ];

        $this->mockResponse
            ->shouldReceive('isOk')->once()->andReturn(true)
            ->shouldReceive('getResult')->once()->andReturn($respResult);

        $actual = $this->sut->getListDataUser(null, ['unit_Key' => 'unit_Val']);

        $this->assertEquals([
            'unit_Key' => 'unit_Val',
            'unit_Id1' => 'unit_Val1',
        ], $actual);
    }

    /**
     * Several legacy templates point at one letter type -- the letters engine's choices adapt
     * a single type at generation time, so the list must offer that journey once, labelled
     * with the letter type's own name rather than one of the RTF-era descriptions.
     */
    public function testDocTemplatesLinkedToTheSameLetterTypeCollapseToOneEntry(): void
    {
        $this->mockResponse->shouldReceive('getResult')->andReturn(
            ['isEnabled' => true],
            ['results' => [
                ['id' => 7, 'description' => 'GV - 1st request for supporting docs',
                    'letterType' => ['id' => 7, 'name' => 'First and Finals GB']],
                ['id' => 8, 'description' => 'GV - final request for supporting docs',
                    'letterType' => ['id' => 7, 'name' => 'First and Finals GB']],
                ['id' => 12, 'description' => 'A legacy RTF template'],
            ]]
        );
        $this->mockResponse->shouldReceive('isOk')->andReturn(true);

        $result = $this->sut->getListDataDocTemplates(9, 31, 'Please select');

        $this->assertSame(
            [
                '' => 'Please select',
                7 => '[New] First and Finals GB',
                12 => 'A legacy RTF template',
            ],
            $result
        );
    }

    /**
     * Regenerating a document must keep its stored template selectable. By the time the form
     * re-validates, the original document has already been deleted -- so if the stored id is a
     * sibling that consolidation dropped, the browser silently falls back to a different
     * template or the POST fails InArray validation. The kept row therefore stands as its
     * letter type's single representative instead of the first row in description order.
     */
    public function testKeepTemplateIdSurvivesTheLetterTypeCollapse(): void
    {
        $this->mockResponse->shouldReceive('getResult')->andReturn(
            ['isEnabled' => true],
            ['results' => [
                ['id' => 7, 'description' => 'GV - 1st request for supporting docs',
                    'letterType' => ['id' => 7, 'name' => 'First and Finals GB']],
                ['id' => 8, 'description' => 'GV - final request for supporting docs',
                    'letterType' => ['id' => 7, 'name' => 'First and Finals GB']],
                ['id' => 12, 'description' => 'A legacy RTF template'],
            ]]
        );
        $this->mockResponse->shouldReceive('isOk')->andReturn(true);

        $result = $this->sut->getListDataDocTemplates(9, 31, 'Please select', 8);

        $this->assertSame(
            [
                '' => 'Please select',
                8 => '[New] First and Finals GB',
                12 => 'A legacy RTF template',
            ],
            $result
        );
    }

    public function testDocTemplatesAreUntouchedWhenTheFeatureIsOff(): void
    {
        $this->mockResponse->shouldReceive('getResult')->andReturn(
            ['isEnabled' => false],
            ['results' => [
                ['id' => 7, 'description' => 'GV - 1st request',
                    'letterType' => ['id' => 7, 'name' => 'First and Finals GB']],
                ['id' => 8, 'description' => 'GV - final request',
                    'letterType' => ['id' => 7, 'name' => 'First and Finals GB']],
            ]]
        );
        $this->mockResponse->shouldReceive('isOk')->andReturn(true);

        $result = $this->sut->getListDataDocTemplates(9, 31);

        $this->assertSame([7 => 'GV - 1st request', 8 => 'GV - final request'], $result);
    }
}
