<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper;

use Admin\Data\Mapper\MessagingSubject as Sut;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class MessagingSubjectTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $data = [
            'id' => 5,
            'version' => 2,
            'description' => 'Licence query',
            'category' => ['id' => 1, 'description' => 'Licensing'],
            'subCategory' => ['id' => 3],
            'isActive' => true,
        ];

        $expected = [
            'details' => [
                'id' => 5,
                'version' => 2,
                'description' => 'Licence query',
                'category' => 1,
                'subCategory' => 3,
                'isActive' => '1',
            ],
        ];

        $this->assertSame($expected, Sut::mapFromResult($data));
    }

    public function testMapFromResultWithoutSubCategoryOrActive(): void
    {
        $data = [
            'id' => 5,
            'version' => 2,
            'description' => 'Licence query',
            'category' => ['id' => 1],
            'subCategory' => null,
            'isActive' => false,
        ];

        $result = Sut::mapFromResult($data);

        $this->assertNull($result['details']['subCategory']);
        $this->assertSame('0', $result['details']['isActive']);
    }

    public function testMapFromResultForNewRecordStartsActive(): void
    {
        $this->assertSame(['details' => ['isActive' => '1']], Sut::mapFromResult([]));
    }

    public function testMapFromFormSendsRealBooleans(): void
    {
        $form = [
            'details' => [
                'id' => '5',
                'version' => '2',
                'description' => 'Licence query',
                'category' => '1',
                'subCategory' => '',
                'isActive' => '0',
            ],
        ];

        $result = Sut::mapFromForm($form);

        $this->assertFalse($result['isActive']);
        $this->assertSame('Licence query', $result['description']);
        $this->assertSame('1', $result['category']);

        $form['details']['isActive'] = '1';
        $this->assertTrue(Sut::mapFromForm($form)['isActive']);
    }
}
