<?php
require 'includes/db.php';

try {
    // Récupération des catégories
    $categoriesStmt = $pdo->query("SELECT id, name, image FROM categories");
    $categories = $categoriesStmt->fetchAll();

    // Récupération des promotions
    $promotionsStmt = $pdo->query("
        SELECT p.id, p.name, p.price, p.description, c.image AS category_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.stock > 0 AND p.promotion IS NOT NULL
    ");
    $promotions = $promotionsStmt->fetchAll();

    // Produits best-sellers ou populaires (exemple avec random)
    $popularStmt = $pdo->query("
        SELECT p.id, p.name, p.price, p.description, c.image AS category_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.stock > 0
        ORDER BY RANDOM()
        LIMIT 5
    ");
    $popular = $popularStmt->fetchAll();

    // Tous les produits
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.price, p.description, p.category_id, c.image AS category_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.stock > 0
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <!-- Banderole d'annonces -->
    <div class="bg-blue-600 text-white text-center py-2">
        Livraison gratuite à partir de 50 € ! | -30% sur les accessoires ce mois-ci !
    </div>

    <!-- Carrousel des promotions -->
    <div class="bg-gray-100 py-4 px-6">
        <h2 class="text-xl font-bold mb-4">Promotions</h2>
        <div class="flex overflow-x-auto gap-4">
            <?php foreach ($promotions as $promo): ?>
                <div class="min-w-[300px] bg-white rounded-lg shadow-md p-4 relative">
                    <img 
                        src="assets/images/legumes.png<?= htmlspecialchars($promo['category_image']) ?>" 
                        alt="<?= htmlspecialchars($promo['name']) ?>" 
                        class="w-full h-40 object-cover rounded-lg mb-4"
                    >
                    <span class="absolute top-2 left-2 bg-red-500 text-white text-sm px-2 py-1 rounded">-30 %</span>
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($promo['name']) ?></h3>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($promo['description']) ?></p>
                    <p class="text-green-600 font-bold mt-2"><?= number_format($promo['price'], 2) ?> €</p>
                    <button 
                        onclick="addToCart(<?= $promo['id'] ?>, '<?= htmlspecialchars($promo['name']) ?>', <?= $promo['price'] ?>)" 
                        class="bg-green-500 text-white px-4 py-2 rounded mt-4 hover:bg-green-600"
                    >
                        Ajouter au panier
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Slider des best-sellers -->
    <div class="bg-gray-100 py-4 px-6">
        <h2 class="text-xl font-bold mb-4">Best-Sellers</h2>
        <div class="flex overflow-x-auto gap-4">
            <?php foreach ($popular as $item): ?>
                <div class="min-w-[300px] bg-white rounded-lg shadow-md p-4">
                    <img 
                        src="assets/images/<?= htmlspecialchars($item['category_image']) ?>" 
                        alt="<?= htmlspecialchars($item['name']) ?>" 
                        class="w-full h-40 object-cover rounded-lg mb-4"
                    >
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="text-gray-500 text-sm"><?= htmlspecialchars($item['description']) ?></p>
                    <p class="text-green-600 font-bold mt-2"><?= number_format($item['price'], 2) ?> €</p>
                    <button 
                        onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>', <?= $item['price'] ?>)" 
                        class="bg-green-500 text-white px-4 py-2 rounded mt-4 hover:bg-green-600"
                    >
                        Ajouter au panier
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sous-navbar des catégories -->
    <div class="bg-white shadow-md py-4 px-6 mb-6">
        <div class="overflow-x-auto flex gap-4 items-center">
            <button 
                class="py-2 px-4 bg-gray-200 rounded-lg hover:bg-gray-300 transition" 
                onclick="filterProducts('all')">
                Toutes
            </button>
            <?php foreach ($categories as $category): ?>
                <button 
                    class="py-2 px-4 bg-gray-200 rounded-lg hover:bg-gray-300 transition" 
                    onclick="filterProducts(<?= $category['id'] ?>)">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Section des produits -->
    <div class="max-w-6xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-8">Nos Produits</h1>
        <div id="product-container" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
            <div 
                class="bg-white rounded-lg shadow-md p-4 relative product-card" 
                data-category="<?= $product['category_id'] ?>">
                <img 
                    src="assets/images/<?= htmlspecialchars($product['category_image'] ?: 'default.png') ?>" 
                    alt="<?= htmlspecialchars($product['name']) ?>" 
                    class="w-full h-40 object-cover rounded-lg mb-4"
                >
                <h2 class="text-lg font-bold"><?= htmlspecialchars($product['name']) ?></h2>
                <p class="text-gray-500 text-sm mt-2"><?= htmlspecialchars($product['description']) ?></p>
                <p class="text-green-600 font-bold mt-2"><?= number_format($product['price'], 2) ?> €</p>
                <button 
                    onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price'] ?>)" 
                    class="bg-green-500 text-white px-4 py-2 rounded mt-4 hover:bg-green-600"
                >
                    Ajouter au panier
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function filterProducts(categoryId) {
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                if (categoryId === 'all' || card.dataset.category == categoryId) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function addToCart(id, name, price) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const item = cart.find(product => product.id === id);

            if (item) {
                item.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            showNotification('Produit ajouté au panier !');
        }

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const count = cart.reduce((acc, item) => acc + item.quantity, 0);
            const cartNotification = document.getElementById('cart-notification');
            if (cartNotification) {
                cartNotification.textContent = count;
            }
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.className = 'fixed top-16 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg';
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
</body>
</html>
