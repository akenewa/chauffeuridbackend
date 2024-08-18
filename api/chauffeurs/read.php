<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $chauffeur_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($chauffeur_id > 0) {
        $query = "SELECT * FROM chauffeurs WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $chauffeur_id);
        $stmt->execute();

        $chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($chauffeur) {
            // Déterminer les extensions d'images possibles
            $possible_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $image_path = null;

            // Vérifier l'existence de l'image avec chaque extension
            foreach ($possible_extensions as $extension) {
                $potential_path = "/backend/api/images/photo_" . $chauffeur_id . "." . $extension;
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $potential_path)) {
                    $image_path = $potential_path;
                    break;
                }
            }

            // Ajouter le chemin de l'image au résultat, ou null s'il n'y a pas d'image
            $chauffeur['photo_profil'] = $image_path;

            http_response_code(200); // OK
            echo json_encode($chauffeur);
        } else {
            http_response_code(404); // Non trouvé
            echo json_encode(array("message" => "Chauffeur non trouvé."));
        }
    } else {
        http_response_code(400); // Mauvaise requête
        echo json_encode(array("message" => "ID de chauffeur invalide."));
    }
} catch (Exception $e) {
    http_response_code(500); // Erreur interne du serveur
    echo json_encode(array("message" => "Erreur serveur : " . $e->getMessage()));
}
?>