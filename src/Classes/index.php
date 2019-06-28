<?php
/* Permet de détruire la session et de rediriger vers la page d'accueil. */
    session_start();
    session_destroy();

    header("Location: ../../Public/index.php");