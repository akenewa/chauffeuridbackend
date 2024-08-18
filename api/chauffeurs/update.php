<?php

header('Content-Type: application/json');

include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/config/database.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/upload_image.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Récupération des données POST
    $data = $_POST;
    $files = $_FILES;

    // Vérification des données essentielles et de l'existence de l'ID
    if (!empty($data['id']) && !empty($data['nom']) && !empty($data['prenom']) && !empty($data['statut']) && !empty($data['vehicule']) && !empty($data['immatriculation']) && !empty($data['contacts']) && !empty($data['syndicat'])) {
        $chauffeur_id = intval($data['id']);

        // Préparation de la requête de mise à jour
        $query = "UPDATE chauffeurs SET nom = :nom, prenom = :prenom, statut = :statut, 
                  vehicule = :vehicule, immatriculation = :immatriculation, contacts = :contacts, syndicat = :syndicat
                  WHERE id = :id";
        $stmt = $db->prepare($query);

        // Liaison des paramètres
        $stmt->bindParam(':id', $chauffeur_id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $data['nom']);
        $stmt->bindParam(':prenom', $data['prenom']);
        $stmt->bindParam(':statut', $data['statut']);
        $stmt->bindParam(':vehicule', $data['vehicule']);
        $stmt->bindParam(':immatriculation', $data['immatriculation']);
        $stmt->bindParam(':contacts', $data['contacts']);
        $stmt->bindParam(':syndicat', $data['syndicat']);

        // Exécution de la requête de mise à jour
        if ($stmt->execute()) {
            $image_upload_success = true;
            $image_path = null;

            // Vérification de l'existence de l'ancienne image
            $stmtCheck = $db->prepare("SELECT photo_profil FROM chauffeurs WHERE id = :id");
            $stmtCheck->bindParam(':id', $chauffeur_id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $old_image_path = $result['photo_profil'];

            // Gestion de l'image si elle est présente dans les fichiers téléchargés
            if (isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                // Supprimer l'ancienne image si elle existe
                if ($old_image_path && file_exists($_SERVER['DOCUMENT_ROOT'] . $old_image_path)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $old_image_path);
                }

                $upload_result = uploadImage($files['photo'], $chauffeur_id);

                if ($upload_result['success']) {
                    $image_path = $upload_result['file_path'];
                    // Mise à jour du chemin de l'image dans la base de données
                    $stmt = $db->prepare("UPDATE chauffeurs SET photo_profil = :photo_profil WHERE id = :id");
                    $stmt->bindParam(':photo_profil', $image_path);
                    $stmt->bindParam(':id', $chauffeur_id);
                    if (!$stmt->execute()) {
                        $image_upload_success = false;
                        error_log("Erreur lors de la mise à jour de l'image dans la base de données.");
                    }
                } else {
                    $image_upload_success = false;
                    error_log($upload_result['message']);
                }
            }

            if ($image_upload_success) {
                http_response_code(200); // OK
                echo json_encode([
                    "message" => "Chauffeur mis à jour avec succès.",
                    "id" => $chauffeur_id,
                    "photo_profil" => $image_path
                ]);
            } else {
                http_response_code(500); // Erreur interne du serveur
                echo json_encode([
                    "message" => "Chauffeur mis à jour, mais erreur lors de l'upload de l'image."
                ]);
            }
        } else {
            http_response_code(503); // Service indisponible
            echo json_encode(["message" => "Impossible de mettre à jour le chauffeur."]);
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