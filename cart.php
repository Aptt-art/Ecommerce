<?php
session_start();
require 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-6xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold mb-6">Votre Panier</h1>
        <div id="cart-items" class="bg-white p-4 rounded shadow">
            <!-- Les articles du panier seront affichés dynamiquement via JS -->
        </div>
        <div class="text-right mt-4">
            <button onclick="checkout()" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Passer la commande</button>
        </div>
    </div>

    <script>
        // Afficher les articles du panier
        document.addEventListener('DOMContentLoaded', () => {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const cartItemsContainer = document.getElementById('cart-items');
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<p class="text-center text-gray-500">Votre panier est vide.</p>';
                return;
            }

            let total = 0;
            cartItemsContainer.innerHTML = cart.map(item => {
                total += item.price * item.quantity;
                return `
                    <div class="flex justify-between items-center p-4 border-b">
                        <div>
                            <h2 class="font-bold">${item.name}</h2>
                            <p>${item.price.toFixed(2)} € × ${item.quantity}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="updateQuantity(${item.id}, -1)" class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">-</button>
                            <span>${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, 1)" class="px-3 py-1 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">+</button>
                            <button onclick="removeItem(${item.id})" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Supprimer</button>
                        </div>
                    </div>
                `;
            }).join('') + `
                <div class="p-4 text-right">
                    <strong>Total : ${total.toFixed(2)} €</strong>
                </div>
            `;
        });

        // Modifier la quantité d'un article
        function updateQuantity(productId, delta) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const item = cart.find(p => p.id === productId);
            if (item) {
                item.quantity = Math.max(1, item.quantity + delta);
                localStorage.setItem('cart', JSON.stringify(cart));
                location.reload();
            }
        }

        // Supprimer un article
        function removeItem(productId) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            cart = cart.filter(p => p.id !== productId);
            localStorage.setItem('cart', JSON.stringify(cart));
            location.reload();
        }

        // Passer la commande
        function checkout() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            if (cart.length === 0) {
                alert('Votre panier est vide.');
                return;
            }

            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart, adresse_facturation: "123 Rue Exemple", adresse_livraison: "123 Rue Exemple" })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Commande passée avec succès.');
                    localStorage.removeItem('cart');
                    location.reload();
                } else {
                    alert('Erreur : ' + data.error);
                }
            })
            .catch(err => alert('Erreur réseau : ' + err.message));
        }
    </script>
</body>
</html>
