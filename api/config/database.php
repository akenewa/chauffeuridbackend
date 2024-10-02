<?php
class Database {
    private $host = "localhost";
    private $db_name = "";
    private $username = "";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");

            // Appeler la méthode pour créer les tables nécessaires
            $this->createTables();
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    private function createTables() {
        // Définition de la table des chauffeurs avec toutes les colonnes
        $chauffeursColumns = [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'nom' => 'VARCHAR(100) NOT NULL',
            'prenom' => 'VARCHAR(100) NOT NULL',
            'statut' => 'VARCHAR(50) NOT NULL',
            'photo_profil' => 'VARCHAR(255)',
            'vehicule' => 'VARCHAR(255) NOT NULL',
            'immatriculation' => 'VARCHAR(100) NOT NULL',
            'contacts' => 'VARCHAR(255)',
            'syndicat' => 'VARCHAR(100)'
        ];
        $this->createOrUpdateTable('chauffeurs', $chauffeursColumns);

        // Définition de la table des utilisateurs avec toutes les colonnes
        $usersColumns = [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'username' => 'VARCHAR(100) NOT NULL',
            'password' => 'VARCHAR(255) NOT NULL',
            'role' => 'VARCHAR(50) NOT NULL DEFAULT \'admin\''
        ];
        $this->createOrUpdateTable('users', $usersColumns);

        // Vérification de l'existence d'un utilisateur dans la table
        $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM users");
        $stmtCheck->execute();
        $userCount = $stmtCheck->fetchColumn();

        if ($userCount == 0) {
            // Création d'un utilisateur administrateur par défaut
            $defaultUsername = 'admin';
            $defaultPassword = password_hash('password', PASSWORD_DEFAULT); // Hasher le mot de passe pour plus de sécurité
            $stmtInsert = $this->conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmtInsert->bindParam(':username', $defaultUsername);
            $stmtInsert->bindParam(':password', $defaultPassword);
            $stmtInsert->execute();
        }
    }

    private function createOrUpdateTable($tableName, $columns) {
        // Vérification de l'existence de la table
        $tableExists = $this->conn->query("SHOW TABLES LIKE '$tableName'")->rowCount() > 0;

        if (!$tableExists) {
            // Création de la table
            $columnsDefinition = implode(", ", array_map(
                function($name, $definition) { return "$name $definition"; },
                array_keys($columns), $columns
            ));
            $createQuery = "CREATE TABLE $tableName ($columnsDefinition)";
            $this->conn->exec($createQuery);
        } else {
            // Mise à jour de la table : ajout des colonnes manquantes
            $existingColumns = $this->getColumns($tableName);
            foreach ($columns as $columnName => $columnDefinition) {
                if (!in_array($columnName, $existingColumns)) {
                    $alterQuery = "ALTER TABLE $tableName ADD $columnName $columnDefinition";
                    $this->conn->exec($alterQuery);
                }
            }
        }
    }

    private function getColumns($tableName) {
        $query = "SHOW COLUMNS FROM $tableName";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
