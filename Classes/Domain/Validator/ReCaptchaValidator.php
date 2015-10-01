<?php
namespace RH\RhRecaptcha\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * ReCaptchaValidator
 */
class ReCaptchaValidator {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @param \In2code\Powermail\Domain\Model\Mail $mail
	 * @param \In2code\Powermail\Domain\Validator\CustomValidator $object
	 */
	public function isValid($mail, $object) {
		$answers = $mail->getAnswers();
		$powermailVars = GeneralUtility::_GP('tx_powermail_pi1');

		/** @var \In2code\Powermail\Domain\Model\Answer $answer */
		foreach ($answers as $answer) {
			$field = $answer->getField();

			if ($field->getType() == 'recaptcha') {
				/*
				 * Response will be token if valid, an empty string when not valid
				 * When the previous step doesn't contain the recaptcha, NULL is
				 * returned
				 */
				$response = GeneralUtility::_GP('g-recaptcha-response');

				if ($response !== NULL) {
					// Only check if a response is set

					/** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager configurationManager */
					$configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
					$fullTs = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
					$reCaptchaSettings = $fullTs['plugin.']['tx_powermail.']['settings.']['setup.']['reCAPTCHA.'];

					if (
						isset($reCaptchaSettings) &&
						is_array($reCaptchaSettings) &&
						isset($reCaptchaSettings['secretKey']) &&
						$reCaptchaSettings['secretKey']
					) {
						$ch = curl_init();

						$fields = array(
							'secret' => $reCaptchaSettings['secretKey'],
							'response' => $response
						);

						//url-ify the data for the POST
						$fieldsString = '';
						foreach ($fields as $key => $value) {
							$fieldsString .= $key . '=' . $value . '&';
						}
						rtrim($fieldsString, '&');

						//set the url, number of POST vars, POST data
						curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
						curl_setopt($ch, CURLOPT_POST, count($fields));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);

						//execute post
						$result = json_decode(curl_exec($ch));
						if (!(bool)$result->success) {
							$object->setErrorAndMessage($field, LocalizationUtility::translate('validation.possible_robot', 'rhrecaptcha'));
						}
					} else {
						throw new InvalidVariableException(LocalizationUtility::translate('error.no_secretKey', 'rhrecaptcha'), 1358349150);
					}
				}
			}
		}
	}
}