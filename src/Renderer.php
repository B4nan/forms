<?php

namespace Bargency\Forms;

use Nette\Forms\Rendering\DefaultFormRenderer,
	Nette\Forms\Controls,
	Nette\Utils\Html,
	Nette;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Renderer extends DefaultFormRenderer
{

	/**
	 * @param Nette\Forms\Form $form
	 */
	public function __construct(Nette\Forms\Form $form)
	{
		$this->form = $form;

		// setup form rendering
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class=control-group';
		$this->wrappers['pair']['.error'] = 'error';
		$this->wrappers['control']['container'] = 'div class=controls';
		$this->wrappers['label']['container'] = 'div class="control-label"';
		$this->wrappers['label']['.required'] = 'required';
		$this->wrappers['control']['description'] = 'span class=help-inline';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-inline';

		if ($form::getOption('renderColonSuffix')) {
			$this->wrappers['label']['suffix'] = ':';
		}
	}

	/**
	 * Provides complete form rendering.
	 *
	 * @param  Nette\Forms\Form
	 * @param  string 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL)
	{
		// make form and controls compatible with Twitter Bootstrap
		$form->getElementPrototype()->class[] = 'form-horizontal';

		$usedPrimary = FALSE;
		foreach ($form->getControls() as $control) {
			$label = $control->getLabelPrototype();
			if ($label) {
				if ($control->isRequired()) {
					$label->addClass('required');
				}
			}

			if ($control instanceof Controls\Button) {
				$control->getControlPrototype()->addClass(!$usedPrimary ? 'btn btn-success' : 'btn');
				$usedPrimary = TRUE;
			} elseif ($control instanceof Controls\RadioList) {
				$control->getSeparatorPrototype()->setName(NULL);
				$control->getItemLabelPrototype()->addClass($control->controlPrototype->type);
			}
		}

		if ($this->form !== $form) {
			$this->form = $form;
			$this->init();
		}

		$s = '';
		if (!$mode || $mode === 'begin') {
			$s .= $this->renderBegin();
		}
		if (!$mode || strtolower($mode) === 'ownerrors') {
			$s .= $this->renderErrors();
		} elseif ($mode === 'errors') {
			$s .= $this->renderErrors(NULL, FALSE);
		}
		if (!$mode) {
			foreach ($form->getControls() as $control) {
				if ($control instanceof Controls\Button) {
					$control->setOption('rendered', TRUE);
				}
			}
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody();
		}
		if (!$mode) {
			foreach ($form->getControls() as $control) {
				if ($control instanceof Controls\Button) {
					$control->setOption('rendered', FALSE);
				}
			}
		}
		if (!$mode || $mode === 'buttons') {
			$s .= $this->renderButtons($form, 0);
		}
		if (!$mode || $mode === 'end') {
			$s .= $this->renderEnd();
		}
		return $s;
	}

	/**
	 * Renders single visual row.
	 * @return string
	 */
	public function renderPair(Nette\Forms\IControl $control)
	{
		$pair = $this->getWrapper('pair container');
		$pair->add($this->renderLabel($control));

		$controlPart = $this->renderControl($control);
		if ($control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
			foreach ($controlPart->getChildren() as & $item) {
				$class = $control->getControlPrototype()->type;
				$item = Nette\Utils\Strings::replace($item, '/<label/i', '<label class="' . $class . '"');
			}
		}

		// input append/prepend
		$prepend = $control->getOption('input-prepend');
		$append = $control->getOption('input-append');
		if ($prepend || $append) {
			$inner = Html::el('div');
			if ($prepend = $control->getOption('input-prepend')) {
				$inner->addClass('input-prepend');
			}
			if ($append = $control->getOption('input-append')) {
				$inner->addClass('input-append');
			}
			$html = $prepend . $controlPart->getHtml() . $append;
			$inner->setHtml($html);
			$controlPart->removeChildren();
			$controlPart->add($inner);
		}

		$pair->add($controlPart);
		$pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), TRUE);
		$pair->class($control->hasErrors() ? $this->getValue('pair .error') : NULL, TRUE);
		$pair->class($control->getOption('class'), TRUE);
		if (++$this->counter % 2) {
			$pair->class($this->getValue('pair .odd'), TRUE);
		}
		$pair->id = $control->getHtmlId() . '-pair';
		return $pair->render(0);
	}

	/**
	 * @param Nette\Forms\Form $form
	 * @param bool $echo
	 * @return string
	 */
	public function renderButtons(Nette\Forms\Form $form, $echo = TRUE)
	{
		$wrapper = Html::el('div', array('class' => 'form-actions'));

		$usedPrimary = FALSE;
		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button && !$control->getOption('rendered')) {
				$control->getControlPrototype()->addClass(!$usedPrimary ? 'btn btn-success' : 'btn');
				$usedPrimary = TRUE;
				$wrapper->add($control->getControl());
			}
		}

		if ($echo) {
			echo $wrapper;
		} else {
			return $wrapper->render(0);
		}
	}

}
