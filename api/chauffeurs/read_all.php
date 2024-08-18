<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT id, nom, prenom, statut, photo_profil, vehicule, immatriculation FROM chauffeurs";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $chauffeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($chauffeurs) {
        http_response_code(200); // OK
        echo json_encode($chauffeurs);
    } else {
        http_response_code(404); // Non trouvé
        echo json_encode(array("message" => "Aucun chauffeur trouvé."));
    }
} catch (Exception $e) {
    http_response_code(500); // Erreur interne du serveur
    echo json_encode(array("message" => "Erreur serveur : " . $e->getMessage()));
}
?>