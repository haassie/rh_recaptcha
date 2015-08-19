<?php
namespace RH\RhRecaptcha\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;

/**
 * Class ReCaptchaViewHelper
 * @package RH\RhRecaptcha\ViewHelpers
 * @author Richard Haeser <richardhaeser@gmail.com>
 */
class ReCaptchaViewHelper extends AbstractViewHelper {

	/**
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @return string
	 */
	public function render() {
		$fullTs = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$reCaptchaSettings = $fullTs['plugin.']['tx_powermail.']['settings.']['setup.']['reCAPTCHA.'];

		if (
			isset($reCaptchaSettings) &&
			is_array($reCaptchaSettings) &&
			isset($reCaptchaSettings['siteKey']) &&
			$reCaptchaSettings['siteKey']
		) {
			$this->templateVariableContainer->add('siteKey', $reCaptchaSettings['siteKey']);
			$content = $this->renderChildren();
			$this->templateVariableContainer->remove('siteKey');
		} else {
			throw new InvalidVariableException('No siteKey provided in TypoScript constants', 1358349150);
		}

		return $content;
	}

}