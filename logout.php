<?php
session_start();

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil avec un message
header('Location: ../index.php?message=logout_success');
exit();
?>
