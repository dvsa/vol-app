<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Type('\Common\Form\Elements\Types\RadioVertical')]
#[Form\Options(['radio-element' => 'goodsApplicationInterim'])]
class Interim
{
    #[Form\Name('goodsApplicationInterim')]
    #[Form\Attributes(['id' => 'interim[goodsApplicationInterim]', 'placeholder' => '', 'value' => 'N'])]
    #[Form\Options(['label' => 'interim.application.undertakings.form.checkbox.label', 'value_options' => [
        'Y' => ['label' => 'Yes', 'value' => 'Y', 'attributes' => ['id' => 'interim-goods-application-yes']],
        'N' => ['label' => 'No', 'value' => 'N', 'attributes' => ['id' => 'interim-goods-application-no']],
    ], 'label_attributes' => ['class' => 'form-control form-control--radio form-control--inline']])]
    #[Form\Type('\Common\Form\Elements\Types\Radio')]
    public $goodsApplicationInterim;

    #[Form\ComposedObject('\Common\Form\Model\Form\Lva\Fieldset\AuthorityToOperate')]
    public $YContent;
}
