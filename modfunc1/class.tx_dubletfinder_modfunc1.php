<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2005-2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Module extension (addition to function menu) 'Dublet Finder' for the
 * 'dubletfinder' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_dubletfinder
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
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
	var $useCross = false;
	/** boolean: whether to check for dublets in fe_users */
	var $useFeUsers = false;
	/** boolean: whether to check for dublets in tt_address */
	var $useAddress = false;

	/** boolean: whether to also check for deleted records */
	var $checkDeletedRecords = false;

	/** set to true to switch debug output on */
	var $debug = false;

	/** boolean: whether to trim the e-mail addresses first */
	var $doTrim = false;
	/** boolean: whether to delete obviously invalid e-mail addresses first */
	var $doRemoveInvalid = false;
	/** boolean: whether to delete empty e-mail addresses first */
	var $doRemoveBlank = false;

	function modMenu()	{
		return array(
			'tx_dubletfinder_modfunc1_function' => ''
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
		$output .= $this->renderCleanup();
		$output .= $this->renderCheckboxes();

		$this->retrieveFormData();
		$output .= $this->renderForm();

		$output .= '<h3>'.$LANG->getLL('heading_results').'</h3>'.chr(10);
		$output .= $this->createRecursivePageList();

		if ($this->doTrim) {
			$output .= $this->trimEmail();
		}

		if ($this->doRemoveInvalid) {
			$output .= $this->removeInvalidEmail();
		}

		if ($this->doRemoveBlank) {
			$output .= $this->removeBlankEmail();
		}

		if ($this->checkForm()) {
			$output .= $this->removeAllDublets();
		}

		if ($this->checkDeletedRecords) {
			$output .= $this->removeRecordsWithDeletedDublets();
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
			'1'.$this->enableFields('sys_dmail_group'),
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
	 * Renders the cleanup checkboxes (they are non-checked by default)
	 *
	 * @return	String		HTML output (won't be empty)
	 *
	 * @access private
	 */
	function renderCleanup() {
		global $LANG;

		$output = '<h3>'.$LANG->getLL('heading_cleanUpEmail').'</h3>'.chr(10);
		$output .= '<p>'.$LANG->getLL('verbose_cleanUpEmail').'</p>'.chr(10);

		$this->doTrim = t3lib_div::GPvar('doTrim');
		$this->doRemoveInvalid = t3lib_div::GPvar('doRemoveInvalid');
		$this->doRemoveBlank = t3lib_div::GPvar('doRemoveBlank');

		$output .= '<p>'.chr(10);
		$output .= '<input type="checkbox" name="doTrim" id="doTrim" value="1"'.($this->doTrim ? ' checked="checked"' : '').' /><label for="doTrim"> '.$LANG->getLL('label_trim').'</label><br />'.chr(10);
		$output .= '<input type="checkbox" name="doRemoveInvalid" id="doRemoveInvalid" value="1"'.($this->doRemoveInvalid ? ' checked="checked"' : '').' /><label for="doRemoveInvalid"> '.$LANG->getLL('label_deleteInvalid').'</label><br />'.chr(10);
		$output .= '<input type="checkbox" name="doRemoveBlank" id="doRemoveBlank" value="1"'.($this->doRemoveBlank ? ' checked="checked"' : '').' /><label for="doRemoveBlank"> '.$LANG->getLL('label_deleteBlank').'</label>'.chr(10);
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
			$this->checkDeletedRecords = in_array('checkDeletedRecords', $useTables);
		} else {
			// If the form has not been submitted yet, use default values.
			$this->useCross = true;
			$this->useAddress = true;
			$this->useFeUsers = true;
			$this->checkDeletedRecords = false;
		}

		$output .= '<p>'.chr(10);
		$output .= '<input type="hidden" name="submitted" value="1" />'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="useCross" value="useCross"'.($this->useCross ? ' checked="checked"' : '').' /><label for="useCross"> '.$LANG->getLL('label_useCross').'</label><br />'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="useFeUsers" value="useFeUsers"'.($this->useFeUsers ? ' checked="checked"' : '').' /><label for="useFeUsers"> '.$LANG->getLL('label_useFeUsers').'</label><br />'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="useAddress" value="useAddress"'.($this->useAddress ? ' checked="checked"' : '').' /><label for="useAddress"> '.$LANG->getLL('label_useAddress').'</label>'.chr(10);
		$output .= '<h4>'.$LANG->getLL('heading_specialSelection').'</h4>'.chr(10);
		$output .= '<input type="checkbox" name="useTables[]" id="checkDeletedRecords" value="checkDeletedRecords"'.($this->checkDeletedRecords ? ' checked="checked"' : '').' /><label for="checkDeletedRecords"> '.$LANG->getLL('label_checkDeletedRecords').'</label>'.chr(10);
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

		$output = '';

		if ($this->useCross) {
			$dbResult = $this->getCrossDublets();
			if ($dbResult) {
				$output .= '<h4>'.$LANG->getLL('heading_thereAre').' '
					.$GLOBALS['TYPO3_DB']->sql_num_rows($dbResult).' '
					.$LANG->getLL('heading_crossDublets').':</h4>';

				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$output .= '<p>'.htmlentities($row['email']);
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
				$output .= '<h4>'.$LANG->getLL('heading_thereAre').' '
					.$GLOBALS['TYPO3_DB']->sql_num_rows($dbResult).' '
					.$LANG->getLL('heading_FeUsersDublets').':</h4>';

				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$output .= '<p>'.htmlentities($row['email']).' ('.intval($row['numbers']).')';
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
				$output .= '<h4>'.$LANG->getLL('heading_thereAre').' '
					.$GLOBALS['TYPO3_DB']->sql_num_rows($dbResult).' '
					.$LANG->getLL('heading_AddressDublets').':</h4>';

				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					$output .= '<p>'.htmlentities($row['email']).' ('.intval($row['numbers']).')';
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
	 * Gets DB query of e-mail addresses that appear in fe_users more than once.
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
				.$this->enableFields('fe_users'),
			'email HAVING numbers > 1',
			'',
			''
		);
	}

	/**
	 * Deletes a dublet from fe_users (and deletes all corresponding entries in tt_address).
	 *
	 * @param	String		e-mail address that has more than one fe_users record
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
			'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields('fe_users'),
			'',
			'',
			''
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			if ($this->debug) {
				$output .= ': UID: '.intval($row['uid']).', PID: '.intval($row['pid']).', '.$LANG->getLL('label_category').': '.intval($row['category']).'; ';
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
				.' AND email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields('fe_users'),
			array(
				'deleted' => 1,
			)
		);

		return $output;
	}

	/**
	 * Gets DB query of e-mail addresses that appear in tt_address more than once.
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
				.$this->enableFields('tt_address'),
			'email HAVING numbers > 1',
			'',
			''
		);
	}

	/**
	 * Deletes a dublet from tt_address, keeping just one occurence.
	 *
	 * @param	String		e-mail address that has more than one tt_address record
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
			'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields('tt_address'),
			'',
			'',
			''
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			if ($this->debug) {
				$output .= ': UID: '.intval($row['uid']).', PID: '.intval($row['pid']).', '.$LANG->getLL('label_category').': '.intval($row['category']).'; ';
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
				.' AND email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields('tt_address'),
			array(
				'deleted' => 1,
			)
		);

		return $output;
	}

	/**
	 * Gets DB query of e-mail addresses that appear in tt_address _and_ fe_users
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
				.$this->enableFields('tt_address')
				.$this->enableFields('fe_users'),
			'',
			'',
			''
		);
	}

	/**
	 * Deletes a dublet from fe_users (and deletes all corresponding entries in tt_address).
	 *
	 * @param	String		e-mail address that is in tt_address and fe_users
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
			'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
				.' AND pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields('fe_users'),
			'',
			'',
			''
		);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
			if ($this->debug) {
				$output .= ': UID: '.intval($row['uid']).', PID: '.intval($row['pid']).', '.$LANG->getLL('label_category').': '.intval($row['category']).'; ';
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
				'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
					.' AND pid IN ('.$this->pageListRecursive.')'
					.$this->enableFields('tt_address'),
				'',
				'',
				''
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				if ($this->debug) {
					$output .= ': UID: '.intval($row['uid']).', PID: '.intval($row['pid']).', '.$LANG->getLL('label_category').': '.intval($row['category']).'; ';
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
					.' AND email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
					.' AND pid IN ('.$this->pageListRecursive.')'
					.$this->enableFields('fe_users'),
				array(
					'deleted' => 1,
				)
			);

			if ($this->debug) {
				$output .= '<br /><strong>'.$LANG->getLL('label_whereClause').': </strong>';
				$output .= htmlentities('email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
						.' AND pid IN ('.$this->pageListRecursive.')'
						.$this->enableFields('tt_address'))
						.'<br /><br />';
			}
			// delete all other occurences in tt_address
		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
		 		'tt_address',
				'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
					.' AND pid IN ('.$this->pageListRecursive.')'
					.$this->enableFields('tt_address'),
				array(
					'deleted' => 1,
				)
			);

			if ($this->debug) {
				$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'email',
					'tt_address',
					'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($email)
						.' AND pid IN ('.$this->pageListRecursive.')'
						.$this->enableFields('tt_address'),
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
	 * Trims the e-mail addresses in tt_address and fe_users
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function trimEmail() {
		$output = $this->trimEmailInTable('tt_address');
		$output .= $this->trimEmailInTable('fe_users');

		return $output;
	}

	/**
	 * Trims the e-mail addresses in a database table
	 *
	 * @param	String		name of the database table to use (should be tt_address or fe_users)
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function trimEmailInTable($tableName) {
		global $LANG;
		$regex = '/^((\'|\"|&nbsp;)*)([^\"\'&]+)((\'|\"|&nbsp;)*)$/';

		$output = '';

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, email',
			$tableName,
			'pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields($tableName),
			'',
			'',
			''
		);

		if ($dbResult) {
			$output .= '<h4>'.$LANG->getLL('heading_trimTable').' <code>'.$tableName.'</code>:</h4>'.chr(10);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$currentEmail = $row['email'];
				$trimmedEmail = trim($currentEmail);
				$matches = array();
				if (preg_match($regex, $trimmedEmail, $matches)) {
					$betterTrimmedEmail = $matches[3];
				} else {
					$betterTrimmedEmail = $trimmedEmail;
				}

				if ($currentEmail !== $betterTrimmedEmail) {
					if ($this->debug) {
						$output .= 'UID: '.intval($row['uid']).', ';
					}
					$output .= '['.htmlentities($currentEmail).'] -&gt; ['.htmlentities($betterTrimmedEmail).']'.'<br />'.chr(10);

					if ($this->isLive()) {
					 	$updateResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					 		$tableName,
							'uid='.intval($row['uid']),
							array(
								'email' => $betterTrimmedEmail
							)
						);
					}
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		}

		return $output;
	}

	/**
	 * Removes records with invalid e-mail addresses in tt_address and fe_users
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function removeInvalidEmail() {
		$output = $this->removeInvalidEmailInTable('tt_address');
		$output .= $this->removeInvalidEmailInTable('fe_users');

		return $output;
	}

	/**
	 * Removes records with invalid e-mail addresses in a database table
	 *
	 * @param	String		name of the database table to use (should be tt_address or fe_users)
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function removeInvalidEmailInTable($tableName) {
		global $LANG;
		$regex = '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

		$output = '';

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, email',
			$tableName,
			'pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields($tableName),
			'',
			'',
			''
		);

		if ($dbResult) {
			$output .= '<h4>'.$LANG->getLL('heading_deleteInvalidFromTable').' <code>'.$tableName.'</code>:</h4>'.chr(10);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$currentEmail = trim($row['email']);
				$matches = array();
				$match = preg_match($regex, $currentEmail, $matches);

				// only act if:
				// 1. the e-mail address isn't empty, and
				// 2. the pattern doesn't match exactly.
				if (!empty($currentEmail) && (!$match || ($matches[0] !== $currentEmail))) {
					if ($this->debug) {
						$output .= 'UID: '.intval($row['uid']).', ';
					}
					$output .= htmlentities($currentEmail).'<br />'.chr(10);

					if ($this->isLive()) {
					 	$updateResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					 		$tableName,
							'uid='.intval($row['uid']),
							array(
								'deleted' => 1,
							)
						);
					}
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		}

		return $output;
	}

	/**
	 * Removes records with blank e-mail addresses in tt_address and fe_users
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function removeBlankEmail() {
		$output = $this->removeBlankEmailInTable('tt_address');
		$output .= $this->removeBlankEmailInTable('fe_users');

		return $output;
	}

	/**
	 * Removes records with blank e-mail addresses in a database table
	 *
	 * @param	String		name of the database table to use (should be tt_address or fe_users)
	 *
	 * @return	String		status output
	 *
	 * @access private
	 */
	function removeBlankEmailInTable($tableName) {
		global $LANG;

		$output = '';

	 	$dbResultCount = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS numbers',
			$tableName,
			'email=\'\''
				.' AND pid IN ('.$this->pageListRecursive.')'
				.$this->enableFields($tableName),
			'',
			'',
			''
		);

		if ($dbResultCount) {
			$output .= '<h4>'.$LANG->getLL('heading_deleteBlankFromTable').' <code>'.$tableName.'</code>:</h4>'.chr(10);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultCount);
			$output .= '<p>'.$LANG->getLL('heading_thereAre').' '
				.intval($row['numbers']).' '
				.$LANG->getLL('label_entriesWithEmptyEmail').'.</p>';
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResultCount);

			if ($this->isLive()) {
			 	$updateResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			 		$tableName,
					'email=\'\''
						.' AND pid IN ('.$this->pageListRecursive.')'
						.$this->enableFields($tableName),
					array(
						'deleted' => 1,
					)
				);
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
	 * @return	string		debug output (if debug mode is on)
	 *
	 * @access	private
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
					.$this->enableFields('pages'),
				'',
				'',
				''
			);

			$currentPageList = '';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				if (!empty($currentPageList)) {
					$currentPageList .= ',';
				}
				$currentPageList .= intval($row['uid']);
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
	 * Removes *undeleted* records from for which *deleted*
	 * records with the same e-mail address exit.
	 *
	 * Note: This function will remove valid records!
	 *
	 * @param	string		name of the DB table to operate on (currently only
	 * 						tt_address is supported)
	 *
	 * @return	string		status output
	 *
	 * @access	private
	 */
	function removeRecordsWithDeletedDublets($tableName = 'tt_address') {
		global $LANG;

		$output = '<h4>'.$LANG->getLL('label_checkDeletedRecords').':</h4>'
			.chr(10);

	 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'email',
			$tableName,
			'pid IN ('.$this->pageListRecursive.') '
				.'AND deleted=1'
		);

		$deletedEmails = array();

		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				// Only add addresses without comma as we will be creating a
				// comma-separated list of these addresses.
				if (strpos($row['email'], ',') == false) {
					$deletedEmails[] = $GLOBALS['TYPO3_DB']->fullQuoteStr($row['email']);
				}
			}
			// remove duplicates
			$deletedEmails = array_unique($deletedEmails);
			$deletedEmailsCommaSeparated = implode(',', $deletedEmails);
			if ($this->debug) {
				$output .= '<p>'.$deletedEmailsCommaSeparated.'</p>'.chr(10);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		}

		if (!empty($deletedEmailsCommaSeparated)) {
			$counter = 0;

		 	$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'email',
				$tableName,
				'pid IN ('.$this->pageListRecursive.') '
					.'AND deleted=0 '
					.'AND email IN ('.$deletedEmailsCommaSeparated.')'
			);
			if ($this->debug) {
				$output .= '<p>';
			}
			if ($dbResult) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
					if ($this->debug) {
						$output .= htmlspecialchars($row['email']).'<br />';
					}
					$counter++;
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			}
			if ($this->debug) {
				$output .= '</p>'.chr(10);
			}

			if ($this->isLive()) {
			 	$dbResult = $GLOBALS['TYPO3_DB']->exec_DELETEquery(
					$tableName,
					'pid IN ('.$this->pageListRecursive.') '
						.'AND deleted=0 '
						.'AND email IN ('.$deletedEmailsCommaSeparated.')'
				);
			}

			$output .= '<p>'.$LANG->getLL('heading_thereAre').' '.$counter.'</p>'
				.chr(10);

		}

		return $output;
	}

	/**
	 * Wrapper function for t3lib_pageSelect::enableFields() since it is no longer
	 * accessible statically.
	 *
	 * Returns a part of a WHERE clause which will filter out records with start/end
	 * times or deleted/hidden/fe_groups fields set to values that should de-select
	 * them according to the current time, preview settings or user login.
	 * Is using the $TCA arrays "ctrl" part where the key "enablefields" determines
	 * for each table which of these features applies to that table.
	 *
	 * @param	string		table name found in the $TCA array
	 * @param	integer		If $show_hidden is set (0/1), any hidden-fields in
	 * 						records are ignored. NOTICE: If you call this function,
	 * 						consider what to do with the show_hidden parameter.
	 * 						Maybe it should be set? See tslib_cObj->enableFields
	 * 						where it's implemented correctly.
	 * @param	array		Array you can pass where keys can be "disabled",
	 * 						"starttime", "endtime", "fe_group" (keys from
	 * 						"enablefields" in TCA) and if set they will make sure
	 * 						that part of the clause is not added. Thus disables
	 * 						the specific part of the clause. For previewing etc.
	 * @param	boolean		If set, enableFields will be applied regardless of
	 * 						any versioning preview settings which might otherwise
	 * 						disable enableFields.
	 * @return	string		the clause starting like " AND ...=... AND ...=..."
	 *
	 * @access	protected
	 */
	function enableFields($table, $show_hidden = -1, $ignore_array = array(),
		$noVersionPreview = false
	) {
		// We need to use an array as the singleton otherwise won't work.
		static $pageCache = array();

		if (!$pageCache[0]) {
			$pageCache[0] = t3lib_div::makeInstance('t3lib_pageSelect');
		}

		return $pageCache[0]->enableFields(
			$table,
			$show_hidden,
			$ignore_array,
			$noVersionPreview
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dubletfinder/modfunc1/class.tx_dubletfinder_modfunc1.php'])	 {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dubletfinder/modfunc1/class.tx_dubletfinder_modfunc1.php']);
}
