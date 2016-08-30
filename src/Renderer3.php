<?php

namespace B4nan\Forms;

use B4nan\Forms\Controls\CKEditor;
use Nette\Forms\Rendering\DefaultFormRenderer,
	Nette\Forms\Controls,
	Nette\Utils\Html,
	Nette;

/**
 * @author Martin Adámek <adamek@bargency.com>
 */
class Renderer3 extends DefaultFormRenderer
{

	/**
	 * @param Nette\Forms\Form $form
	 */
	public function __construct(Nette\Forms\Form $form)
	{
		$this->form = $form;

		// setup form rendering
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class=form-group';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = 'div class=col-sm-5';
		$this->wrappers['label']['container'] = 'div class="col-sm-2 control-label"';
		$this->wrappers['control']['description'] = 'span class=help-block';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-block';
	}

	/**
	 * Provides complete form rendering.
	 *
	 * @param  \Nette\Forms\Form
	 * @param  string 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(\Nette\Forms\Form $form, $mode = NULL)
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
			} elseif ($control instanceof Controls\Checkbox) {
				$control->getLabelPrototype()->addClass($control->getControlPrototype()->type);
			} elseif ($control instanceof Controls\RadioList) {
				$control->getItemLabelPrototype()->addClass($control->getControlPrototype()->type . '-inline');
				$control->getSeparatorPrototype()->setName(NULL);
			} else {
				$control->getControlPrototype()->addClass('form-control');
			}
		}

		return parent::render($form, $mode);
	}

	/**
	 * Renders single visual row.
	 * @return string
	 */
	public function renderPair(\Nette\Forms\IControl $control)
	{
		$pair = $this->getWrapper('pair container');
		$l = $this->renderLabel($control);
		if (Form::getOption('renderColonSuffix')) {
			$l= Nette\Utils\Strings::replace($l, '/<\/label>/i', ':</label>');
		}
		$pair->add($l);

		if ($control instanceof CKEditor) {
			$controlContainer = $this->wrappers['control']['container'];
			$this->wrappers['control']['container'] = 'div class=col-sm-10';
		}

		/** @var Html $controlPart */
		$controlPart = $this->renderControl($control);

		if ($control instanceof CKEditor) {
			$this->wrappers['control']['container'] = $controlContainer;
		}

		if ($control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
			foreach ($controlPart->getChildren() as & $item) {
				$class = $control->getControlPrototype()->type . '-inline';
				$item = Nette\Utils\Strings::replace($item, '/<label>/i', '<label class="' . $class . '">');
			}
		}

		// input append/prepend
		$prepend = $control->getOption('input-prepend');
		$append = $control->getOption('input-append');
		if ($prepend || $append) {
			$inner = Html::el('div')->addClass('input-group');
			if ($prepend) {
				$prepend = Html::el('div')->addClass('input-group-addon')->setText($prepend);
			}
			if ($append) {
				$append = Html::el('div')->addClass('input-group-addon')->setText($append);
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
	 */
	public function renderButtons(Nette\Forms\Form $form)
	{
		$wrapper = Html::el('div', ['class' => 'form-actions']);

		$usedPrimary = FALSE;
		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button && !$control->getOption('rendered')) {
				$control->getControlPrototype()->addClass(!$usedPrimary ? 'btn btn-success' : 'btn');
				$usedPrimary = TRUE;
				$wrapper->add($control->getControl());
			}
		}

		echo $wrapper;
	}

}
