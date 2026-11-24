<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\MessagingSubjectController;
use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Data\SubCategory;

final class MessagingSubjectControllerTest extends MockeryTestCase
{
    private SubCategory&m\MockInterface $subCategory;

    private MessagingSubjectController $sut;

    public function setUp(): void
    {
        $this->subCategory = m::mock(SubCategory::class);

        $this->sut = new MessagingSubjectController(
            m::mock(TranslationHelperService::class),
            m::mock(FormHelperService::class),
            m::mock(FlashMessengerHelperService::class),
            m::mock(Navigation::class),
            $this->subCategory
        );
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
}
