<?php
if (!defined('PONMONITOR')) {
    die('Hacking attempt!');
}
function getSwitchById($pdo, $olt, $pole = '*') {
    if (is_array($pole)) {
        $pole = implode(',', $pole);
    }
    $sql = "SELECT {$pole} FROM switch WHERE id = :olt";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':olt', $olt, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getOnuById($pdo, $idonu, $pole = '*') {
    if (is_array($pole)) {
        $pole = implode(',', $pole);
    }
    $sql = "SELECT {$pole} FROM onus WHERE idonu = :idonu";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idonu', $idonu, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getOntById($pdo, $idonu, $pole = '*') {
    if (is_array($pole)) {
        $pole = implode(',', $pole);
    }
    $sql = "SELECT {$pole} FROM onus WHERE idonu = :idonu";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idonu', $idonu, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function pdoFast($pdo, $table, $field, $where) {
    $conditions = [];
    foreach ($where as $key => $val) {
        $conditions[] = "`$key` = :$key";
    }
    $sql = "SELECT `$field` FROM `$table` WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($where);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function pdoInsert($pdo, $table, $data) {
    $fields = array_keys($data);
    $placeholders = array_map(function($field) { return ":$field"; }, $fields);
    
    $sql = "INSERT INTO `$table` (`" . implode('`,`', $fields) . "`) 
            VALUES (" . implode(',', $placeholders) . ")";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);

    // Повертає ID вставленого рядка
    return $pdo->lastInsertId();
}

?>