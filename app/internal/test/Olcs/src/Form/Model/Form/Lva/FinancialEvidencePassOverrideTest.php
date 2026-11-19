<?php

declare(strict_types=1);

namespace OlcsTest\Form\Model\Form\Lva;

use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Element\Textarea;
use Laminas\Form\FormInterface;
use Olcs\Form\Model\Form\Lva\FinancialEvidencePassOverride;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validates the real form, built from its attributes. The controller tests use a mocked form, so
 * they cannot see what the form itself accepts: an input with no Required attribute is required
 * by default, which once made every save with an empty text box fail as an unknown error before
 * the controller's own rules (and messages) were reached.
 */
final class FinancialEvidencePassOverrideTest extends TestCase
{
    /**
     * The form must let an empty text box through. Whether text is needed depends on the answer
     * (a reason for "Yes", nothing for "No"), and the controller decides that so it can say what
     * is missing.
     */
    #[DataProvider('acceptedProvider')]
    public function testAcceptsEachAnswerWithOrWithoutText(string $answer, string $comment): void
    {
        $form = $this->form();
        $form->setData(['analysisId' => '9', 'row' => 'name', 'changeRecommendation' => $answer, 'comment' => $comment]);

        $this->assertTrue($form->isValid(), json_encode($form->getMessages()));
        $this->assertSame(trim($comment), $form->getData()['comment']);
    }

    public static function acceptedProvider(): \Iterator
    {
        yield 'no, nothing to say' => ['N', ''];
        yield 'no, with a note' => ['N', ' a note '];
        yield 'yes, no reason yet' => ['Y', ''];
        yield 'yes, with a reason' => ['Y', 'Reconsidered'];
    }

    #[DataProvider('rejectedProvider')]
    public function testRejectsWhatCannotBeAnAnswer(array $data): void
    {
        $form = $this->form();
        $form->setData($data + ['analysisId' => '9', 'row' => 'name', 'changeRecommendation' => 'Y', 'comment' => 'x']);

        $this->assertFalse($form->isValid());
    }

    public static function rejectedProvider(): \Iterator
    {
        yield 'no answer' => [['changeRecommendation' => '']];
        yield 'an analysis id that is not a number' => [['analysisId' => 'x']];
        // Bank carries no flag, so it is not a check that can be changed.
        yield 'a row that is not a check' => [['row' => 'bank']];
        yield 'text over the limit' => [['comment' => str_repeat('a', 1001)]];
    }

    private function form(): FormInterface
    {
        $builder = new AttributeBuilder();

        // The application registers "TextArea" as an alias; a bare builder needs telling.
        $elements = $builder->getFormFactory()->getFormElementManager();
        $elements->setAllowOverride(true);
        $elements->setAlias('TextArea', Textarea::class);

        return $builder->createForm(FinancialEvidencePassOverride::class);
    }
}
