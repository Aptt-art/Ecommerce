<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Récupération des données
try {
    // Commandes par jour
    $ordersByDay = $pdo->query("
        SELECT TO_CHAR(created_at, 'YYYY-MM-DD') as day, COUNT(*) as count 
        FROM orders 
        GROUP BY day
        ORDER BY day ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Revenus par jour
    $revenueByDay = $pdo->query("
        SELECT TO_CHAR(created_at, 'YYYY-MM-DD') as day, SUM(total) as amount
        FROM orders
        GROUP BY day
        ORDER BY day ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Commandes par catégorie
    $ordersByCategory = $pdo->query("
        SELECT c.name as category, COUNT(*) as count
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        GROUP BY c.name
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des statistiques : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white py-4 mb-6">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-lg font-bold">Admin Dashboard</a>
            <div class="flex gap-4">
                <a href="categories.php" class="hover:text-gray-300">Catégories</a>
                <a href="products.php" class="hover:text-gray-300">Produits</a>
                <a href="orders.php" class="hover:text-gray-300">Commandes</a>
                <a href="stats.php" class="hover:text-gray-300">Statistiques</a>
                <a href="../logout.php" class="hover:text-gray-300">Déconnexion</a>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Statistiques</h1>

        <!-- Graphique des commandes par jour -->
        <div class="mb-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Commandes par jour</h2>
            <canvas id="ordersChart"></canvas>
        </div>

        <!-- Graphique des revenus par jour -->
        <div class="mb-8 bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Revenus par jour</h2>
            <canvas id="revenueChart"></canvas>
        </div>

        <!-- Commandes par catégorie -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Commandes par catégorie</h2>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Scripts Chart.js -->
    <script>
    // Commandes par jour
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($ordersByDay)) ?>,
            datasets: [{
                label: 'Commandes par jour',
                data: <?= json_encode(array_values($ordersByDay)) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        }
    });

    // Revenus par jour
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($revenueByDay)) ?>,
            datasets: [{
                label: 'Revenus par jour (€)',
                data: <?= json_encode(array_values($revenueByDay)) ?>,
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 2,
                fill: true
            }]
        }
    });

    // Commandes par catégorie
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($ordersByCategory, 'category')) ?>,
            datasets: [{
                label: 'Commandes par catégorie',
                data: <?= json_encode(array_column($ordersByCategory, 'count')) ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.5)',
                    'rgba(34, 197, 94, 0.5)',
                    'rgba(234, 88, 12, 0.5)',
                    'rgba(220, 38, 38, 0.5)'
                ]
            }]
        }
    });
    </script>
</body>
</html>
