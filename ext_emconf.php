<?php

########################################################################
# Extension Manager/Repository config file for ext: "dubletfinder"
# 
# Auto generated 19-08-2005 22:04
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'Dublet Finder',
	'description' => 'This extension finds entries from tt_address and fe_users that share the same email address and deletes all but one (for each address), merging the flags in the process. The remaining entry is moved to a different page.',
	'category' => 'module',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'shy' => '',
	'dependencies' => 'direct_mail',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'private' => '',
	'download_password' => '',
	'version' => '0.0.0',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:7:{s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"d3b9";s:16:"locallang_db.php";s:4:"a5d8";s:19:"doc/wizard_form.dat";s:4:"c6a5";s:20:"doc/wizard_form.html";s:4:"79cc";s:43:"modfunc1/class.tx_dubletfinder_modfunc1.php";s:4:"e985";s:22:"modfunc1/locallang.php";s:4:"894e";}',
);

?>