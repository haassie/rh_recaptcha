<?php
namespace RH\RhRecaptcha\Domain\Validator;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use In2code\Powermail\Domain\Model\Form;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;

/**
 * ReCaptchaValidator
 */
class ReCaptchaValidator
{
    /**
     * @var \RH\RhRecaptcha\Domain\Repository\FormRepository
     * @inject
     */
    protected $formRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @param \In2code\Powermail\Domain\Model\Mail $mail
     * @param \In2code\Powermail\Domain\Validator\CustomValidator $object
     * @throws InvalidVariableException
     */
    public function isValid($mail, $object)
    {
        $answers = $mail->getAnswers();

        /** @var \In2code\Powermail\Domain\Model\Answer $answer */
        if ($this->formHasReCaptcha($mail->getForm())) {
            $captchaFoundInAnswer = false;
            $field = null;
            foreach ($answers as $answer) {
                $field = $answer->getField();

                if ($field->getType() !== 'recaptcha') {
                    continue;
                }

                /*
                 * Response will be token if valid, an empty string when not valid
                 * When the previous step doesn't contain the recaptcha, NULL is
                 * returned
                 */
                $response = GeneralUtility::_GP('g-recaptcha-response');
                if ($response !== null) {
                    $captchaFoundInAnswer = true;

                    // Only check if a response is set
                    /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager configurationManager */
                    $configurationManager = $this->objectManager->get(
                        'TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager'
                    );
                    $fullTs = $configurationManager->getConfiguration(
                        ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
                    );
                    $reCaptchaSettings = $fullTs['plugin.']['tx_powermail.']['settings.']['setup.']['reCAPTCHA.'];

                    if (isset($reCaptchaSettings) &&
                        is_array($reCaptchaSettings) &&
                        isset($reCaptchaSettings['secretKey']) &&
                        $reCaptchaSettings['secretKey']
                    ) {
                        $ch = curl_init();

                        $fields = [
                            'secret' => $reCaptchaSettings['secretKey'],
                            'response' => $response
                        ];

                        //url-ify the data for the POST
                        $fieldsString = '';
                        foreach ($fields as $key => $value) {
                            $fieldsString .= $key . '=' . $value . '&';
                        }
                        rtrim($fieldsString, '&');

                        //set the url, number of POST vars, POST data
                        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                        curl_setopt($ch, CURLOPT_POST, count($fields));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);

                        //execute post
                        $result = json_decode(curl_exec($ch));
                        if (!(bool) $result->success) {
                            $object->setErrorAndMessage(
                                $field,
                                LocalizationUtility::translate('validation.possible_robot', 'rhRecaptcha')
                            );
                        }
                    } else {
                        throw new InvalidVariableException(
                            LocalizationUtility::translate('error.no_secretKey', 'rhRecaptcha'),
                            1358349150
                        );
                    }
                }
            }

            // if no captcha arguments given (maybe deleted from DOM)
            if (!$captchaFoundInAnswer) {
                $object->setErrorAndMessage(
                    $field,
                    LocalizationUtility::translate('validation.possible_robot', 'rhRecaptcha')
                );
            }
        }
    }

    /**
     * Checks if given form has a captcha
     *
     * @param \In2code\Powermail\Domain\Model\Form $form
     * @return boolean
     */
    protected function formHasReCaptcha(Form $form)
    {
        $form = $this->formRepository->hasReCaptcha($form);
        return count($form) ? true : false;
    }
}
