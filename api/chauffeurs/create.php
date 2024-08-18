<?php

header('Content-Type: application/json');

include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/config/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/upload_image.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Récupération des données POST
    $data = $_POST;

    // Vérification des données essentielles
    if (
        !empty($data['nom']) &&
        !empty($data['prenom']) &&
        !empty($data['statut']) &&
        !empty($data['vehicule']) &&
        !empty($data['immatriculation']) &&
        !empty($data['contacts']) && 
        !empty($data['syndicat'])   
    ) {
        // Préparation de la requête d'insertion
        $query = "INSERT INTO chauffeurs (nom, prenom, statut, vehicule, immatriculation, contacts, syndicat)
                  VALUES (:nom, :prenom, :statut, :vehicule, :immatriculation, :contacts, :syndicat)";
        $stmt = $db->prepare($query);

        // Liaison des paramètres
        $stmt->bindParam(':nom', $data['nom']);
        $stmt->bindParam(':prenom', $data['prenom']);
        $stmt->bindParam(':statut', $data['statut']);
        $stmt->bindParam(':vehicule', $data['vehicule']);
        $stmt->bindParam(':immatriculation', $data['immatriculation']);
        $stmt->bindParam(':contacts', $data['contacts']);
        $stmt->bindParam(':syndicat', $data['syndicat']);

        // Exécution de la requête
        if ($stmt->execute()) {
            // Récupération de l'ID du chauffeur inséré
            $chauffeur_id = $db->lastInsertId();

            $image_upload_success = true;
            $image_path = null;

            // Gestion de l'image si elle est présente dans les fichiers téléchargés
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload_result = uploadImage($_FILES['photo'], $chauffeur_id);

                if ($upload_result['success']) {
                    $image_path = $upload_result['file_path'];
                    // Mise à jour du chemin de l'image dans la base de données
                    $stmt = $db->prepare("UPDATE chauffeurs SET photo_profil = :photo_profil WHERE id = :id");
                    $stmt->bindParam(':photo_profil', $image_path);
                    $stmt->bindParam(':id', $chauffeur_id);
                    if (!$stmt->execute()) {
                        $image_upload_success = false;
                    }
                } else {
                    $image_upload_success = false;
                }
            }

            if ($image_upload_success) {
                http_response_code(201); // Créé
                echo json_encode([
                    "message" => "Chauffeur créé avec succès.",
                    "id" => $chauffeur_id,
                    "photo_profil" => $image_path
                ]);
            } else {
                http_response_code(500); // Erreur interne du serveur
                echo json_encode([
                    "message" => "Chauffeur créé, mais erreur lors de l'upload de l'image."
                ]);
            }
        } else {
            http_response_code(503); // Service indisponible
            echo json_encode(["message" => "Impossible de créer le chauffeur."]);
        }
    } else {
        http_response_code(400); // Mauvaise requête
        echo json_encode(["message" => "Données incomplètes."]);
    }
} catch (Exception $e) {
    http_response_code(500); // Erreur interne du serveur
    echo json_encode(["message" => "Erreur serveur : " . $e->getMessage()]);
}
?>