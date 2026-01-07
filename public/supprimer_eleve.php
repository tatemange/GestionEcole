<?php
require_once '../config/db.php';
require_once '../config/session.php';
check_admin();

$id = $_GET['id'] ?? null;
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM eleves WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // En prod, on loggerait l'erreur
        // die("Erreur : " . $e->getMessage());
    }
}
header("Location: eleves.php");
exit;
?>
