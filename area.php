<?php
session_start();     // Prova sessione precedente
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$linkquer = $pdo->query("SELECT id_prova FROM prove_preassessment");     // Fa una query di tutte le prove presenti nella tabella...
$linkarr = $linkquer->fetchAll(PDO::FETCH_COLUMN);     // ...e le salva come array.
$linkarr = [0,1,2,3,4,5,6,7,8,9];     //Cancella questa riga una volta fatto
?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Pre-assessment - inserimento dati</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sez">
  <h1>AREA RISERVATA</h1>
  <?php
    echo "<p class='log'>Azienda: <strong>".$_SESSION["tuonome"]."</strong></p>";
  ?>
  <div class="sez">
    <h2>Dati azienda:</h2>
    <hr>
    <?php
      echo "<p>Ragione sociale: <strong>".$_SESSION["tuonome"]."</strong></p>";
      echo "<p>Partita IVA: <strong>".$_SESSION["tuaiva"]."</strong></p>";
      echo "<p>Codice fiscale: <strong>".$_SESSION["tuocodice"]."</strong></p>";
      echo "<p>Settore: <strong>".$_SESSION["tuosettore"]."</strong></p>";
      echo "<p>Data creazione: <strong>".$_SESSION["tuacreazione"]."</strong></p>";
      echo "<p>Sede: <strong>".$_SESSION["tuasede"]."</strong></p>";
      echo "<p>Codice ATECO: <strong>".$_SESSION["tuoateco"]."</strong></p>";
      echo "<p>Tipo: <strong>".$_SESSION["tuotipo"]."</strong></p>";
      echo "<p>Indirizzo e-mail: <strong>".$_SESSION["tuaemail"]."</strong></p>";
    ?>
  </div>
  <div class="sez">
    <h2>Questionari disponibili:</h2>
    <div class="sez">
      <h2>Pre-assessment</h2>
      <a class="bot" href="questionnaire.php">Nuova prova</a>
      <p>Prove precedentemente effettuate:</p>
      <?php     // REPLICAZIONE LINK PER OGNI ENTRY DELL'ARRAY
      $n=0;     // Indice per la numerazione dei link
      foreach ($linkarr as $i) {
        $n=$n+1;     // Aggiorna l'indice
        echo '<a class="bot" href="questionnaire.php">Prova n° '.$n.'</a>';
      }
      ?>
    </div>
  </div>

  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
