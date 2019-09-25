<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Form;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Permits\Data\Mapper\ChangeLicence;
use Zend\Form\Fieldset;

class ChangeLicenceTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function testMapForFormOptions()
    {
        $confirmChangeLabel = 'confirm change label';
        $licNo = 'OB1234567';

        $translationHelperService = m::mock(TranslationHelperService::class);
        $translationHelperService->shouldReceive('translateReplace')
            ->once()
            ->with(ChangeLicence::CHANGE_LICENCE_LABEL, [$licNo])
            ->andReturn($confirmChangeLabel);
        $sut = new ChangeLicence($translationHelperService);

        $licenceId = 7;
        $inputData = [
            'licencesAvailable' => [
                'eligibleLicences' => [
                    $licenceId => [
                        'licNo' => $licNo,
                    ],
                ],
            ],
            'licence' => $licenceId
        ];

        $expectedFormData = ['fields' => ['licence' => $licenceId]];

        $mockCheckbox = m::mock(SingleCheckbox::class);
        $mockCheckbox->shouldReceive('setLabel')->once()->with($confirmChangeLabel);

        $mockFieldSet = m::mock(Fieldset::class);
        $mockFieldSet->shouldReceive('get')->once()->with('ConfirmChange')->andReturn($mockCheckbox);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturn($mockFieldSet);
        $mockForm->shouldReceive('setData')->once()->with($expectedFormData);

        $this->assertEquals(
            $inputData,
            $sut->mapForFormOptions($inputData, $mockForm)
        );
    }
}
