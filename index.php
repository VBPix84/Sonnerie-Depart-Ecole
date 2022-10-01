<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <title>Départ Ecole</title>
  </head>
  <body>


<?php

/* 
function parseJson($nomDuFichier)

Transforme le fichier en argument en array lisible par PHP.
Utilisation d'une fonction car appelée à plusieurs endroit du code
*/
function parseJson($nomDuFichier){
  $parsedFile = json_decode(file_get_contents($nomDuFichier), true);
  return $parsedFile;
}

/* 
function formaterDate($i, $format)

Récupère la date actuelle, applique le décalage $i, applique le $format et retourne la variable formatée 
*/

function formaterDate($i, $format){
  $date = new DateTime();
  $date->modify('+ '.$i.' days');
  $dateFormatee = $date->format($format);

  return $dateFormatee;
}

/* 
Initialisation des variables :

  - $nomDuFichier => Nom du fichier calendrier
  - $parsedJson => Le fichier json décodé par la fonction est intégré dans la variable
  - $nbDatesAffichage => Nombre de jour à afficher en "mode normal"
*/
$nomDuFichier = "calDepartEcoleV2.json";
$parsedJson = parseJson($nomDuFichier);
$nbDatesAffichage = 14;

/*
Modes d'affichages : 

  - Normal : $_GET['admin'] n'existent pas // Affiche la semaine qui arrive (= $nbDatesAffichage)
  - Admin : $_GET['admin'] == true // Affiche toutes les dates dispo dans le json ("-1 pour enlever la date du jour")
*/

/*
Traitement du formulaire via $_POST

A la réception d'une demande d'enregistrement, un tableau est rempli avec les valeurs true ou false
pour le JSON (le fichier est COMPLETEMENT réécrit puis ré-enregistré)

  - $enregistrement => Stock le tableau en cours de réécriture
*/
?>

<div class="container col-10 col-sm-5 mt-3 content-align-center" style="margin-bottom : 75px;"> <!-- div qui contient le contenu -->

<?php

if(isset($_GET['admin']) == true) {
  $nbDatesAffichage = 60; // Nombre de date dans le fichier json = count($parsedJson);
  $nbDatesTotal = count($parsedJson);
  $nbDatesDepassees = 5;
  $btnDisabled = NULL;

  if($nbDatesDepassees >= 20) {
    $styleDateDepassee = "bg-danger";
    $stylebtnPurge = "btn-danger";
  } elseif ($nbDatesDepassees > 0) {
    $styleDateDepassee = "bg-warning";
    $stylebtnPurge = "btn-warning";
  } elseif ($nbDatesDepassees == 0) {
    $styleDateDepassee = "bg-success";
    $stylebtnPurge = "btn-outline-success";
    $btnDisabled = 'disabled';
  }

?>
  <nav class="navbar fixed-top navbar-dark bg-danger">
    <div class="container-fluid justify-content-center ">
      <span class="badge text-bg-light">Mode administrateur actif</span>
    </div>
  </nav>
  <div class="card my-5">
    <div class="card-header text-center">
      <b>Administration</b>
    </div>
    <div class="card-body">
        <ul class="list-group col-12">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Nb total d'entrées
            <span class="badge bg-primary"><?php echo $nbDatesTotal; ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Nb d'entrées affichées
            <span class="badge bg-primary"><?php echo $nbDatesAffichage; ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Nb de dates dépassées
            <span class="badge <?php echo $styleDateDepassee; ?>"><?php echo $nbDatesDepassees; ?></span>
          </li>
        </ul>
        <p class="list-group-item d-flex justify-content-center align-items-center mt-3">
          <button class="btn btn-sm <?php echo $stylebtnPurge; ?>" type="submit" <?php echo $btnDisabled; ?>>Purger les dates dépassées</button>
        </p>

    </div>
  </div>
<?php
}
?>

