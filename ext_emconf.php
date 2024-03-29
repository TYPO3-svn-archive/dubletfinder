<?php

########################################################################
# Extension Manager/Repository config file for ext: "dubletfinder"
#
# Auto generated 02-01-2009 20:04
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Direct Mail Companion',
	'description' => 'This extension is no longer maintained. (It helps you maintain your Direct Mail subscriber records. It can remove whitespace and quotes around e-mail addresses, delete records with an invalid or empty e-mail address and remove duplicates.)',
	'category' => 'module',
	'shy' => 0,
	'dependencies' => 'cms,direct_mail',
	'conflicts' => 'dbal',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'obsolete',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'author_company' => 'oliverklee.de',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.6.0',
	'_md5_values_when_last_written' => 'a:7:{s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"e791";s:16:"locallang_db.xml";s:4:"0c0b";s:8:"todo.txt";s:4:"a00f";s:43:"modfunc1/class.tx_dubletfinder_modfunc1.php";s:4:"f4b2";s:22:"modfunc1/locallang.xml";s:4:"cbd4";s:14:"doc/manual.sxw";s:4:"d5dd";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'direct_mail' => '',
			'typo3' => '4.0.0-0.0.0',
		),
		'conflicts' => array(
			'dbal' => '',
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>