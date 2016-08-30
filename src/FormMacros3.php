<?php

namespace B4nan\Forms;

use Nette,
	Nette\Forms\Controls,
	Latte,
	Latte\MacroNode,
	Latte\PhpWriter,
	Latte\CompileException;

/**
 * @author Martin Adamek <adamek@bargency.com>
 */
class FormMacros3 extends Nette\Bridges\FormsLatte\FormMacros
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('form', [$me, 'macroForm'], 'Nette\Bridges\FormsLatte\FormMacros::renderFormEnd($_form)');
		$me->addMacro('pair', [$me, 'macroPair']);
		$me->addMacro('body', [$me, 'macroBody']);
		$me->addMacro('buttons', [$me, 'macroButtons']);
		$me->addMacro('errors', [$me, 'macroErrors']);
		$me->addMacro('labelWrap', [$me, 'macroLabelWrap'], [$me, 'macroLabelWrapEnd']);
	}

	/**
	 * {body ...}
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 * @throws CompileException
	 */
	public function macroBody(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing form name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write('$_form->render("body")');
	}

	/**
	 * {form ...}
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 * @throws CompileException
	 */
	public function macroForm(MacroNode $node, PhpWriter $writer)
	{
		if ($node->htmlNode && strtolower($node->htmlNode->name) === 'form') {
			throw new CompileException('Did you mean <form n:name=...> ?');
		}
		$name = $node->tokenizer->fetchWord();
		if ($name === FALSE) {
			throw new CompileException("Missing form name in {{$node->name}}.");
		}
		$node->tokenizer->reset();
		return $writer->write(
			'B4nan\Forms\FormMacros::renderFormBegin($form = $_form = '
			. ($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '')
			. '$_control[%node.word], %node.array)'
		);
	}

	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroPair(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo $_form->getRenderer()->renderPair($_form[%node.word])');
	}

	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroButtons(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('$_form->getRenderer()->renderButtons($_form)');
	}

	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 */
	public function macroErrors(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo $_form->getRenderer()->renderErrors()');
	}

	/**
	 * @return void
	 */
	public static function setFormClasses(Nette\Forms\Form $form)
	{
		// form class
		$form->getElementPrototype()->addClass('form-horizontal');

		// add submit, label, radio and checkbox classes
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
			} elseif ($control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getItemLabelPrototype()->addClass($control->getControlPrototype()->type . '-inline');
				$control->getSeparatorPrototype()->setName(NULL);
			} else {
				$control->getControlPrototype()->addClass('form-control');
			}
		}
	}

	/**
	 * @return void
	 */
	public static function renderFormBegin(Nette\Forms\Form $form, array $attrs, $withTags = TRUE)
	{
		self::setFormClasses($form);
		parent::renderFormBegin($form, $attrs, $withTags);
	}

	/**
	 * {labelWrap ...}
	 */
	public function macroLabelWrap(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$' ? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word]; if ($_label = $_input' : 'if ($_label = $_form[%0.word]')
			. '->%1.raw) echo $_label'
			. ($node->tokenizer->isNext() ? '->addAttributes(%node.array)' : ''),
			$name,
			$words ? ('getLabelPart(' . implode(', ', array_map([$writer, 'formatWord'], $words)) . ')') : 'getLabel()'
		);
	}

	/**
	 * {/labelWrap}
	 */
	public function macroLabelWrapEnd(MacroNode $node, PhpWriter $writer)
	{
		$node->openingCode = '<div class="col-sm-2 control-label">' . $node->openingCode;
		$node->openingCode = rtrim($node->openingCode, '?> ') . '->startTag() ?>';
		if ($node->content === NULL) {
			return $writer->write(
				'echo $_label'
				. ($node->tokenizer->isNext() ? '->addAttributes(%node.array)' : '')
				. '->getText()' . (! Form::getOption('renderColonSuffix') ? '' : ' . ":"') . ' . $_label->endTag() . "</div>"'
			);
		} else {
			return $writer->write('if ($_label) echo ' . (! Form::getOption('renderColonSuffix') ?: '":" . ') . '$_label->endTag() . "</div>"');
		}
	}

}
