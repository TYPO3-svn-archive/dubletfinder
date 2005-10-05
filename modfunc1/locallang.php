<?php
/**
 * Language labels for module "tx_dubletfinder_modfunc1"
 *
 * This file is detected by the translation tool.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'title' => 'Direct Mail Companion',

		'label_modeDryrun' => 'Dry run: only search, don\'t actually change anything',
		'label_modeLive' => 'Execture: search and write changes to DB',
		'label_debugMode' => 'Enable debug mode',
		'label_trim' => 'Trim whitespace, quotes and &amp;nbsp; around e-mail addresses',
		'label_deleteInvalid' => 'Delete records with obviously invalid e-mail addresses',
		'label_deleteBlank' => 'Delete records with blank e-mail addresses',
		'label_useCross' => 'Search for duplicates that occur in <code>fe_users</code> as well as in <code>tt_address</code>',
		'label_useFeUsers' => 'Search for duplicates within <code>fe_users</code>',
		'label_useAddress' => 'Search for duplicates within <code>tt_address</code>',
		'label_submit' => 'Let\'s go!',
		'label_category' => 'category',
		'label_combinedCategories' => 'Combined categories',
		'label_keepUid' => 'Keeping UID ',
		'label_keepFeUsersUid' => 'Keeping <code>fe_users</code> UID ',
		'label_deletingAllOthers' => 'deleting all others',
		'label_deletingAllOthersFromBoth' => 'deleting all others from <code>fe_users</code> as well as <code>tt_address</code>',
		'label_writingChangesTo' => 'Writing changes to',
		'label_whereClause' => 'WHERE clause',
		'label_pagesList' => 'Pages in the Direct Mail groups',
		'label_pagesListRecursive' => 'Recursive list of pages',
		'label_entriesWithEmptyEmail' => 'records with empty e-mail addresses', 

		'heading_whatHappens' => 'What does this module do?',
		'heading_selectGroups' => 'Select groups for the search',
		'heading_debugMode' => 'Debug mode',
		'heading_cleanUpEmail' => 'Clean up e-mail addresses',
		'heading_selectTables' => 'Select database tables',
		'heading_results' => 'Results',
		'heading_Details' => 'Details',
		'heading_thereAre' => 'There are ',
		'heading_crossDublets' => ' duplicates that occur in <code>fe_users</code> as well as in <code>tt_address</code>',
		'heading_FeUsersDublets' => ' duplicates within <code>fe_users</code>',
		'heading_AddressDublets' => ' duplicates within <code>tt_address</code>',
		'heading_trimTable' => 'Trimming e-mail addresses in',
		'heading_deleteInvalidFromTable' => 'Removing records with obviously invalid e-mail addresses in',
		'heading_deleteBlankFromTable' => 'Removing records with blank e-mail addresses in',

		'verbose_whatHappens1' => 'This module looks for e-mail addresses that occur more than once in the selected Direct Mail groups (in the same group as well as across groups).',
		'verbose_whatHappens2a' => 'This module modifies the records in the database tables <code>tt_address</code> and <code>fe_users</code>. One record per e-mail address will be moved to the currently selected page  (PID&nbsp;',
		'verbose_whatHappens2b' => '), while the duplicates will be marked as deleted. The Direct Mail categories of all records will be merged for this. If an e-mail address occurs in <code>fe_users</code> as well as in <code>tt_address</code>, only the records in <code>fe_user</code> will be kept.',
		'verbose_selectGroups' => 'Please select one or more Direct Mail groups which will be searched for duplicate addresses.',
		'verbose_debugMode' => 'Here you can toggle the debug mode. When the debug mode is enabled, the module displays information that can help you in tracking down problems.',
		'verbose_cleanUpEmail' => 'If you have imported e-mail address from another source, there might be whitespace (space, tabs, or linefeeds), quotes or &amp;nbsp; at the beginning or the end of the <code>email</code> field. This would mess up the search for duplicates. You can use this function to remove that whitespace first. In addition, you can have obviously invalid e-mail addresses deleted. Records with no e-mail addresses will not be deleted, though.',
		'verbose_selectTables' => 'Please select which tables should be searched for duplicates. If you want to find all duplicates, just leave all items checked.',

		'message_notCorrectlyDeleted' => 'This record has not been deleted correctly from <code>tt_address</code>!',
	),
	'dk' => Array (
	),
	'de' => Array (
		'title' => 'Direct-Mail-Begleiter',

		'label_modeDryrun' => 'Testlauf: nur suchen, nichts �ndern',
		'label_modeLive' => 'Ausf�hren: suchen und �nderungen in die DB schreiben',
		'label_debugMode' => 'Debug-Modus einschalten',
		'label_trim' => 'Whitespace, Anf�hrungszeichen und &amp;nbsp; um E-Mail-Adressen entfernen',
		'label_deleteInvalid' => 'Eintr�ge mit offensichtlich ung�ltige E-Mail-Adressen l�schen',
		'label_deleteBlank' => 'Eintr�ge mit leeren E-Mail-Adressen l�schen',
		'label_useCross' => 'Dubletten suchen, die sowohl in <code>fe_users</code> als auch in <code>tt_address</code> vorkommen',
		'label_useFeUsers' => 'Dubletten innerhalb von <code>fe_users</code> suchen',
		'label_useAddress' => 'Dubletten innerhalb von <code>tt_address</code> suchen',
		'label_submit' => 'Los!',
		'label_category' => 'Kategorie',
		'label_combinedCategories' => 'Kombinierte Kategorien',
		'label_keepUid' => 'Behalte UID ',
		'label_keepFeUsersUid' => 'Behalte <code>fe_users</code>-UID ',
		'label_deletingAllOthers' => 'l�sche alle anderen',
		'label_deletingAllOthersFromBoth' => 'l�sche alle anderen aus <code>fe_users</code> sowie <code>tt_address</code>',
		'label_writingChangesTo' => 'Writing changes to',
		'label_whereClause' => 'WHERE-Klausel',
		'label_pagesList' => 'Seiten in den Direct-Mail-Gruppen',
		'label_pagesListRecursive' => 'Rekursive Seitenliste',
		'label_entriesWithEmptyEmail' => 'Eintr�ge mit leeren E-Mail-Adressen', 

		'heading_whatHappens' => 'Was passiert in diesem Modul?',
		'heading_selectGroups' => 'Gruppen f�r die Suche ausw�hlen',
		'heading_debugMode' => 'Debug-Modus einschalten',
		'heading_cleanUpEmail' => 'E-Mail-Adressen bereinigen',
		'heading_selectTables' => 'Datenbanktabellen ausw�hlen',
		'heading_results' => 'Ergebnisse',
		'heading_Details' => 'Details',
		'heading_thereAre' => 'Es gibt ',
		'heading_crossDublets' => ' Dublette(n), die sowohl in <code>fe_users</code> als auch in <code>tt_address</code> vorkommen',
		'heading_FeUsersDublets' => ' Dublette(n) innerhalb von <code>fe_users</code>',
		'heading_AddressDublets' => ' Dublette(n) innerhalb von <code>tt_address</code>',
		'heading_trimTable' => 'Entferne unn�tige Zeichen um E-Mail-Adressen in',
		'heading_deleteInvalidFromTable' => 'L�sche Eintr�ge mit offensichtlich ung�ltigen E-Mail-Adressen in',
		'heading_deleteBlankFromTable' => 'L�sche Eintr�ge mit leeren E-Mail-Adressen in',

		'verbose_whatHappens1' => 'Dieses Modul sucht in den ausgew�hlten Direct-Mail-Gruppen nach mehrfach vorkommenden E-Mail-Adressen (sowohl innerhalb einer Gruppe als auch gruppen�bergreifend).',
		'verbose_whatHappens2a' => 'Dieses Modul ver�ndert die Eintr�ge in den Tabellen <code>tt_address</code> und <code>fe_users</code>. Ein Eintrag pro Adresse wird dann in die momentan ausgew�hlte Seite (PID&nbsp;',
		'verbose_whatHappens2b' => ') verschoben, die anderen werden gel�scht. Dabei werden die Direct-Mail-Kategorien aller vorkommenden Eintr�ge kombiniert. Wenn eine Adresse sowohl in <code>fe_users</code> als auch in <code>tt_address</code> vorkommt, bleibt nur der Eintrag in <code>fe_user</code> erhalten.',
		'verbose_selectGroups' => 'Bitte w�hlen Sie eine oder mehrere Direct-Mail-Gruppen aus, in denen nach mehrfach vorkommenden Eintr�gen gesucht werden soll.',
		'verbose_debugMode' => 'Hier k�nnen Sie den Debug-Modus einschalten. Es werden dann Informationen angezeigt, die Ihnen helfen k�nnen, Fehler zu finden.',
		'verbose_cleanUpEmail' => 'Wenn Sie E-Mail-Adressen von einer anderen Quelle importiert haben, kann es sein, dass sich Whitespace (Spaces, Tabs oder Zeilenumbr�che), Anf�hrungszeichen oder &amp;nbsp; am Anfang oder Ende des <code>email</code>-Feldes befindet. Dies w�rde Probleme bei der Dubletten-Suche verursachen. Mit dieser Funktion k�nnen Sie den Whitespace automatisch vor der Suche entfernen. Weiterhin haben Sie die M�glichkeit, offensichtlich ung�ltige E-Mail-Adressen l�schen zu lassen. Eintr�ge mit leeren E-Mail-Adressen werden allerdings nicht gel�scht.',
		'verbose_selectTables' => 'Bitte w�hlen Sie aus, in welchen Tabellen nach Dubletten gesucht werden soll. Wenn Sie alle m�glichen Dubletten finden m�chten, lassen Sie einfach alles angew�hlt.',

		'message_notCorrectlyDeleted' => 'Dieser Datensatz wurde nicht korrekt aus <code>tt_address</code> gel�scht!',
	),
	'no' => Array (
	),
	'it' => Array (
	),
	'fr' => Array (
	),
	'es' => Array (
	),
	'nl' => Array (
	),
	'cz' => Array (
	),
	'pl' => Array (
	),
	'si' => Array (
	),
	'fi' => Array (
	),
	'tr' => Array (
	),
	'se' => Array (
	),
	'pt' => Array (
	),
	'ru' => Array (
	),
	'ro' => Array (
	),
	'ch' => Array (
	),
	'sk' => Array (
	),
	'lt' => Array (
	),
	'is' => Array (
	),
	'hr' => Array (
	),
	'hu' => Array (
	),
	'gl' => Array (
	),
	'th' => Array (
	),
	'gr' => Array (
	),
	'hk' => Array (
	),
	'eu' => Array (
	),
	'bg' => Array (
	),
	'br' => Array (
	),
	'et' => Array (
	),
	'ar' => Array (
	),
	'he' => Array (
	),
	'ua' => Array (
	),
	'lv' => Array (
	),
	'jp' => Array (
	),
	'vn' => Array (
	),
	'ca' => Array (
	),
	'ba' => Array (
	),
	'kr' => Array (
	),
	'eo' => Array (
	),
	'my' => Array (
	),
);
?>