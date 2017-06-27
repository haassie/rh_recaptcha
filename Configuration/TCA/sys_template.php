<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(    
  'rh_recaptcha',    
  'Configuration/TypoScript',    
  'reCAPTCHA'
);
