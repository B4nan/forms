<?php

namespace Bargency\Forms\Controls;

use Nette\Utils\Html,
	Nette\Forms\Helpers,
	Nette\Forms\Controls\TextInput;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class EditableSelectBox extends TextInput
{

	/** validation rule */
	const VALID = ':editableSelectBoxValid';

	/** @var array of option / optgroup */
	private $options = array();

	/** @var mixed */
	private $prompt = FALSE;

	/** @var mixed */
	private $other = FALSE;

	/**
	 * @param string $label
	 * @param array $items
	 * @param mixed $other defaults to last item
	 */
	public function __construct($label = NULL, array $items = NULL, $other = NULL)
	{
		parent::__construct($label);

		if ($items !== NULL) {
			$this->setItems($items);
		}

		if ($other) {
			$this->setOther($other);
		}
	}

	/**
	 * Sets first prompt item in select box.
	 * @param  string
	 * @return self
	 */
	public function setPrompt($prompt)
	{
		$this->prompt = $prompt;
		return $this;
	}

	/**
	 * Returns first prompt item?
	 * @return mixed
	 */
	public function getPrompt()
	{
		return $this->prompt;
	}

	/**
	 * @param  string
	 * @return self
	 */
	public function setOther($other)
	{
		$this->other = $other;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOther()
	{
		return $this->other;
	}

	/**
	 * Sets options and option groups from which to choose.
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$useKeys) {
			$res = array();
			foreach ($items as $key => $value) {
				unset($items[$key]);
				if (is_array($value)) {
					foreach ($value as $val) {
						$res[$key][(string) $val] = $val;
					}
				} else {
					$res[(string) $value] = $value;
				}
			}
			$items = $res;
		}
		$this->options = $items;

		if (count($items)) {
			// other defaults to last item
			$keys = array_keys($items);
			$this->setOther(end($keys));
		}

		$this->items = $useKeys ? $items : array_combine($items, $items);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->options;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return is_string($this->value) && $this->value ? $this->value : NULL;
	}

	/**
	 * @return Nette\Utils\Html
	 */
	public function getControlSelect()
	{
		$items = $this->prompt === FALSE ? array() : array('' => $this->translate($this->prompt));
		foreach ($this->options as $key => $value) {
			$items[is_array($value) ? $this->translate($key) : $key] = $this->translate($value);
		}

		$attrs = parent::getControl()->attrs;
		$attrs = array(
			'name' => $attrs['name'] . '__eselect',
			'id' => $attrs['id'] . '-select',
			'required' => $attrs['required'],
			'disabled' => $attrs['disabled'],
			'data-nette-rules' => $attrs['data-nette-rules'],
		);
		$opts = array(
			'selected?' => $this->value,
			'disabled:' => is_array($this->disabled) ? $this->disabled : NULL,
		);

		return Helpers::createSelectBox($items, $opts)
						->addAttributes($attrs);
	}

	/**
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		$select = $this->getControlSelect();
		$text = parent::getControl()->addAttributes(array('tabindex' => -1));

		if ($this->disabled) {
			$text->setDisabled($this->disabled);
		}

		$container = Html::el('div');
		$container->data['other'] = $this->other;
		$container->addClass('editable-select');
		$container->add($select);
		$container->add($text);

		return $container;
	}

	/**
	 * @return Html
	 */
	public function getLabel($caption = NULL)
	{
		$label = parent::getLabel($caption);
		$label->for = NULL;

		return $label;
	}

}
