<?php

namespace B4nan\Forms\Controls;

use Nette\Forms\Controls\TextArea,
	Nette\Utils\Html;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class CKEditor extends TextArea
{

    /**
     * Generates control's HTML element.
     *
     * @return Html
     */
    public function getControl()
    {
        $control = parent::getControl();
        $control->class = 'ckeditor';

        $render = Html::el('div')->class('ckeditor_control')
                                 ->add($control);

        return $render;
    }

}
