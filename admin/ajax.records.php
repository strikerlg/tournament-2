<?php
/**
 * AJAX: handling of Ajax record calls
 * 
 * PHP Version 5.2
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * @category  phpMyFAQ
 * @package   Administration
 * @author    Anatoliy Belsky <anatoliy.belsky@mayflower.de>
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @copyright 2009-2012 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/MPL-1.1.html Mozilla Public License Version 1.1
 * @link      http://www.phpmyfaq.de
 * @since     2009-03-31
 */

if (!defined('IS_VALID_PHPMYFAQ')) {
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

$ajax_action = PMF_Filter::filterInput(INPUT_GET, 'ajaxaction', FILTER_SANITIZE_STRING);

// Expected is an array of the structure:
// array( 0 => array((int)id, (string)langugage, (int) checked)),
//        1 => .....
// )
$items = isset($_GET['items']) && is_array($_GET['items']) ? $_GET['items'] : array();

if (!isset($items[0][2])) {
    $items[0][2] = 0;
}

switch($ajax_action) {

    // save active FAQs
    case 'save_active_records':
        if ($permission['approverec']) {
            if (!empty($items)) {
                $faq = new PMF_Faq();

                foreach ($items as $item) {
                    if (is_array($item) && count($item) == 3 && PMF_Language::isASupportedLanguage($item[1])) {
                        print $faq->updateRecordFlag((int)$item[0], addslashes($item[1]), (int)$item[2], 'active');
                    }
                }
            }
        } else {
            print $PMF_LANG['err_NotAuth'];
        }
        break;

    // save sticky FAQs
    case 'save_sticky_records':
        if ($permission['editbt']) {
            if (!empty($items)) {
                $faq = new PMF_Faq();

                foreach ($items as $item) {
                    if (is_array($item) && count($item) == 3 && PMF_Language::isASupportedLanguage($item[1])) {
                        print $faq->updateRecordFlag((int)$item[0], addslashes($item[1]), (int)$item[2], 'sticky');
                    }
                }
            }
        } else {
            print $PMF_LANG['err_NotAuth'];
        }
        break;

    // search FAQs for suggestions
    case 'search_records':
        if ($permission['editbt']) {

            $faq             = new PMF_Faq();
            $faqSearch       = new PMF_Search($db, $Language);
            $faqSearchResult = new PMF_Search_Resultset($user, $faq);
            $searchResult    = '';
            $searchString    = PMF_Filter::filterInput(INPUT_POST, 'search', FILTER_SANITIZE_STRIPPED);

            if (!is_null($searchString)) {
                $searchResult = $faqSearch->search($searchString, false);

                $faqSearchResult->reviewResultset($searchResult);

                $faqSearchHelper = PMF_Helper_Search::getInstance();
                $faqSearchHelper->setSearchterm($searchString);

                print $faqSearchHelper->renderAdminSuggestionResult($faqSearchResult);
            }
            
        } else {
            print $PMF_LANG['err_NotAuth'];
        }
        break;

    // delete FAQs
    case 'delete_record':
        if ($permission['delbt']) {

            $recordId   = PMF_Filter::filterInput(INPUT_POST, 'record_id', FILTER_VALIDATE_INT);
            $recordLang = PMF_Filter::filterInput(INPUT_POST, 'record_lang', FILTER_SANITIZE_STRING);

            $logging = new PMF_Logging();
            $logging->logAdmin($user, 'Deleted FAQ ID ' . $recordId);

            $faq->deleteRecord($recordId, $recordLang);
            print $PMF_LANG['ad_entry_delsuc'];
            
        } else {
            print $PMF_LANG['err_NotAuth'];
        }
        break;
}
