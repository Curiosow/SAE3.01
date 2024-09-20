<?php

function getCollegueFullName($id) {
    if(!isset($id)) {
        return '';
    }

    $preparedStatement = "SELECT * FROM collegue WHERE id=$1";

    $connexion = pg_connect("host=iutinfo-sgbd.uphf.fr port=5432 dbname=edt user=iutinfo315 password=MDPToSAE22!");
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($id));

    $row = pg_fetch_assoc($result);
    return $row['prenom'] . ' ' . $row['nom'];
}