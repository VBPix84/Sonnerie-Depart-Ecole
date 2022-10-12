<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <title>Sonnerie Départ Ecole V1</title>
  </head>
  <body>
<?php

require 'fonctions.php';

/* 
Initialisation des variables :

  - $nomDuFichier => Nom du fichier calendrier
  - $parsedJson => Le fichier json décodé par la fonction est intégré dans la variable
  - $nbDatesAffichage => Nombre de jour à afficher en "mode normal"
*/
$nomDuFichier = "calDepartEcoleV2.json";
$parsedJson = parseJson($nomDuFichier);
$nbDatesAffichage = 14;
?>

<div class="container col-12 col-sm-5 mt-3 content-align-center" style="margin-bottom : 75px;"> <!-- div qui contient le contenu -->

<?php

//
// Affichage Mode Admin
//

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
    
    if($dateAComparer->format('Y-m-d') >= $dateNow->format('Y-m-d')) {
      break;
    }
    array_push($tabDatesDepasees, $date);

  }

  if(array_key_exists('btnSubmitPurgerDates', $_GET)) {
    $tabDatesDepasees = purgerDates($nomDuFichier, $tabDatesDepasees); // On purge et on vide le tableau
  }

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
  }
?>
  <nav class="navbar fixed-top navbar-dark bg-danger">
    <div class="container-fluid justify-content-center">
      <a class="badge text-bg-light" href="?#">Mode administrateur actif</a>
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
              <button class="btn btn-outline-secondary" type="button" id="btnRecAffModeAdmin" onclick="" disabled>Enregistrer</button>
            </div>
          </div>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div class="col-12">
            <label for="heureSonnerie" class="form-label">Heure de la sonnerie</label>
            <div class="input-group col-6">
              <input type="time" class="form-control" id="heureSonnerie" placeholder="7:15" disabled>
              <button class="btn btn-outline-secondary" type="button" id="btnRecAffModeAdmin" onclick="" disabled>Enregistrer</button>
            </div>
          </div>
        </li>
      </ul>
      <p class="list-group-item d-flex justify-content-center align-items-center mt-3">
        <a class="btn <?php echo $stylebtnPurge; ?>" href="?admin=true&btnSubmitPurgerDates=true">Purger les dates dépassées &nbsp;<span class="badge text-bg-secondary"><?php echo $nbDatesDepassees; ?></span></a>
      </p>
    </div>
  </div>
<?php
}

//
// Fin Mode Admin
//
?>

  <div class="card">
    <div class="card-header text-center">
      <b>Départ Ecole</b>
    </div>
    <div class="card-body">
    <?php

      /*
      Traitement du formulaire via $_POST

      A la réception d'une demande d'enregistrement, un tableau ($enregistrement) est rempli avec les valeurs true ou false des dates affichées (= qui existent dans $_POST)
      pour le JSON (le fichier est COMPLETEMENT réécrit puis ré-enregistré).

      Boucle sur chaque entrée du formulaire:
        1. $_POST pour la date en cours existe : Elle est donc à true, on push $enregistrement[$dateJourYMD] = true
        2. $_POST pour la date en cours n'existe pas, on push $enregistrement[$dateJourYMD] = false
        
      On merge les 2 tableaux : L'ancien + Le nouveau pour ne mettre à jours QUE les entrées du formulaire, on ne
      touche pas aux autres

      Puis on intègre le tableau merge dans le fichier avec file_put_contents() et on affiche un message d'enregistrement

        - $enregistrement => Stock le tableau en cours de réécriture
      */
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $enregistrement = array();

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
      <?php } // Fin traitement du "POST" ?> 
        <form method="post" action="#">
      <?php

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
        $parsedJson = parseJson($nomDuFichier);

        for($i = 1; $i <= $nbDatesAffichage; $i++) {

          // Création de la date format AAMMJJ
          $dateJourYMD = formaterDate($i, 'Y-m-d', '');

          // Création de la date format français (pour affichage uniquement)
          $listeSemaine = array("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
          $listeMois = array(1=>"janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "decembre");
          $dateFrancaise = $listeSemaine[formaterDate($i, 'w', '')].' '.formaterDate($i, 'j', '').' '.$listeMois[formaterDate($i, 'n', '')];
          
          if(array_key_exists($dateJourYMD, $parsedJson)) {
            $declenchement = ($parsedJson[$dateJourYMD] == true) ? 'checked':''; // Traitement si la date existe dans le json
          } else {
            $declenchement = ''; // Traitement si elle n'existe pas (on la met à False par défaut)
          }
        ?>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="<?php echo $dateJourYMD; ?>" id="<?php echo $dateJourYMD; ?>" <?php echo $declenchement; ?>>
            <label class="form-check-label" for="<?php echo $dateJourYMD; ?>"><?php echo $dateFrancaise; ?></label>
          </div>
        <?php } // Fin Boucle For ?>
        </div>
        <div class="d-flex justify-content-center my-2">
            <button class="btn btn-outline-primary" type="submit" id="btnEnregistrer">Enregistrer</button>
          </div>
      </form>
    </div>
    <?php if(!isset($_GET['admin'])) { // Affiche le bouton si mode administrateur non activé ?>
    <div class="text-center py-3">
      <a class="badge text-bg-light fw-light" href="?admin=true">Mode administrateur</a>
    </div>
    <?php } ?>
  </body>
</html>
