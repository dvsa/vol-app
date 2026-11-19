<?php

declare(strict_types=1);

namespace OlcsTest\View;

use Common\Form\Form;
use DOMDocument;
use DOMXPath;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Element\Csrf;
use Laminas\Form\View\Helper\Form as FormHelper;
use Laminas\Form\View\Helper\FormHidden;
use Olcs\Form\Model\Form\Vehicle\AddVehicleSearch;
use Olcs\Form\Model\Form\Vehicle\ConfirmVehicle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AddVehiclePageTest extends TestCase
{
    #[DataProvider('vehicleDataProvider')]
    public function testFormAndSecurityIdsAreUnique(?array $vehicleData, int $expectedFormCount): void
    {
        $builder = new AttributeBuilder();
        $searchForm = $builder->createForm(AddVehicleSearch::class);
        $confirmationForm = $builder->createForm(ConfirmVehicle::class);
        foreach ([$searchForm, $confirmationForm] as $form) {
            $form->add(['type' => Csrf::class, 'name' => 'security']);
        }

        $view = new class ($searchForm, $confirmationForm, $vehicleData) {
            public string $title = 'Title';
            public string $licNo = 'Licence';
            public string $backLink = '/';
            public string $content = '';
            public ?string $note = null;
            public ?string $bottomLink = null;

            public function __construct(
                public Form $form,
                public Form $confirmationForm,
                public ?array $vehicleData,
            ) {
                $form->setAttribute('action', '/search');
                $confirmationForm->setAttribute('action', '/confirm');
            }

            public function render(): string
            {
                ob_start();
                include __DIR__ . '/../../../../module/Olcs/view/pages/licence/vehicle/add.phtml';
                return ob_get_clean();
            }

            public function form(Form $form): string
            {
                $formHelper = new FormHelper();
                $hiddenHelper = new FormHidden();
                return $formHelper->openTag($form)
                    . $hiddenHelper->render($form->get('security'))
                    . $formHelper->closeTag();
            }

            public function formErrors(Form $form): string
            {
                return '';
            }

            public function partial(string $template, array $variables): string
            {
                return '';
            }

            public function translate(string $message): string
            {
                return $message;
            }

            public function translateReplace(string $message, array $values): string
            {
                return $message;
            }
        };
        $html = $view->render();
        $document = new DOMDocument();
        self::assertTrue($document->loadHTML($html));
        $xpath = new DOMXPath($document);
        $forms = $xpath->query('//form');
        self::assertCount($expectedFormCount, $forms);

        $ids = [];
        foreach ($forms as $form) {
            $formId = $form->getAttribute('id');
            self::assertNotSame('', $formId);
            $ids[] = $formId;
            $security = $xpath->query('.//input[@name="security" and @type="hidden"]', $form);
            self::assertCount(1, $security);
            self::assertSame($formId . '-security', $security->item(0)->getAttribute('id'));
            $ids[] = $security->item(0)->getAttribute('id');
        }
        self::assertCount(count(array_unique($ids)), $ids);
    }

    public static function vehicleDataProvider(): array
    {
        return [
            'search' => [null, 1],
            'search results' => [['registrationNumber' => 'AB12CDE'], 2],
        ];
    }
}
