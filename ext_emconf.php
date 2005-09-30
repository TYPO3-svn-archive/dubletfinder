<?php

########################################################################
# Extension Manager/Repository config file for ext: "dubletfinder"
# 
# Auto generated 30-09-2005 03:48
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'Dublet Finder',
	'description' => 'This extension finds entries from tt_address and fe_users that share the same email address (dublets/duplicates/double entries) and deletes all but one (for each address), merging the flags in the process. The remaining entry is moved to a different page.',
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
	'_md5_values_when_last_written' => 'a:6:{s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"e791";s:16:"locallang_db.php";s:4:"3ffb";s:8:"todo.txt";s:4:"8dc3";s:43:"modfunc1/class.tx_dubletfinder_modfunc1.php";s:4:"4f39";s:22:"modfunc1/locallang.php";s:4:"59be";}',
);

?>