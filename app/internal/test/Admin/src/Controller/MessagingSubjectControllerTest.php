<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\MessagingSubjectController;
use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Data\SubCategory;

final class MessagingSubjectControllerTest extends MockeryTestCase
{
    private SubCategory&m\MockInterface $subCategory;

    private Params&m\MockInterface $params;

    private MessagingSubjectController&m\MockInterface $sut;

    public function setUp(): void
    {
        $this->subCategory = m::mock(SubCategory::class);

        $this->params = m::mock(Params::class);

        $this->sut = m::mock(
            MessagingSubjectController::class,
            [
                m::mock(TranslationHelperService::class),
                m::mock(FormHelperService::class),
                m::mock(FlashMessengerHelperService::class),
                m::mock(Navigation::class),
                $this->subCategory,
            ]
        )->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testAlterFormForEditFiltersSubCategoriesByCategory(): void
    {
        $form = m::mock(Form::class);

        $this->subCategory->shouldReceive('setCategory')->once()->with(12)->andReturnSelf();

        $this->assertSame($form, $this->sut->alterFormForEdit($form, ['details' => ['category' => 12]]));
    }

    public function testAlterFormForEditWithoutCategoryLeavesSubCategoriesAlone(): void
    {
        $form = m::mock(Form::class);

        $this->subCategory->shouldReceive('setCategory')->never();

        $this->assertSame($form, $this->sut->alterFormForEdit($form, ['details' => []]));
    }

    public function testAlterFormForAddFiltersSubCategoriesByPostedCategory(): void
    {
        $form = m::mock(Form::class);

        $this->sut->shouldReceive('params')->once()->withNoArgs()->andReturn($this->params);
        $this->params->shouldReceive('fromPost')->once()->withNoArgs()->andReturn(['details' => ['category' => '12']]);
        $this->subCategory->shouldReceive('setCategory')->once()->with('12')->andReturnSelf();

        $this->assertSame($form, $this->sut->alterFormForAdd($form, []));
    }

    public function testAlterFormForAddWithNothingPostedLeavesSubCategoriesAlone(): void
    {
        $form = m::mock(Form::class);

        $this->sut->shouldReceive('params')->once()->withNoArgs()->andReturn($this->params);
        $this->params->shouldReceive('fromPost')->once()->withNoArgs()->andReturn([]);
        $this->subCategory->shouldReceive('setCategory')->never();

        $this->assertSame($form, $this->sut->alterFormForAdd($form, []));
    }
}
