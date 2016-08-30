<?php

namespace B4nan\Forms;

use Nette\Forms\Container as NContainer;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Container extends NContainer
{

	use FormControlTrait;

	/**
	 * Register
	 */
	public static function register()
	{
		NContainer::extensionMethod('addDate', function (NContainer $_this, $name, $label = NULL, $dateFormat = 'j.n.Y') {
			return $_this[$name] = new Controls\DatePicker($dateFormat, $label);
		});
	}

}
