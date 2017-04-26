<?php
namespace RH\RhRecaptcha\ViewHelpers;

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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class ReCaptchaViewHelper
 *
 * @package RH\RhRecaptcha\ViewHelpers
 * @author Richard Haeser <richardhaeser@gmail.com>
 */
class ReCaptchaViewHelper extends AbstractViewHelper implements SingletonInterface {
	/**
	 * @var bool
	 */
	protected $initialized = FALSE;

	/**
	 * @var bool
	 */
	protected $escapeOutput = FALSE;

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
	 * Returns an instance of the page renderer
	 *
	 * @return PageRenderer
	 */
	public function getPageRenderer() {
		if (TYPO3_MODE === 'BE') {
			$pageRenderer = $this->getDocInstance()->getPageRenderer();
		} else {
			$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		}

		return $pageRenderer;
	}

	/**
	 * @return string
	 * @throws InvalidVariableException
	 */
	public function render() {
		$fullTs = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
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

		if (!$this->initialized) {
			$key = $reCaptchaSettings['siteKey'];
			$this->initialized = TRUE;
			$pageRenderer = $this->getPageRenderer();
			$pageRenderer->addJsFooterInlineCode(
				'recaptcha', '
					var recaptchaCallback = function() {
						for (var i = 1; i <= 1000; ++i) {
							if (document.getElementById(\'g-recaptcha-\' + i)) {
								grecaptcha.render(\'g-recaptcha-\' + i, {\'sitekey\' : \'' . $key . '\'});
							}
						}
					};
					/*]]>*/					
					</script>
					<script src="https://www.google.com/recaptcha/api.js?hl=' . $reCaptchaSettings['lang'] . '&onload=recaptchaCallback&render=explicit"
						async defer data-ignore="1">/*<![CDATA[*/
				', FALSE, TRUE
			);
//			$pageRenderer->addJsFooterFile(
//				'https://www.google.com/recaptcha/api.js?onload=captchaCallback&render=explicit',
//				'', FALSE, FALSE, '', TRUE
//			);
		}

		return $content;
	}

}