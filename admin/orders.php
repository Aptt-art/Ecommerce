<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

// Traitement des actions d'acceptation ou de refus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'];

    if ($orderId && in_array($action, ['accept', 'reject'])) {
        try {
            if ($action === 'accept') {
                $stmt = $pdo->prepare("UPDATE commandes SET statut_preparation = 'acceptée' WHERE id_commande = :order_id");
            } elseif ($action === 'reject') {
                $stmt = $pdo->prepare("UPDATE commandes SET statut_preparation = 'refusée' WHERE id_commande = :order_id");
            }

            $stmt->execute(['order_id' => $orderId]);
            $message = "Commande #$orderId mise à jour avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour de la commande.";
        }
    } else {
        $message = "Données invalides.";
    }
}

// Récupérer toutes les commandes
$orders = $pdo->query("
    SELECT c.*, m.pseudo_membre, m.email 
    FROM commandes c
    LEFT JOIN membres m ON c.id_membre = m.id_membre
    ORDER BY c.date_commande DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'navbar_admin.php'; ?>

    <div class="max-w-6xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Gestion des Commandes</h1>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded shadow">
            <?php if (empty($orders)): ?>
                <p class="text-center p-4 text-gray-500">Aucune commande à afficher.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="p-4 border-b">
                        <p><strong>Commande #<?= $order['id_commande'] ?></strong></p>
                        <p>Membre : <?= htmlspecialchars($order['pseudo_membre'] ?? 'Inconnu') ?> (<?= htmlspecialchars($order['email'] ?? 'N/A') ?>)</p>
                        <p>Date : <?= htmlspecialchars($order['date_commande']) ?></p>
                        <p>Montant : <?= number_format($order['montant_total'], 2) ?> €</p>
                        <p>
                            Statut : 
                            <span class="px-2 py-1 rounded <?= $order['statut_preparation'] === 'en attente' ? 'bg-yellow-200 text-yellow-700' : ($order['statut_preparation'] === 'acceptée' ? 'bg-green-200 text-green-700' : 'bg-red-200 text-red-700') ?>">
                                <?= htmlspecialchars($order['statut_preparation']) ?>
                            </span>
                        </p>
                        <?php if ($order['statut_preparation'] === 'en attente'): ?>
                            <form method="POST" class="flex gap-4 mt-2">
                                <input type="hidden" name="order_id" value="<?= $order['id_commande'] ?>">
                                <button name="action" value="accept" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Accepter</button>
                                <button name="action" value="reject" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Refuser</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
