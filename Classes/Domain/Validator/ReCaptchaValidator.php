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
	 * @param \In2code\Powermail\Domain\Model\Mail $mail
	 * @param \In2code\Powermail\Domain\Validator\CustomValidator $object
	 */
	public function isValid($mail, $object) {
		$answers = $mail->getAnswers();
		/** @var \In2code\Powermail\Domain\Model\Answer $answer */
		foreach ($answers as $answer) {
			$field = $answer->getField();

			if ($field->getType() == 'recaptcha') {
				$response = GeneralUtility::_GP('g-recaptcha-response');

				/** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager configurationManager */
				$configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');

				$fullTs = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
				$reCaptchaSettings = $fullTs['plugin.']['tx_powermail.']['settings.']['setup.']['reCAPTCHA.'];


				if (
					isset($reCaptchaSettings) &&
					is_array($reCaptchaSettings) &&
					isset($reCaptchaSettings['secretKey']) &&
					$reCaptchaSettings['secretKey']
				) {
					//open connection
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
						$object->setErrorAndMessage($field, LocalizationUtility::translate('validation.possible_robot', 'rh_recaptcha'));
					}
				} else {
					throw new InvalidVariableException(LocalizationUtility::translate('error.no_secretKey', 'rh_recaptcha'), 1358349150);
				}


			}
		}
	}
}