<?php
session_start();
require '../includes/db.php';
include 'navbar_admin.php';

// Vérification de l'accès administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';
$productToEdit = null; // Contiendra les informations du produit à modifier

// Vérifier si une modification est demandée
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'edit') {
    $productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($productId) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $productToEdit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$productToEdit) {
            $error = "Produit introuvable.";
        }
    } else {
        $error = "ID de produit invalide.";
    }
}

// Gestion de la mise à jour d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $productId = (int)$_POST['id'];
    $name = htmlspecialchars($_POST['name'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : null;
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = htmlspecialchars($_POST['description'] ?? '');

    if (empty($name) || is_null($price) || is_null($stock) || is_null($categoryId)) {
        $error = "Tous les champs sont obligatoires pour la modification.";
    } elseif ($price <= 0 || $stock < 0) {
        $error = "Le prix doit être supérieur à zéro et le stock ne peut pas être négatif.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = :name, price = :price, stock = :stock, category_id = :category_id, description = :description 
                WHERE id = :id
            ");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'category_id' => $categoryId,
                'description' => $description,
                'id' => $productId,
            ]);
            $success = "Produit mis à jour avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification du produit : " . $e->getMessage();
        }
    }
}

// Gestion de l'ajout de produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : null;
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = htmlspecialchars($_POST['description'] ?? '');

    if (empty($name) || is_null($price) || is_null($stock) || is_null($categoryId)) {
        $error = "Tous les champs sont obligatoires pour l'ajout.";
    } elseif ($price <= 0 || $stock < 0) {
        $error = "Le prix doit être supérieur à zéro et le stock ne peut pas être négatif.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Un produit avec ce nom existe déjà.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, price, stock, category_id, description) 
                    VALUES (:name, :price, :stock, :category_id, :description)
                ");
                $stmt->execute([
                    'name' => $name,
                    'price' => $price,
                    'stock' => $stock,
                    'category_id' => $categoryId,
                    'description' => $description,
                ]);
                $success = "Produit ajouté avec succès.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout du produit : " . $e->getMessage();
        }
    }
}

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les produits
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Gestion des Produits</title>
</head>
<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Gestion des Produits</h1>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Formulaire d'ajout ou de modification de produit -->
        <form method="POST" class="bg-white p-6 rounded shadow mb-8">
            <input type="hidden" name="id" value="<?= $productToEdit['id'] ?? '' ?>">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold mb-2">Nom du produit</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($productToEdit['name'] ?? '') ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block font-bold mb-2">Prix (€)</label>
                    <input type="number" step="0.01" name="price" value="<?= $productToEdit['price'] ?? '' ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block font-bold mb-2">Stock</label>
                    <input type="number" name="stock" value="<?= $productToEdit['stock'] ?? '' ?>" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block font-bold mb-2">Catégorie</label>
                    <select name="category_id" required class="w-full p-2 border rounded">
                        <option value="" disabled <?= !isset($productToEdit['category_id']) ? 'selected' : '' ?>>Choisir une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= isset($productToEdit['category_id']) && $productToEdit['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block font-bold mb-2">Description</label>
                    <textarea name="description" rows="4" required class="w-full p-2 border rounded"><?= htmlspecialchars($productToEdit['description'] ?? '') ?></textarea>
                </div>
            </div>
            <button type="submit" name="<?= $productToEdit ? 'update_product' : 'add_product' ?>" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <?= $productToEdit ? 'Modifier le produit' : 'Ajouter le produit' ?>
            </button>
        </form>

        <!-- Liste des produits -->
        <div class="bg-white rounded shadow">
            <div class="grid grid-cols-6 font-bold p-4 border-b">
                <div>Nom</div>
                <div>Catégorie</div>
                <div>Prix</div>
                <div>Stock</div>
                <div colspan="2" class="text-right">Actions</div>
            </div>
            <?php foreach ($products as $product): ?>
                <div class="grid grid-cols-6 items-center p-4 border-b">
                    <div><?= htmlspecialchars($product['name'] ?? 'Produit inconnu') ?></div>
                    <div><?= htmlspecialchars($product['category_name'] ?? 'Sans catégorie') ?></div>
                    <div><?= number_format($product['price'], 2) ?>€</div>
                    <div><?= $product['stock'] ?></div>
                    <div>
                        <a href="products.php?action=edit&id=<?= $product['id'] ?>" class="text-blue-600 hover:text-blue-800">Modifier</a>
                    </div>
                    <div>
                        <a href="products.php?action=delete&id=<?= $product['id'] ?>" 
                           class="text-red-600 hover:text-red-800"
                           onclick="return confirm('Supprimer ce produit ?')">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
