<?php
require_once 'db.php';
function getChatList($userId) {
	global $conn;
	$stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, s.status FROM chats c INNER JOIN users u ON u.id = c.from INNER JOIN session s on s.user_id = c.from where c.to = :userId group by(c.from)");
	$stmt->bindParam('userId', $userId);
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$fromResult = $stmt->fetchAll();
	$users = array();
	$frmIds= '';
	if($fromResult) {
		foreach($fromResult as $res) {
			$users[$res['id']]['name'] = $res['first_name'].' '. $res['last_name'];
			$users[$res['id']]['status'] = $res['status'];
		}
		$frmIds = implode(',', array_keys($users));
	}
	if($frmIds)
		$stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, s.status FROM chats c INNER JOIN users u ON u.id = c.to  INNER JOIN session s on s.user_id = c.from where c.from = :userId AND c.to NOT IN($frmIds) group by(c.from)");
	else 
		$stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, s.status FROM chats c INNER JOIN users u ON u.id = c.to  INNER JOIN session s on s.user_id = c.from where c.from = :userId group by(c.from)");
	$stmt->bindParam('userId', $userId);
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$toResult = $stmt->fetchAll();
	if($toResult) {
		foreach($toResult as $res) {
			$users[$res['id']]['name'] = $res['first_name'].' '. $res['last_name'];
			$users[$res['id']]['status'] = $res['status'];
		}
	}
	return $users;
	//print_r($users); die;
}
function changeUserStatus($userId = 0, $status = 1) {
	global $conn;
	$stmt = $conn->prepare("SELECT id FROM session where user_id = $userId");
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = $stmt->fetch();
	if($result) {
		$stmt = $conn->prepare("UPDATE session set status = :status where id = :id");
		$stmt->bindParam(':id', $result['id']);
		$stmt->bindParam(':status', $status);
		$stmt->execute();
	} else {
		$stmt = $conn->prepare("INSERT into session (user_id, status) VALUES (:userId, :status)");
		$stmt->bindParam(':userId', $userId);
		$stmt->bindParam(':status', $status);
		$stmt->execute();
	}
}
function getChatMsgs($userId, $otherUserId) {
	global $conn;
	$stmt = $conn->prepare("SELECT c.id, c.message, c.sent, c.recd, c.from, c.to FROM chats c where (c.from = :userId AND c.to = :anotherUserId) OR (c.from = :userId AND c.to = :anotherUserId)");
	$stmt->bindParam('userId', $userId);
	$stmt->bindParam('anotherUserId', $otherUserId);
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$res = $stmt->fetchAll();
	print_r($res);
	return $res;
}