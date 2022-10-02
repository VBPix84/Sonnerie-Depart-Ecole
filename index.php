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
function purgerDates($fichierTabJson, $tabDatesDepasees)

Suppression des dates dépassées et enregistrées dans le json
*/
function purgerDates($fichierTabJson, $tabDatesDepasees){

  $tabJsonEnArray = parseJson($fichierTabJson); // là on a les dates du json dans un tableau

  foreach ($tabDatesDepasees as $dateASuppr) { // Pour chaque date dans le tableau "dates à supprimer"
    unset($tabJsonEnArray[$dateASuppr]);       // On supprime l'entrée dans le tableau $tabJsonEnArray qui a pour key la date à supprimer
  }

  file_put_contents($fichierTabJson, json_encode($tabJsonEnArray));
  $tabDatesDepasees = array('');
}

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
function formaterDate($i, $format, $dateInitiale)

Sur la date désirée (soit actuelle, soit passé en paramètre $dateInitiale) applique le décalage $i, applique le $format et retourne la variable formatée 
*/
function formaterDate($i, $format, $dateInitiale){
  $dateInitiale = new DateTime($dateInitiale);
  $dateInitiale->modify('+ '.$i.' days');
  $dateFormatee = $dateInitiale->format($format);

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
Traitement du formulaire via $_POST

A la réception d'une demande d'enregistrement, un tableau est rempli avec les valeurs true ou false
pour le JSON (le fichier est COMPLETEMENT réécrit puis ré-enregistré)

  - $enregistrement => Stock le tableau en cours de réécriture
*/
?>

<div class="container col-12 col-sm-5 mt-3 content-align-center" style="margin-bottom : 75px;"> <!-- div qui contient le contenu -->

<?php

//
// Affichage Mode Admin

if(isset($_GET['admin']) == true) {
  $parsedJson = parseJson($nomDuFichier);
  $nbDatesAffichage = 60; // Nombre de date dans le fichier json = count($parsedJson);
  $nbDatesTotal = count($parsedJson);

  // dates dépassées
  $datesCles = array_keys($parsedJson);
  $tabDatesDepasees = array();
  $dateNow = new \DateTime();

    foreach ($datesCles as $date) {
      $dateAComparer = DateTime::createFromFormat('Y-m-d', $date);
      

      if($dateAComparer->format('Y-m-d') > $dateNow->format('Y-m-d')) {
        break;
      }
      array_push($tabDatesDepasees, $date);
    }


  // if(array_key_exists('btnPurgerDates', $_GET)) {
  //   purgerDates($nomDuFichier, $tabDatesDepasees);
  //}

  $nbDatesDepassees = count($tabDatesDepasees);
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
      <form method="GET" action="#">
        <button class="badge btn-outline-danger btn-sm text-bg-light" id="admin" type="submit">Mode administrateur actif</button>
      </form>
    </div>
  </nav>
  <div class="card mt-5 mb-2">
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
           Dernière date validée
            <span class="badge bg-primary"><?php echo formaterDate(0, "d/n/Y", array_key_last($parsedJson)); ?></span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div class="col-12">
              <label for="AffichageEntreeNormal" class="form-label">Affichage "Normal"</label>
              <div class="input-group col-6">
                <select class="form-select" name="AffichageEntreeNormal" id="AffichageEntreeNormal" disabled>
                  <option selected>#</option>
                  <option value="7">1 semaine</option>
                  <option value="14">2 semaines</option>
                  <option value="21">3 semaines</option>
                </select>
                <button class="btn btn-outline-secondary" type="button" id="" disabled>Enregistrer</button>
              </div>
            </div>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div class="col-12">
              <label for="AffichageEntreeAdmin" class="form-label">Affichage mode "Administrateur"</label>
              <div class="input-group col-6">
              <select class="form-select" name="AffichageEntreeAdmin" id="AffichageEntreeAdmin" disabled>
                  <option selected>#</option>
                  <option value="7">1 semaine</option>
                  <option value="14">2 semaines</option>
                  <option value="21">3 semaines</option>
                </select>
                <button class="btn btn-outline-secondary" type="button" id="btnRecAffModeAdmin" onclick="">Enregistrer</button>
              </div>
            </div>
          </li>

        </ul>

        <form method="GET" target=""?admin=true"">
          <p class="list-group-item d-flex justify-content-center align-items-center mt-3">
          <input name="admin" value="true" type="hidden"><input name="btnPurgerDates" value="true" type="hidden">
            <button class="btn <?php echo $stylebtnPurge; ?>" type="submit" <?php echo $btnDisabled; ?> disabled>Purger les dates dépassées &nbsp;<span class="badge text-bg-secondary"><?php echo $nbDatesDepassees; ?></span></button>
          </p>
        </form>

    </div>
  </div>
<?php
}

//
// Fin Mode Admin
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
              
              $dateJourYMD = formaterDate($i, 'Y-m-d', '');
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
            $dateJourYMD = formaterDate($i, 'Y-m-d', '');

            // Pour passer en français la date affichée
            $listeSemaine = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
            $listeMois = array(1=>"janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "decembre");
            $dateFrancaise = $listeSemaine[formaterDate($i, 'w', '')].' '.formaterDate($i, 'j', '').' '.$listeMois[formaterDate($i, 'n', '')];
            
            // enregistrement[$dateJourYMD] = (array_key_exists($dateJourYMD, $_POST)) ? true : false;
            if(array_key_exists($dateJourYMD, $parsedJson)) {
              $declenchement = ($parsedJson[$dateJourYMD] == true) ? 'checked':'';
            } else {
              $declenchement = '';
            }

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
