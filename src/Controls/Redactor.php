<?php

namespace B4nan\Forms\Controls;

use Nette\Forms\Controls\TextArea,
	Nette\Utils\Html;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Redactor extends TextArea
{

    /**
     * Generates control's HTML element.
     *
     * @return Html
     */
    public function getControl()
    {
        $control = parent::getControl();
        $control->class = 'redactor';

        $render = Html::el('div')->class('redactor_control')
                                 ->add($control);

        return $render;
    }

}
