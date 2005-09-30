<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Module extension (addition to function menu) 'Dublet Finder' for the 'dubletfinder' extension.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once(PATH_t3lib.'class.t3lib_page.php');

class tx_dubletfinder_modfunc1 extends t3lib_extobjbase {
	/** String about what to do (dryrun/live) */
	var $function = 'dryrun';

	/** array of entered page lists (comma-separated) for Direct Mail groups */
	var $groups = array();
	/** comma-separated list of pages in which to look for duplicates */
	var $pageList = '';
	/** comma-separated list of pages in which to look for duplicates
	   (including subpages) */
	var $pageListRecursive = '';

	/** boolean: whether to check for cross-entries in fe_users and tt_address */
	var $useCross;
	/** boolean: whether to check for dublets in fe_users */
	var $useFeUsers;
	/** boolean: whether to check for dublets in tt_address */
	var $useAddress;

	/** set to true to switch debug output on */
	var $debug;

	function modMenu()	{
		return Array(
			'tx_dubletfinder_modfunc1_function' => '',
		);
	}

	/**
	 * Initializes the whole shebang.
	 *
	 * @access public
	 */
	function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$output = 'Funktion: '.$this->getFunctionMenu();

		$output .= '<h3>'.$LANG->getLL('heading_whatHappens').'</h3>'.chr(10);
		$output .= '<p>'.$LANG->getLL('verbose_whatHappens1').'</p>'.chr(10);
		$output .= '<p><strong>'.$LANG->getLL('heading_Details').':</strong> '.$LANG->getLL('verbose_whatHappens2a').$this->getPid().$LANG->getLL('verbose_whatHappens2b').'</p>'.chr(10);

		$output .= $this->renderDebugCheckbox();
		$output .= $this->renderCheckboxes();

		$this->retrieveFormData();
		$output .= $this->renderForm();

		if ($this->checkForm()) {
			$output .= $this->createRecursivePageList();
			$output .= $this->removeAllDublets();
		}

