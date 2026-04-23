<?php
require_once __DIR__ . '/Config/Define.php';
require_once __DIR__ . '/Config/AutoLoad.php';
require_once __DIR__ . '/Config/Conexion.php';

AutoLoad::run();

try {
    $pdo  = Conexion::getInstance();
    $stmt = $pdo->prepare("CALL sp_combos_findAll()");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    echo "SP funciona. Filas: " . count($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}