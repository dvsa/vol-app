<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\Custom\EcmtCandidatePermitSelectionValidatingElement;
use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Permits\Data\Mapper\CandidatePermitSelection;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * CandidatePermitSelectionTest
 */
class CandidatePermitSelectionTest extends TestCase
{
    public function testMapForFormOptions(): void
    {
        $unpaginatedUnpaidPermits = [
            'results' => [
                [
                    'id' => 123,
                    'wanted' => true,
                ],
                [
                    'id' => 456,
                    'wanted' => false,
                ],
                [
                    'id' => 789,
                    'wanted' => true,
                ]
            ]
        ];

        $data = [
            'unpaginatedUnpaidPermits' => $unpaginatedUnpaidPermits
        ];

        $tableMarkup = '<table>' .
            '<tr><th>Header 1</th><th>Header 2</th><th>Header 3</th>' .
            '<tr><td>abc</td><td>def</td><td>{checkboxPlaceholder}</td></tr>' .
            '<tr><td>ghi</td><td>jkl</td><td>{checkboxPlaceholder}</td></tr>' .
            '<tr><td>mno</td><td>pqr</td><td>{checkboxPlaceholder}</td></tr>' .
            '</table>';

        $tableBuilder = m::mock(TableBuilder::class);
        $tableBuilder->shouldReceive('__toString')
            ->withNoArgs()
            ->andReturn($tableMarkup);

        $tableFactory = m::mock(TableFactory::class);
        $tableFactory->shouldReceive('prepareTable')
            ->with('candidate-permits-selection', $unpaginatedUnpaidPermits)
            ->once()
            ->andReturn($tableBuilder);

        $fieldset = m::mock(Fieldset::class);
        $htmlAdder = m::mock(HtmlAdder::class);

        $ecmtCandidatePermitSelectionValidatingElementParams = [
            'type' => EcmtCandidatePermitSelectionValidatingElement::class,
            'name' => 'candidatePermitSelectionValidator'
        ];

        $fieldset->shouldReceive('add')
            ->with($ecmtCandidatePermitSelectionValidatingElementParams)
            ->once()
            ->globally()
            ->ordered();

        $htmlAdder->shouldReceive('add')
            ->with(
                $fieldset,
                'table1',
                '<table><tr><th>Header 1</th><th>Header 2</th><th>Header 3</th><tr><td>abc</td><td>def</td><td>'
            )
            ->once()
            ->globally()
            ->ordered();

        $checkboxParams = [
            'type' => SingleCheckbox::class,
            'name' => 'candidate-123',
            'attributes' => [
                'class' => 'govuk-checkboxes__input',
                'id' => 'candidate-123',
                'value' => '1',
                'data-container-class' => 'govuk-checkboxes__item',
            ],
            'options' => [
                'label' => '<span class="govuk-visually-hidden">Select permit 1</span>',
                'label_attributes' => [
                    'class' => 'form-control form-control--checkbox form-control--advanced'
                ],
                'label_options' => [
                    'disable_html_escape' => true
                ],
                'checked_value' => '1',
                'unchecked_value' => '0',
            ]
        ];

        $fieldset->shouldReceive('add')
            ->with($checkboxParams)
            ->once()
            ->globally()
            ->ordered();

        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'table2', '</td></tr><tr><td>ghi</td><td>jkl</td><td>')
            ->once()
            ->globally()
            ->ordered();

        $checkboxParams = [
            'type' => SingleCheckbox::class,
            'name' => 'candidate-456',
            'attributes' => [
                'class' => 'govuk-checkboxes__input',
                'id' => 'candidate-456',
                'value' => '0',
                'data-container-class' => 'govuk-checkboxes__item',
            ],
            'options' => [
                'label' => '<span class="govuk-visually-hidden">Select permit 2</span>',
                'label_attributes' => [
                    'class' => 'form-control form-control--checkbox form-control--advanced'
                ],
                'label_options' => [
                    'disable_html_escape' => true
                ],
                'checked_value' => '1',
                'unchecked_value' => '0',
            ]
        ];

        $fieldset->shouldReceive('add')
            ->with($checkboxParams)
            ->once()
            ->globally()
            ->ordered();

        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'table3', '</td></tr><tr><td>mno</td><td>pqr</td><td>')
            ->once()
            ->globally()
            ->ordered();

        $checkboxParams = [
            'type' => SingleCheckbox::class,
            'name' => 'candidate-789',
            'attributes' => [
                'class' => 'govuk-checkboxes__input',
                'id' => 'candidate-789',
                'value' => '1',
                'data-container-class' => 'govuk-checkboxes__item',
            ],
            'options' => [
                'label' => '<span class="govuk-visually-hidden">Select permit 3</span>',
                'label_attributes' => [
                    'class' => 'form-control form-control--checkbox form-control--advanced'
                ],
                'label_options' => [
                    'disable_html_escape' => true
                ],
                'checked_value' => '1',
                'unchecked_value' => '0',
            ]
        ];

        $fieldset->shouldReceive('add')
            ->with($checkboxParams)
            ->once()
            ->globally()
            ->ordered();

        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'table4', '</td></tr></table>')
            ->once()
            ->globally()
            ->ordered();

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->andReturn($fieldset);

        $candidatePermitSelection = new CandidatePermitSelection($htmlAdder, $tableFactory);

        $this->assertEquals(
            $data,
            $candidatePermitSelection->mapForFormOptions($data, $form)
        );
    }
}
