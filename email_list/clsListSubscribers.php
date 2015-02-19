<?php

class ListSubscribers extends ObjCollection {
	
	public function loadActiveByList($list) {
		parent::clear();
		$elid = (is_object($list))? $list->getID() : $list;
		$rs = ListSubscriberDataManager::loadActiveByList($elid);
		foreach ($rs as $row) {
			parent::add(ListSubscriber::loadByID($row['ListSubscriberID'], $row));
		}
	}
	
	public function loadByList($list) {
		parent::clear();
		$elid = (is_object($list))? $list->getID() : $list;
		$rs = ListSubscriberDataManager::loadByList($elid);
		foreach ($rs as $row) {
			parent::add(ListSubscriber::loadByID($row['ListSubscriberID'], $row));
		}
	}
	
}