<div class="card">
  <div class="card-header text-center">
    <b>Départ Ecole</b>
  </div>
  <div class="card-body">

          <?php


          if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $enregistrement = array();

            /*
            Boucle for

            Boucle sur chaque entrée du formulaire:
              1. $_POST pour la date en cours existe : Elle est donc à true, on push $enregistrement[$dateJourYMD] = true
              2. $_POST pour la date en cours n'existe pas, on push $enregistrement[$dateJourYMD] = false
              
            On merge les 2 tableaux : L'ancien + Le nouveau pour ne mettre à jours QUE les entrées du formulaire, on ne
            touche pas aux autres

            Puis on intègre le tableau merge dans le fichier avec file_put_contents();

            */

            for($i = 0; $i <= $nbDatesAffichage; $i++) { 
              
              $dateJourYMD = formaterDate($i, 'Y-m-d');
              $enregistrement[$dateJourYMD] = (array_key_exists($dateJourYMD, $_POST)) ? true : false;
            }
            $aEnregistrer = array_merge($parsedJson, $enregistrement);
            file_put_contents($nomDuFichier, json_encode($aEnregistrer));

          ?>

        <!-- on affiche l'alerte : Données enregistrées -->
        <div class="alert alert-success d-flex align-items-center" role="alert">
          <div>
            <i class="bi bi-check-circle-fill"></i>&nbsp;Données enregistrées !
          </div>
        </div>
        
          
        <?php

          }

          /*
          Affichage du formulaire :

            Pour chaque entrée selon le mode d'affichage (Toutes les entrées ou 14 jours):
              - $dateJourYMD => Une case à cocher nommée avec la date format AAAAMMJJ
              - $dateFrançaise => variable stockant la date en format FR à afficher
              - $declenchement => Stock 'checked' si la valeur est true pour cocher la case
              - $i => On commence à 1 pour commencer à demain...

            Puis le bouton d'enregistrement (envoi du formulaire en mode $_POST[])

            On rappelle la fonction $parsenJson pour actualiser l'affichage même après l'envoi du formulaire
          */

          echo '<form method="post" action="#">';

          $parsedJson = parseJson($nomDuFichier);

          for($i = 1; $i < $nbDatesAffichage +1; $i++) {

            // Création de la date format AAMMJJ
            $dateJourYMD = formaterDate($i, 'Y-m-d');

            // Pour passer en français la date affichée
            $listeSemaine = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
            $listeMois = array(1=>"janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "decembre");
            $dateFrancaise = $listeSemaine[formaterDate($i, 'w')].' '.formaterDate($i, 'j').' '.$listeMois[formaterDate($i, 'n')];
            
            $declenchement = ($parsedJson[$dateJourYMD] == true) ? 'checked':'';

            // Affiche la checkbox de la date
            echo '<div class="form-check form-switch">';
            echo '  <input class="form-check-input" type="checkbox" role="switch" name="'.$dateJourYMD.'" id="'.$dateJourYMD.'" '.$declenchement.'>';
            echo '  <label class="form-check-label" for="'.$dateJourYMD.'">'.$dateFrancaise.'</label>';
            echo '</div>';
          }
          ?>
      <nav class="navbar fixed-bottom navbar-dark bg-dark">
        <div class="container-fluid  justify-content-center ">
          <button class="btn btn-outline-light" type="submit" id="btnEnregistrer">Enregistrer</button>
        </div>
      </nav>
        </form>
      </div>
    </div>
    <?php 
        if(!isset($_GET['admin'])) { // On affiche le bouton uniquement si nous ne sommes pas déjà en mode administrateur
      ?>
        <div class="text-center py-3">
          <form method="get" action="">
            <input name="admin" value="true" type="hidden">
            <button class="btn btn-sm btn-outline-danger" id="admin" type="submit">Mode Administrateur</button>
          </form>
        </div>
      <?php
        }
      ?>
  </body>
</html>
