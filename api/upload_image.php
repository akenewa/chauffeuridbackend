<?php

function uploadImage($file, $id) {
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/backend/api/images/";
    $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];

    // Vérifier si le fichier est une image réelle
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ["success" => false, "message" => "Le fichier n'est pas une image valide."];
    }

    // Vérifier la taille du fichier (ici limitée à 50MB)
    if ($file['size'] > 50000000) { // 50MB
        return ["success" => false, "message" => "Le fichier est trop grand. La taille maximale est de 50MB."];
    }

    // Vérifier le type de fichier
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowed_file_types)) {
        return ["success" => false, "message" => "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés."];
    }

    // Créer le répertoire de destination si nécessaire
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            return ["success" => false, "message" => "Erreur lors de la création du répertoire de destination."];
        }
    }

    // Définir le chemin complet du nouveau fichier
    $new_file_path = $target_dir . "photo_" . $id . "." . $fileType;

    // Supprimer l'ancienne image si elle existe
    foreach ($allowed_file_types as $extension) {
        $old_file = $target_dir . "photo_" . $id . "." . $extension;
        if (file_exists($old_file) && $old_file !== $new_file_path) {
            unlink($old_file);
        }
    }

    // Déplacer le fichier téléchargé dans le répertoire cible
    if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
        // Chemin relatif à partir de la racine du site
        $relative_path = "/backend/api/images/photo_" . $id . "." . $fileType;
        return ["success" => true, "file_path" => $relative_path];
    } else {
        return ["success" => false, "message" => "Erreur lors du téléchargement de l'image."];
    }
}

?>