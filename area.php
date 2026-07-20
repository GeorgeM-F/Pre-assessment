<?php
session_start();     // Prova sessione precedente
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}
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
      echo "<p>Ragione sociale: <strong>".$_SESSION["tuonome"]."</strong></p></p>";
      echo "<p>Partita IVA: <strong>".$_SESSION["tuaiva"]."</strong></p></p>";
      echo "<p>Codice fiscale: <strong>".$_SESSION["tuocodice"]."</strong></p></p>";
      echo "<p>Settore: <strong>".$_SESSION["tuosettore"]."</strong></p></p>";
      echo "<p>Data creazione: <strong>".$_SESSION["tuacreazione"]."</strong></p></p>";
      echo "<p>Sede: <strong>".$_SESSION["tuasede"]."</strong></p></p>";
      echo "<p>Codice ATECO: <strong>".$_SESSION["tuoateco"]."</strong></p></p>";
      echo "<p>Tipo: <strong>".$_SESSION["tuotipo"]."</strong></p></p>";
      echo "<p>Indirizzo e-mail: <strong>".$_SESSION["tuaemail"]."</strong></p></p>";
    ?>
  </div>
  <div class="sez">
    <h2>Questionari disponibili:</h2>
    <a class="bot" href="questionnaire.php">PRE-ASSESSMENT</a>
  </div>

  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
