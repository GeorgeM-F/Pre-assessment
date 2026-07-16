<?php
session_start();     // Prova sessione precedente
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}
$messaggio = "";     // Crea parti di HTML da visualizzare successivamente
$link = "";     // >>
$domquer = $pdo->query("SELECT domanda FROM domande");     // Fa una query di tutte le domande presenti nella tabella...
$domarr = $domquer->fetchAll(PDO::FETCH_COLUMN);     // ...e le salva come array.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $pdo->exec("DELETE FROM risposte_preassessment_prova");     // Pulisce la tabella prima di aggiungere i nuovi valori
  $domvals = array_fill(0, 70, array_fill(0, 6, ""));     // Crea una tabella...
  foreach ($_POST as $nom => $val) {     // ...e la riempie con i valori ottenuti dal form
    $idom = $nom[0];     // primo carattere della chiave
    switch (substr($nom, 1)) {     // resto della chiave
      case "risp":
        $tdom = 1;
        break;
      case "desc":
        $tdom = 2;
        break;
      case "autoval":
        $tdom = 3;
        break;
      case "prior":
        $tdom = 4;
        break;
      case "not":
        $tdom = 5;
        break;
    }
    $domvals[$idom-1][0] = $idom;
    $domvals[$idom-1][$tdom] = $val;
  }
  foreach ($domvals as $riga) {
    $stmt = $pdo->prepare("INSERT INTO risposte_preassessment_prova (id_domanda, risposta, descrizione, autovalutazione, priorità, note) VALUES (:idom, :risp, :desc, :autoval, :prior, :not)");
    $idom = $riga[0];
    $risp = $riga[1];
    $desc = $riga[2];
    $autoval = $riga[3];
    $prior = $riga[4];
    $not = $riga[5];
    $stmt->bindValue(':idom', $idom, PDO::PARAM_INT);
    $stmt->bindValue(':risp', $risp, PDO::PARAM_STR);
    $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
    $stmt->bindValue(':autoval', $autoval, PDO::PARAM_INT);
    $stmt->bindValue(':prior', $prior, PDO::PARAM_INT);
    $stmt->bindValue(':not', $not, PDO::PARAM_STR);
    $stmt->execute();
    $stmt = 1;
  }

  if ($stmt == 1) {     // Definisce le parti di HTML da visualizzare a seconda dell'esito
    $messaggio = "<p style='color: green;'>Le tue risposte sono state inserite con successo! Ora puoi visualizzare il report:</p>";
    $link = "<a class='bot' href='results.php'>GUARDA I RISULTATI</a>";
  } else {
    $messaggio = "<p style='color: red;'>ERRORE: non è stato possibile salvare le tue risposte.</p>";
  }
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Pre-assessment - questionario</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sez">
  <h1>QUESTIONARIO</h1>
  <?php
    echo "<p class='log'>Azienda: <strong>".$_SESSION["tuonome"]."</strong></p>";
  ?>
  <p>Rispondi alle seguenti domande:</p>

  <form action="" method="POST">

  <?php     // REPLICAZIONE DOMANDA PER OGNI ENTRY DELL'ARRAY
  $n=0;     // Indice per la numerazione delle domande
  foreach ($domarr as $i) {
    $n=$n+1;
    echo '
    <div class="sez">
      <p>Domanda '.$n.' di 70:</p>
      <h2>'.$i.'</h2>
      <div>
        <label><br>Risposta:<br></label>
        <input type="hidden" name="i" value='.$n.'>     //===IMPORTANTE!!!===//
        <input type="radio" name="'.$n.'risp" value="no"><label class="d"><strong>no</strong></label>
        <input type="radio" name="'.$n.'risp" value="in parte"><label class="d"><strong>in parte</strong></label>
        <input type="radio" name="'.$n.'risp" value="sì"><label class="d"><strong>sì</strong></label>
      </div>
      <div>
        <label><br>Descrizione delle attività attualmente implementate dall\'Organizzazione:<br></label>
        <textarea name="'.$n.'desc" rows="5" cols="30"></textarea>
      </div>
      <div>
        <label><br>Auto-valutazione delle attività attualmente implementate dall\'Organizzazione:<br>(1: minima importanza; 2: ridotta importanza; 3: media importanza; 4: elevata importanza; 5: massima importanza)<br></label>
        <input type="range" name="'.$n.'autoval" min="1" max="5">
      </div>
      <div>
        <label><br>Grado di priorità nell\'implementazione o potenziamento delle pratiche aziendali:<br></label>
        <input type="radio" name="'.$n.'prior" value=1><label class="d"><strong>bassa</strong></label>
        <input type="radio" name="'.$n.'prior" value=2><label class="d"><strong>media</strong></label>
        <input type="radio" name="'.$n.'prior" value=3><label class="d"><strong>alta</strong></label>
      </div>
      <div>
        <label><br>Note (facoltativo):<br></label>
        <input type="text" name="'.$n.'not">
      </div>
    </div>
    ';}
    ?>

    <button type="submit">FINITO</button>
    <?php echo $messaggio; ?>
    <?php echo $link; ?>
  </form>

  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