		return $output;
	}

	/**
	 * Renders the form (with already entered values inbetween) and returns the HTML.
	 *
	 * @return	String		the form HTML (will not be empty)
	 *
	 * @access private
	 */
	function renderForm() {
		global $LANG;

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, pages',
			'sys_dmail_group',
			'1'.t3lib_pageSelect::enableFields('sys_dmail_group'),
			'',
			'',
			''
		);

		$output = '<h3>'.$LANG->getLL('heading_selectGroups').'</h3>'.chr(10);
		$output .= '<p>'.$LANG->getLL('verbose_selectGroups').'</p>'.chr(10);
		$output .= '<p>'.chr(10);
		$output .= '<select name="groups[]" id="groups" multiple="multiple">';

		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$selected = (in_array($row['pages'], $this->groups)) ? ' selected="selected"' : '';
				$output .= '<option value="'.$row['pages'].'"'.$selected.'>'.htmlentities($row['title']).'</option>'.chr(10);
			}

			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		}

		$output .= '</select> <input type="submit" value="'.$LANG->getLL('label_submit').'" /></p>';

		return $output;
	}

	/**
	 * Renders the debug mode checkbox (is non-checked by default)
	 *
	 * @return	String		HTML output (won't be empty)
	 *
	 * @access private
	 */
	function renderDebugCheckbox() {
		global $LANG;

		$output = '<h3>'.$LANG->getLL('heading_debugMode').'</h3>'.chr(10);
		$output .= '<p>'.$LANG->getLL('verbose_debugMode').'</p>'.chr(10);

		$this->debug = t3lib_div::GPvar('debugMode');

		$output .= '<p>'.chr(10);
		$output .= '<input type="checkbox" name="debugMode" id="debugMode" value="1"'.($this->debug ? ' checked="checked"' : '').' /><label for="debugMode"> '.$LANG->getLL('label_debugMode').'</label><br />'.chr(10);
		$output .= '</p>'.chr(10);

		return $output;
	}

	/**
	 * Renders the checkboxes that select which tables should be searched.
	 *
	 * @return	String		HTML output (won't be empty)
	 *
	 * @access private
	 */
	function renderCheckboxes() {
		global $LANG;

		$output = '<h3>'.$LANG->getLL('heading_selectTables').'</h3>'.chr(10);
		$output .= '<p>'.$LANG->getLL('verbose_selectTables').'</p>'.chr(10);

		if (t3lib_div::GPvar('submitted')) {
			$useTables = t3lib_div::GPvar('useTables');
			if (!isset($useTables)) {
				$useTables = array();
			}

			$this->useCross = in_array('useCross', $useTables);
			$this->useAddress = in_array('useAddress', $useTables);
			$this->useFeUsers = in_array('useFeUsers', $useTables);
		} else {
			// If the form has not been submitted yet, check everything by default.
			$this->useCross = true;
			$this->useAddress = true;
			$this->useFeUsers = true;
		}

		$output .= '<p>'.chr(10);
		$output .= '<input type="hidden" name="submitted" value="1" />'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="useCross" value="useCross"'.($this->useCross ? ' checked="checked"' : '').' /><label for="useCross"> '.$LANG->getLL('label_useCross').'</label><br />'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="useFeUsers" value="useFeUsers"'.($this->useFeUsers ? ' checked="checked"' : '').' /><label for="useFeUsers"> '.$LANG->getLL('label_useFeUsers').'</label><br />'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="useAddress" value="useAddress"'.($this->useAddress ? ' checked="checked"' : '').' /><label for="useAddress"> '.$LANG->getLL('label_useAddress').'</label>'.chr(10);
		$output .= '</p>'.chr(10);

		return $output;
	}

	/**
	 * This function does all the work and returns a string with its HTML output.
	 *
	 * @return	String		HTML output (shouldn't be empty)
	 *
	 * @access private
	 */
	function removeAllDublets() {
		global $LANG;

		$output = '<h3>'.$LANG->getLL('heading_results').'</h3>'.chr(10);

		if ($this->useCross) {
			$dbResult = $this->getCrossDublets();
			if ($dbResult) {
				$output .= '<h4>'.$LANG->getLL('heading_thereAre').$GLOBALS['TYPO3_DB']->sql_num_rows($dbResult).$LANG->getLL('heading_crossDublets').':</h4>';

				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$output .= '<p>'.$row['email'];
					if ($this->isLive()) {
						$output .= $this->reduceCrossDublet($row['email']);
					}
					$output .= '</p>'.chr(10);
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			} else {
				$output .= $GLOBALS['TYPO3_DB']->sql_error();
			}
		}

		if ($this->useFeUsers) {
			$dbResult = $this->getFeUsersDublets();
			if ($dbResult) {
				$output .= '<h4>'.$LANG->getLL('heading_thereAre').$GLOBALS['TYPO3_DB']->sql_num_rows($dbResult).$LANG->getLL('heading_FeUsersDublets').':</h4>';

				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$output .= '<p>'.$row['email'].' ('.$row['numbers'].')';
					if ($this->isLive()) {
						$output .= $this->reduceFeUsersDublet($row['email']);
					}
					$output .= '</p>'.chr(10);
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			} else {
				$output .= $GLOBALS['TYPO3_DB']->sql_error();
			}
		}

		if ($this->useAddress) {
			$dbResult = $this->getAddressDublets();
			if ($dbResult) {
				$output .= '<h4>'.$LANG->getLL('heading_thereAre').$GLOBALS['TYPO3_DB']->sql_num_rows($dbResult).$LANG->getLL('heading_AddressDublets').':</h4>';

				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$output .= '<p>'.$row['email'].' ('.$row['numbers'].')';
					if ($this->isLive()) {
						$output .= $this->reduceAddressDublet($row['email']);
					}
					$output .= '</p>'.chr(10);
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			} else {
				$output .= $GLOBALS['TYPO3_DB']->sql_error();
			}
		}

		return $output;
	}

	/**
	 * Gets DB query of email addresses that appear in fe_users more than once.
	 *
	 * @return	object		DB result object (for a row)
	 *
	 * @access private
	 */
	function getFeUsersDublets() {
	 	return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'email, COUNT(email) AS numbers',
			'fe_users',
			'pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('fe_users'),
			'email HAVING numbers > 1',
			'',
			''
		);
	}

	/**
	 * Deletes a dublet from fe_users (and deletes all corresponding entries in tt_address).
	 *
	 * @param	String		email address that has more than one fe_users record
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function reduceFeUsersDublet($email) {
		global $LANG;

		$output = '';
		$combinedCategories = 0;
		$firstUid = 0;

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, pid, module_sys_dmail_category AS category',
			'fe_users',
			'email='.$this->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('fe_users'),
			'',
			'',
			''
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			if ($this->debug) {
				$output .= ': UID: '.$row['uid'].', PID: '.$row['pid'].', '.$LANG->getLL('label_category').': '.$row['category'].'; ';
			}
			$combinedCategories |= $row['category'];

			if (!$firstUid) {
				$firstUid = intval($row['uid']);
			}

		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		if ($this->debug) {
			$output .= '<br />'.$LANG->getLL('label_combinedCategories').': '.$combinedCategories.'<br />';
			$output .= $LANG->getLL('label_keepUid').$firstUid.', '.$LANG->getLL('label_deletingAllOthers').'.<br />';
		}
		$output .= ': '.$LANG->getLL('label_writingChangesTo').' <code>fe_users</code.'.chr(10);

		// move tt_address record to page and set combined categories
	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
	 		'fe_users',
			'uid='.$firstUid,
			array(
				'pid' => $this->getPid(),
				'module_sys_dmail_category' => $combinedCategories
			)
		);

		// delete all other occurences in fe_users
	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
	 		'fe_users',
			'uid!='.$firstUid
				.' AND email='.$this->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('fe_users'),
			array(
				'deleted' => 1,
			)
		);

		return $output;
	}

	/**
	 * Gets DB query of email addresses that appear in tt_address more than once.
	 *
	 * @return	object		DB result object (for a row)
	 *
	 * @access private
	 */
	function getAddressDublets() {
	 	return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'email, COUNT(email) AS numbers',
			'tt_address',
			'pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('tt_address'),
			'email HAVING numbers > 1',
			'',
			''
		);
	}

	/**
	 * Deletes a dublet from tt_address, keeping just one occurence.
	 *
	 * @param	String		email address that has more than one tt_address record
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function reduceAddressDublet($email) {
		global $LANG;

		$output = '';
		$combinedCategories = 0;
		$firstUid = 0;

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, pid, module_sys_dmail_category AS category',
			'tt_address',
			'email='.$this->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('tt_address'),
			'',
			'',
			''
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			if ($this->debug) {
				$output .= ': UID: '.$row['uid'].', PID: '.$row['pid'].', '.$LANG->getLL('label_category').': '.$row['category'].'; ';
			}
			$combinedCategories |= $row['category'];

			if (!$firstUid) {
				$firstUid = intval($row['uid']);
			}

		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		if ($this->debug) {
			$output .= '<br />'.$LANG->getLL('label_combinedCategories').': '.$combinedCategories.'<br />';
			$output .= $LANG->getLL('label_keepUid').$firstUid.', '.$LANG->getLL('label_deletingAllOthers').'.<br />';
		}
		$output .= ': '.$LANG->getLL('label_writingChangesTo').' <code>tt_address</code>.'.chr(10);

		// move tt_address record to page and set combined categories
	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
	 		'tt_address',
			'uid='.$firstUid,
			array(
				'pid' => $this->getPid(),
				'module_sys_dmail_category' => $combinedCategories
			)
		);

		// delete all other occurences in tt_address
	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
	 		'tt_address',
			'uid!='.$firstUid
				.' AND email='.$this->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('tt_address'),
			array(
				'deleted' => 1,
			)
		);

		return $output;
	}

	/**
	 * Gets DB query of email addresses that appear in tt_address _and_ fe_users
	 *
	 * @return	object		DB result object (for a row)
	 *
	 * @access private
	 */
	function getCrossDublets() {
	 	return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'DISTINCT fe_users.email AS email',
			'tt_address, fe_users',
			'fe_users.email=tt_address.email'
				.' AND fe_users.pid IN ('.$this->pageListRecursive.') AND tt_address.pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('tt_address')
				.t3lib_pageSelect::enableFields('fe_users'),
			'',
			'',
			''
		);
	}

	/**
	 * Deletes a dublet from fe_users (and deletes all corresponding entries in tt_address).
	 *
	 * @param	String		email address that is in tt_address and fe_users
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function reduceCrossDublet($email) {
		global $LANG;

		$output = '';
		$combinedCategories = 0;
		$firstUid = 0;

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, pid, module_sys_dmail_category AS category',
			'fe_users',
			'email='.$this->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.t3lib_pageSelect::enableFields('fe_users'),
			'',
			'',
			''
		);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			if ($this->debug) {
				$output .= ': UID: '.$row['uid'].', PID: '.$row['pid'].', '.$LANG->getLL('label_category').': '.$row['category'].'; ';
			}
			$combinedCategories |= $row['category'];

			if (!$firstUid) {
				$firstUid = intval($row['uid']);
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		// only proceed if there actually is an entry in fe_users (should always be the case)
		if ($firstUid) {
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid, pid, module_sys_dmail_category AS category',
				'tt_address',
				'email='.$this->fullQuoteStr($email)
					.' AND pid IN ('.$this->pageListRecursive.')'
					.t3lib_pageSelect::enableFields('tt_address'),
				'',
				'',
				''
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				if ($this->debug) {
					$output .= ': UID: '.$row['uid'].', PID: '.$row['pid'].', '.$LANG->getLL('label_category').': '.$row['category'].'; ';
				}
				$combinedCategories |= $row['category'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

			if ($this->debug) {
				$output .= '<br />'.$LANG->getLL('label_combinedCategories').': '.$combinedCategories.'<br />';
				$output .= $LANG->getLL('label_keepFeUsersUid').$firstUid.', '.$LANG->getLL('label_deletingAllOthersFromBoth').'.<br />';
			}
			$output .= ': '.$LANG->getLL('label_writingChangesTo').' <code>fe_users</code>/<code>tt_address</code>.'.chr(10);

			// move fe_users record to page and set combined categories
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
		 		'fe_users',
				'uid='.$firstUid,
				array(
					'pid' => $this->getPid(),
					'module_sys_dmail_category' => $combinedCategories
				)
			);

			// delete all other occurences in fe_users
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
		 		'fe_users',
				'uid!='.$firstUid
					.' AND email='.$this->fullQuoteStr($email)
					.' AND pid IN ('.$this->pageListRecursive.')'
					.t3lib_pageSelect::enableFields('fe_users'),
				array(
					'deleted' => 1,
				)
			);

			if ($this->debug) {
				$output .= '<br /><strong>'.$LANG->getLL('label_whereClause').': </strong>';
				$output .= 'email='.$this->fullQuoteStr($email)
						.' AND pid IN ('.$this->pageListRecursive.')'
						.t3lib_pageSelect::enableFields('tt_address')
						.'<br /><br />';
			}
			// delete all other occurences in tt_address
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
		 		'tt_address',
				'email='.$this->fullQuoteStr($email)
					.' AND pid IN ('.$this->pageListRecursive.')'
					.t3lib_pageSelect::enableFields('tt_address'),
				array(
					'deleted' => 1,
				)
			);

			if ($this->debug) {
				$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'email',
					'tt_address',
					'email='.$this->fullQuoteStr($email)
						.' AND pid IN ('.$this->pageListRecursive.')'
						.t3lib_pageSelect::enableFields('tt_address'),
					'',
					'',
					''
				);

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($dbResult) !== 0) {
					$output .= '<br /><strong>'.$LANG->getLL('message_notCorrectlyDeleted').'</strong>';
				}
			}
		}

		return $output;
	}

	/**
	 * Reads the form value and checks whether they are ok.
	 *
	 * retrieveFormData() must be called before this function can be called!
	 *
	 * @return	boolean		true is everything is entered and ok, false otherwise
	 *
	 * @accces private
	 */
	function checkForm() {
		return (!empty($this->pageList) && $this->pObj->id);
	}

	/**
	 * Retrieves the form data and stores it in $this->function, $this->groups and $this->pageList.
	 *
	 * @access private
	 */
	function retrieveFormData() {
		if (!empty($this->pObj->MOD_SETTINGS['tx_dubletfinder_modfunc1_function'])) {
			$this->function = $this->pObj->MOD_SETTINGS['tx_dubletfinder_modfunc1_function'];
		}

		$this->groups = t3lib_div::GPvar('groups');
		if (!$this->groups) {
			$this->groups = array();
		}
		$this->pageList = implode(',', $this->groups);

		return;
	}

	/**
	 * Creates a drop-down list of available functions (dry-run or live).
	 * The first option always is selected (for safety).
	 * We don't use BEfunc's functions because we don't want to automatically do anything when an option
	 * is selected.
	 *
	 * @return	String		HTML code
	 *
	 * @access private
	 */
	function getFunctionMenu() {
		global $LANG;

		$output = '<select name="SET[tx_dubletfinder_modfunc1_function]">'.chr(10);
		$output .= '  <option value="dryrun" selected="selected">'.$LANG->getLL('label_modeDryrun').'</option>'.chr(10);
		$output .= '  <option value="live">'.$LANG->getLL('label_modeLive').'</option>'.chr(10);
		$output .= '</select>'.chr(10);

		return $output;
	}

	/**
	 * Checks whether we're just simulating or really want to do something.
	 *
	 * @return	boolean		true for live, false for dry-run
	 *
	 * @access private
	 */
	function isLive() {
		return ($this->function === 'live');
	}

	/**
	 * Gets the PID of the selected page in the page tree.
	 *
	 * @return	integer		PID of the selected page in the page tree (shouldn't be zero)
	 *
	 * @access private
	 */
	function getPid() {
		return $this->pObj->id;
	}

	/**
	 * Finds the subpages (recursively) of the pages in $this->pageList
	 * and writes it to $this->pageListRecursive.
	 *
	 * @return	String		debug output (if debug mode is on)
	 *
	 * @access private
	 */
	function createRecursivePageList() {
		global $LANG;

		$output = '';

		$collectivePageList = $this->pageList;
		$currentPageList = $collectivePageList;

		while (!empty($currentPageList)) {
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'pages',
				'pid!=0'
					.' AND pid IN ('.$currentPageList.')'
					.t3lib_pageSelect::enableFields('pages'),
				'',
				'',
				''
			);

			$currentPageList = '';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				if (!empty($currentPageList)) {
					$currentPageList .= ',';
				}
				$currentPageList .= $row['uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			if (!empty($currentPageList)) {
				$collectivePageList .= ','.$currentPageList;
			}
		}

		$this->pageListRecursive = $collectivePageList;

		if ($this->debug) {
			$output .= '<p><strong>'.$LANG->getLL('label_pagesList').':</strong> '.$this->pageList.'<br />';
			$output .= '<strong>'.$LANG->getLL('label_pagesListRecursive').':</strong> '.$this->pageListRecursive.'</p>';
		}

		return $output;
	}

	/**
	 * Escaping and quoting values for SQL statements.
	 * (Taken from t3lib_db as this function has been introduced only in Typo3 3.8)
	 *
	 * @param	string		Input string
	 * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 */
	function fullQuoteStr($str)	{
		return '\''.addslashes($str).'\'';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dubletfinder/modfunc1/class.tx_dubletfinder_modfunc1.php'])	 {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dubletfinder/modfunc1/class.tx_dubletfinder_modfunc1.php']);
}
