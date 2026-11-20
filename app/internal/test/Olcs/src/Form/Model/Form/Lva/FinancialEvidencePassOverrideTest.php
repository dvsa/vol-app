<?php

declare(strict_types=1);

namespace OlcsTest\Form\Model\Form\Lva;

use Common\Validator\ValidateIf;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Element\Textarea;
use Laminas\Form\FormInterface;
use Laminas\Validator\ValidatorChain;
use Olcs\Form\Model\Form\Lva\FinancialEvidencePassOverride;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validates the real form, built from its attributes. The controller tests use a mocked form, so
 * they cannot see what the form itself accepts or says. Every rule the caseworker can fall foul
 * of lives in the form, with its message, so it is all checked here.
 */
final class FinancialEvidencePassOverrideTest extends TestCase
{
    /** What the caseworker can legitimately save. */
    #[DataProvider('acceptedProvider')]
    public function testAcceptsAValidAnswer(string $answer, string $comment): void
    {
        $form = $this->form();
        $form->setData(['analysisId' => '9', 'row' => 'name', 'changeRecommendation' => $answer, 'comment' => $comment]);

        $this->assertTrue($form->isValid(), json_encode($form->getMessages()));
        $this->assertSame(trim($comment), $form->getData()['comment']);
    }

    public static function acceptedProvider(): \Iterator
    {
        // "No" means the analyser's outcome stands, so it needs no text.
        yield 'no, nothing to say' => ['N', ''];
        yield 'no, with a note' => ['N', ' a note '];
        yield 'yes, with a reason' => ['Y', 'Reconsidered'];
        yield 'yes, at the length limit' => ['Y', str_repeat('a', 1000)];
    }

    /**
     * Everything the caseworker can get wrong is caught here, by the form, with a message worded
     * for them against the field it is about. The controller shows the form's own messages; it
     * has no validation rules of its own.
     */
    #[DataProvider('caseworkerErrorProvider')]
    public function testSaysWhatTheCaseworkerNeedsToPutRight(array $data, string $field, string $message): void
    {
        $form = $this->form();
        $form->setData($data + ['analysisId' => '9', 'row' => 'name', 'changeRecommendation' => 'Y', 'comment' => 'x']);

        $this->assertFalse($form->isValid());
        $this->assertSame([$field], array_keys($form->getMessages()));
        $this->assertSame([$message], array_values($form->getMessages()[$field]));
    }

    public static function caseworkerErrorProvider(): \Iterator
    {
        yield 'no answer' => [['changeRecommendation' => ''], 'changeRecommendation', 'Select yes if you want to change the outcome of this issue'];
        // Changing a pass to a fail always needs a reason.
        yield 'yes, no reason' => [['comment' => ''], 'comment', 'You must provide explanatory text.'];
        yield 'yes, only spaces' => [['comment' => '   '], 'comment', 'You must provide explanatory text.'];
        yield 'yes, too long' => [
            ['comment' => str_repeat('a', 1001)],
            'comment',
            'More detail must be 1,000 characters or fewer',
        ];
        yield 'no, note too long' => [
            ['changeRecommendation' => 'N', 'comment' => str_repeat('a', 1001)],
            'comment',
            'More detail must be 1,000 characters or fewer',
        ];
    }

    /** Hidden fields the caseworker never types; a bad value here is a tampered or broken form. */
    #[DataProvider('brokenFormProvider')]
    public function testRejectsABrokenForm(array $data, string $field): void
    {
        $form = $this->form();
        $form->setData($data + ['analysisId' => '9', 'row' => 'name', 'changeRecommendation' => 'Y', 'comment' => 'x']);

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey($field, $form->getMessages());
    }

    public static function brokenFormProvider(): \Iterator
    {
        yield 'an analysis id that is not a number' => [['analysisId' => 'x'], 'analysisId'];
        // Bank carries no flag, so it is not a check that can be changed.
        yield 'a row that is not a check' => [['row' => 'bank'], 'row'];
    }

    private function form(): FormInterface
    {
        $builder = new AttributeBuilder();

        // The application registers these aliases; a bare builder needs telling.
        $elements = $builder->getFormFactory()->getFormElementManager();
        $elements->setAllowOverride(true);
        $elements->setAlias('TextArea', Textarea::class);

        $chain = new ValidatorChain();
        $validators = $chain->getPluginManager();
        $validators->setInvokableClass(ValidateIf::class);
        $validators->setAlias('ValidateIf', ValidateIf::class);
        $builder->getFormFactory()->getInputFilterFactory()->setDefaultValidatorChain($chain);

        return $builder->createForm(FinancialEvidencePassOverride::class);
    }
}
