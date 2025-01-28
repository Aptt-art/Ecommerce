<?php
session_start();
require '../includes/db.php';
include 'navbar_admin.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Récupération des statistiques
try {
    $ordersCount = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
    $totalRevenue = $pdo->query("SELECT SUM(montant_total) FROM commandes")->fetchColumn();
    $productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $categoriesCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body><br>

    <!-- Dashboard content -->
    <div class="max-w-6xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6">Bienvenue, Admin</h1>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-bold">Total Commandes</h2>
                <p class="text-3xl font-bold text-blue-600"><?= $ordersCount ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-bold">Revenu Total</h2>
                <p class="text-3xl font-bold text-green-600"><?= number_format($totalRevenue, 2) ?> €</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-bold">Produits</h2>
                <p class="text-3xl font-bold text-yellow-600"><?= $productsCount ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-bold">Catégories</h2>
                <p class="text-3xl font-bold text-purple-600"><?= $categoriesCount ?></p>
            </div>
        </div>

        <!-- Tableau des dernières commandes -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-bold mb-4">Dernières Commandes</h2>
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border-b-2 p-4 text-left">Client</th>
                        <th class="border-b-2 p-4 text-left">Total</th>
                        <th class="border-b-2 p-4 text-left">Statut</th>
                        <th class="border-b-2 p-4 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = $pdo->query("
                        SELECT o.*, u.email 
                        FROM commandes o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5
                    ")->fetchAll();

                    foreach ($orders as $order): ?>
                    <tr>
                        <td class="border-b p-4"><?= htmlspecialchars($order['email']) ?></td>
                        <td class="border-b p-4"><?= number_format($order['montant_total'], 2) ?> €</td>
                        <td class="border-b p-4"><?= htmlspecialchars($order['status']) ?></td>
                        <td class="border-b p-4"><?= htmlspecialchars($order['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
