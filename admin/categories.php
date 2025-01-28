<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Gestion des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = htmlspecialchars($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $image = 'default.png'; // Image par défaut

    if ($action === 'create' && !empty($name)) {
        // Upload image si présente
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            $image = basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }

        // Insertion de la catégorie
        $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
        $stmt->execute([$name, $image]);
    } elseif ($action === 'update' && $categoryId > 0) {
        // Modification de la catégorie
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            $image = basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $image, $categoryId]);
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $categoryId]);
        }
    } elseif ($action === 'delete' && $categoryId > 0) {
        // Suppression de la catégorie
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
    }
}

// Récupération des catégories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation Admin -->
    <?php include 'navbar_admin.php'; ?><br>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-8">Gestion des Catégories</h1>

        <!-- Formulaire d'ajout -->
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow mb-8">
            <h2 class="text-lg font-bold mb-4">Ajouter une catégorie</h2>
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2">Nom de la catégorie</label>
                    <input type="text" name="name" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-2">Image de la catégorie</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 border rounded">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Ajouter
            </button>
        </form>

        <!-- Liste des catégories -->
        <div class="bg-white rounded shadow">
            <h2 class="text-lg font-bold p-4 border-b">Liste des Catégories</h2>
            <?php foreach ($categories as $category): ?>
            <div class="flex items-center justify-between p-4 border-b">
                <div class="flex items-center gap-4">
                    <img src="../assets/images/<?= htmlspecialchars($category['image']) ?>" 
                         alt="<?= htmlspecialchars($category['name']) ?>" 
                         class="w-16 h-16 object-cover rounded">
                    <span class="text-lg"><?= htmlspecialchars($category['name']) ?></span>
                </div>
                <div class="flex gap-2">
                    <!-- Formulaire de modification -->
                    <form method="POST" enctype="multipart/form-data" class="flex gap-2">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" class="p-2 border rounded">
                        <input type="file" name="image" accept="image/*" class="p-2 border rounded">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Modifier
                        </button>
                    </form>
                    <!-- Formulaire de suppression -->
                    <form method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
