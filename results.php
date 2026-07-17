<?php
session_start();     // Prova sessione precedente
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$stmt = $pdo->query("SELECT risposta FROM risposte_preassessment_prova WHERE risposta = 'no'");     //Array di risposte "no"
$no = count($stmt->fetchALL());     // Numero di elementi nell'array
$stmt = $pdo->query("SELECT risposta FROM risposte_preassessment_prova WHERE risposta = 'in parte'");     //Array di risposte "in parte"
$inparte = count($stmt->fetchALL());     // Numero di elementi nell'array
$stmt = $pdo->query("SELECT risposta FROM risposte_preassessment_prova WHERE risposta = 'sì'");     //Array di risposte "sì"
$si = count($stmt->fetchALL());     // Numero di elementi nell'array

// TABELLA DEI VALORI INTERMEDI:
$vals = array_fill(0, 10, ["criterio_strategie", "criterio_politiche", "criterio_risorse", "criterio_obiettivi", "criterio_metriche"]);

foreach ($vals as $tem) {
  foreach ($vals as $crit) {

// DALLE DOMANDE SELEZIONA GLI INDICI DI QUELLE CON MACRO-AREA = $tem E $crit = 1
    $sqli = "SELECT id_domanda FROM domande WHERE risposta = ".$tem." AND ".$crit." = 1";     //query salvata prima come stringa
    $stmt = $pdo->query($sqli);
    $indici = $stmt->fetchALL();
// DALLE RISPOSTE SELEZIONA GLI AUTOVAL E PRIOR CON QUELL'INDICE
    $sqli = "SELECT autovalutazione, priorità FROM risposte_preassessment_prova WHERE id_domanda IN ".$indici;
    $stmt = $pdo->query($sqli);
    $coppie_valori = $stmt->fetchALL();
// Ogni valore := media dei valori (prior/autoval)/3*100 delle domande attinenti a tale macro-tema e a tale criterio
    //$vals[$tem][$crit] = (espressione...)
  }
}

$e1 = array_sum($vals[0])/5;
$e2 = array_sum($vals[1])/5;
$e3 = array_sum($vals[2])/5;
$e4 = array_sum($vals[3])/5;
$e5 = array_sum($vals[4])/5;
$s1 = array_sum($vals[5])/5;
$s2 = array_sum($vals[6])/5;
$s3 = array_sum($vals[7])/5;
$s4 = array_sum($vals[8])/5;
$g1 = array_sum($vals[9])/5;
$strategie = ($vals[0][0]+$vals[0][1]+$vals[0][2]+$vals[0][3]+$vals[0][4]+$vals[0][5]+$vals[0][6]+$vals[0][7]+$vals[0][8]+$vals[0][9])/10;     // Media di tutti i valori attinenti a tale criterio
$politiche = 0;     // Media di tutti i valori attinenti a tale criterio
$risorse = 0;     // Media di tutti i valori attinenti a tale criterio
$obiettivi = 0;     // Media di tutti i valori attinenti a tale criterio
$metriche = 0;     // Media di tutti i valori attinenti a tale criterio
$environmental = ($e1+$e2+$e3+$e4+$e5)/5;
$social = ($s1+$s2+$s3+$s4)/4;
$governance = ($g1)/1;
$complessivo = 0;     // Media di (...)
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Pre-assessment - risultati</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sez">
  <div class="sez">
    <h1>RISULTATI</h1>
    <p>Grazie per aver compilato il questionario.
    Ecco i risultati ottenuti per ogni ambito ESG:</p>
    <?php
      echo "<p class='log'>Azienda: <strong>".$_SESSION["tuonome"]."</strong></p>";
    ?>
    <div class="risez">
      <?php echo "<h1>Punteggio complessivo: ".$complessivo."%</h1>" ?>
    </div>
    <div class="oriz">
      <div class="risez">
        <h1>Pilastri ESG</h1>
        <?php echo "<h2>Environmental: ".$environmental."%</h2>" ?>
        <?php echo "<h2>Social: ".$social."%</h2>" ?>
        <?php echo "<h2>Governance: ".$governance."%</h2>" ?>
      </div>
      <div class="risez">
        <h1>Grado di priorità per ESRS specifico</h1>
        <?php echo "<h2>E1: ".$e1."%</h2>" ?>
        <?php echo "<h2>E2: ".$e2."%</h2>" ?>
        <?php echo "<h2>E3: ".$e3."%</h2>" ?>
        <?php echo "<h2>E4: ".$e4."%</h2>" ?>
        <?php echo "<h2>E5: ".$e5."%</h2>" ?>
        <?php echo "<h2>S1: ".$s1."%</h2>" ?>
        <?php echo "<h2>S2: ".$s2."%</h2>" ?>
        <?php echo "<h2>S3: ".$s3."%</h2>" ?>
        <?php echo "<h2>S4: ".$s4."%</h2>" ?>
        <?php echo "<h2>G1: ".$g1."%</h2>" ?>
      </div>
    </div>
    <div class="oriz">
      <div class="risez">
        <h1>Prioritizzazione delle categorie</h1>
        <?php echo "<h2>Strategie: ".$strategie."%</h2>" ?>
        <?php echo "<h2>Politiche: ".$politiche."%</h2>" ?>
        <?php echo "<h2>Risorse: ".$risorse."%</h2>" ?>
        <?php echo "<h2>Obiettivi: ".$obiettivi."%</h2>" ?>
        <?php echo "<h2>Metriche: ".$metriche."%</h2>" ?>
      </div>
      <div class="risez">
        <h1>Distribuzione delle risposte al questionario</h1>
        <?php echo "<h2>No: ".round($no/70*100)."%</h2>" ?>
        <?php echo "<h2>In parte: ".round($inparte/70*100)."%</h2>" ?>
        <?php echo "<h2>Sì: ".round($si/70*100)."%</h2>" ?>
      </div>
    </div>
  </div>
  <div class="sez">
    <h1>SUGGERIMENTI</h1>
    <p>Ecco una serie di suggerimenti utili per migliorare la valutazione ESG della tua azienda:</p>
    <p>(...)</p>
  </div>
  <p>Puoi scaricare il report completo in formato .pdf cliccando nel link sottostante:</p>
  <a class="bot" href="report.php">SCARICA IL REPORT</a>
  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
