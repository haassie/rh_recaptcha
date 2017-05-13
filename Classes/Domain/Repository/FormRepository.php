<?php

namespace RH\RhRecaptcha\Domain\Repository;

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
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * FormRepository
 */
class FormRepository extends \In2code\Powermail\Domain\Repository\FormRepository
{
    /**
     * Returns form with captcha from given UID
     *
     * @param Form $form
     * @return QueryResult
     */
    public function hasReCaptcha(Form $form)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false)->setRespectSysLanguage(false);
        $and = [
            $query->equals('uid', $form->getUid()),
            $query->equals('pages.fields.type', 'recaptcha')
        ];
        $query->matching($query->logicalAnd($and));
        return $query->execute();
    }
}
