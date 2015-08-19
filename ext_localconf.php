<?php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
tx_powermail.flexForm.type.addFieldOptions.recaptcha = reCAPTCHA
', 43);


$signalSlotDispatcher->connect(
	In2code\Powermail\Domain\Validator\CustomValidator::class,
	'isValid',
	'RH\RhRecaptcha\Domain\Validator\ReCaptchaValidator',
	'isValid',
	FALSE
);
