<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',		
		'tx_dubletfinder_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_dubletfinder_modfunc1.php',
		'LLL:EXT:dubletfinder/locallang_db.php:moduleFunction.tx_dubletfinder_modfunc1'
	);
}
?>