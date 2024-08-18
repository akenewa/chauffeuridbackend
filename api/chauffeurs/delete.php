<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Lire le contenu JSON de la requête
    $data = json_decode(file_get_contents("php://input"));

    // Vérification de l'existence de l'ID
    if (isset($data->id) && is_numeric($data->id)) {
        $id = intval($data->id);

        // Récupérer les informations du chauffeur avant de le supprimer
        $query = "SELECT * FROM chauffeurs WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chauffeur) {
            // Suppression du chauffeur de la base de données
            $query = "DELETE FROM chauffeurs WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Suppression des fichiers d'image associés
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/backend/api/images/";
                $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];

                foreach ($allowed_file_types as $extension) {
                    $file_path = $target_dir . "photo_" . $id . "." . $extension;
                    if (file_exists($file_path)) {
                        unlink($file_path); // Supprime le fichier
                    }
                }

                http_response_code(200); // OK
                echo json_encode(array("message" => "Chauffeur et image(s) supprimés avec succès."));
            } else {
                http_response_code(503); // Service indisponible
                echo json_encode(array("message" => "Impossible de supprimer le chauffeur."));
            }
        } else {
            http_response_code(404); // Non trouvé
            echo json_encode(array("message" => "Chauffeur non trouvé."));
        }
    } else {
        http_response_code(400); // Mauvaise requête
        echo json_encode(array("message" => "ID de chauffeur manquant ou invalide."));
    }
} catch (Exception $e) {
    http_response_code(500); // Erreur interne du serveur
    echo json_encode(array("message" => "Erreur serveur : " . $e->getMessage()));
}
?>