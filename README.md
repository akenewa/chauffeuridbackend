Cette API permet de gérer les informations des chauffeurs et des véhicules pour Akwary Group.

Créer un chauffeur
POST /api/chauffeurs/create.php

Créer un nouveau chauffeur avec les informations nécessaires.

Lire un chauffeur
GET /api/chauffeurs/read.php?id={id}

Obtenir les détails d'un chauffeur spécifique en utilisant son identifiant unique.

Lire tous les chauffeurs
GET /api/chauffeurs/read_all.php

Obtenir la liste de tous les chauffeurs enregistrés dans le système.

Mettre à jour un chauffeur
PUT /api/chauffeurs/update.php

Mettre à jour les informations d'un chauffeur existant.

Supprimer un chauffeur
DELETE /api/chauffeurs/delete.php

Supprimer un chauffeur de la base de données.

Connexion utilisateur
POST /api/users/login.php

Authentification des utilisateurs pour accéder aux fonctionnalités de gestion.

Télécharger une image
POST /api/upload_image.php?id={id}

Télécharger ou mettre à jour l'image de profil d'un chauffeur.
