<?php

namespace CommonTest\Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\ContactDetails;
use Dvsa\Olcs\Transfer\Query\ContactDetail\ContactDetailsList as Qry;
use Mockery as m;

/**
 * @covers \Common\Service\Data\ContactDetails
 */
class ContactDetailsTest extends AbstractListDataServiceTestCase
{
    /** @var ContactDetails */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new ContactDetails($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        $results = ['results' => 'results'];
        $params = [
            'sort'  => 'description',
            'order' => 'ASC',
            'page'  => null,
            'limit' => null,
            'contactType' => 'unit_ContactType',
        ];

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturnUsing(
                function (Qry $dto) use ($params) {
                    $this->assertEquals($params['sort'], $dto->getSort());
                    $this->assertEquals($params['order'], $dto->getOrder());
                    $this->assertEquals($params['page'], $dto->getPage());
                    $this->assertEquals($params['limit'], $dto->getLimit());
                    $this->assertEquals($params['contactType'], $dto->getContactType());
                    return $this->query;
                }
            );

        $mockResponse = m::mock()
            ->shouldReceive('isOk')->andReturn(true)->once()
            ->shouldReceive('getResult')->andReturn($results)->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->assertEquals($results['results'], $this->sut->fetchListData('unit_ContactType'));
    }

    public function testSetters(): void
    {
        static::assertNull($this->sut->getContactType());

        $this->sut->setContactType('unit_ContactType');

        static::assertEquals('unit_ContactType', $this->sut->getContactType());
    }

    public function testFetchListDataCache(): void
    {
        $data = [
            [
                'id' => 9999,
                'description' => 'EXPECTED'
            ],
        ];
        $this->sut->setData('ContactDetails', $data);

        static::assertEquals([9999 => 'EXPECTED'], $this->sut->fetchListOptions());
    }

    public function testFetchListDataWithException(): void
    {
        $this->expectException(DataServiceException::class);

        $this->transferAnnotationBuilder->shouldReceive('createQuery')
            ->with(m::type(Qry::class))
            ->once()
            ->andReturn($this->query);

        $mockResponse = m::mock()
            ->shouldReceive('isOk')
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->mockHandleQuery($mockResponse);

        $this->sut->setContactType('unit_ContactType');
        $this->sut->fetchListData([]);
    }
}
