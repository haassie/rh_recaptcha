<?php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
tx_powermail.flexForm.type.addFieldOptions.recaptcha = reCAPTCHA
', 43);


$signalSlotDispatcher->connect(
	'In2code\Powermail\Domain\Validator\CustomValidator',
	'isValid',
	'RH\RhRecaptcha\Domain\Validator\ReCaptchaValidator',
	'isValid',
	FALSE
);

if (defined('\In2code\Powermail\Domain\Model\Form::TABLE_NAME')) {
    $tableName = \In2code\Powermail\Domain\Model\Form::TABLE_NAME;
} else {
    $tableName = 'tx_powermail_domain_model_forms';
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
	config.tx_extbase {
		persistence {
			classes {
				RH\RhRecaptcha\Domain\Model\Form {
					mapping {
						tableName = ' . $tableName . '
						columns {
						}
					}
				}
			}
		}
	}
');
