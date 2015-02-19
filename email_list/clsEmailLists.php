<?php

class EmailLists extends ObjCollection {
	
	public function loadAll() {
		parent::clear();
		$rs = EmailListDataManager::loadAll();
		foreach ($rs as $row) {
			parent::add(EmailList::loadByID($row['EmailListID'], $row));
		}
	}
	
}