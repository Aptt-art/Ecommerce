<?php
session_start();
require 'includes/db.php';

// Récupération des données envoyées par le client
$data = json_decode(file_get_contents('php://input'), true);

// Vérification des données
if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données invalides ou panier vide.']);
    exit();
}

// Données de la commande
$cart = $data['cart'];
$total = array_reduce($cart, function ($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);
$adresseFacturation = $data['adresse_facturation'] ?? 'Adresse inconnue';
$adresseLivraison = $data['adresse_livraison'] ?? 'Adresse inconnue';

// Enregistrement dans la table commandes
try {
    $pdo->beginTransaction();

    // Insérer la commande
    $stmt = $pdo->prepare("
        INSERT INTO commandes (date_commande, statut_preparation, montant_ht, montant_ttc, adresse_facturation, adresse_livraison, id_membre, montant_total)
        VALUES (NOW(), 'en attente', :montant_ht, :montant_ttc, :adresse_facturation, :adresse_livraison, :id_membre, :montant_total)
    ");
    $stmt->execute([
        'montant_ht' => $total * 0.83, // Exemple de calcul HT
        'montant_ttc' => $total,       // Total TTC
        'adresse_facturation' => $adresseFacturation,
        'adresse_livraison' => $adresseLivraison,
        'id_membre' => 1,             // ID temporaire pour client non connecté
        'montant_total' => $total,
    ]);

    $orderId = $pdo->lastInsertId();

    // Insérer les articles dans la table contenir
    $stmt = $pdo->prepare("
        INSERT INTO contenir (id_commande, id_article, quantite)
        VALUES (:id_commande, :id_article, :quantite)
    ");
    foreach ($cart as $item) {
        $stmt->execute([
            'id_commande' => $orderId,
            'id_article' => $item['id'],
            'quantite' => $item['quantity'],
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : ' . $e->getMessage()]);
}
