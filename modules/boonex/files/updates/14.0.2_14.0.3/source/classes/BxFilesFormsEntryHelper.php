<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    Files Files
 * @ingroup     UnaModules
 *
 * @{
 */

/**
 * Entry forms helper functions
 */
class BxFilesFormsEntryHelper extends BxBaseModFilesFormsEntryHelper
{
    public function __construct($oModule)
    {
        $this->_sDisplayForFormAdd ='bx_files_entry_upload';
        $this->_sObjectNameForFormAdd ='bx_files_upload';

        parent::__construct($oModule);
    }

    protected function redirectAfterDelete($aContentInfo)
    {
        $CNF = &$this->_oModule->_oConfig->CNF;
        $oProfile = BxDolProfile::getInstance($aContentInfo[$CNF['FIELD_AUTHOR']]);
        if ($oProfile)
            $this->_redirectAndExit('page.php?i=' . $CNF['URI_AUTHOR_ENTRIES'] . '&profile_id=' . $oProfile->id());
        else
            $this->_redirectAndExit($CNF['URL_HOME'], true, array(
                'account_id' => getLoggedId(),
                'profile_id' => bx_get_logged_profile_id(),
            ));
    }
}

/** @} */
