Bargency/Forms
===========================


Installation
------------



```php
// bootstrap form macros
$latte = $template->getLatte();
$latte->onCompile[] = function($latte) {
	Bargency\Forms\FormMacros::install($latte->getCompiler());
};
```

