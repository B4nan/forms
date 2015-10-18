<?php

namespace Bargency\Forms\Controls;

use Nette\Forms\Controls\HiddenField as NHiddenField;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class HiddenField extends NHiddenField
{

	/**
	 * Generates control's HTML element.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->addAttributes(array(
			'id' => $this->getHtmlId(),
		));
		return $control;
	}

}
