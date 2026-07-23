<?php
// Definizione sommatoria
function sommatoria(array $array) {
  foreach ($array as $value) {
    if (!is_numeric($value)) {
      throw new TypeError("L'array contiene valori non numerici.");
    }
  }
  return array_sum($array);
}

session_start();     // Continuazione sessione precedente
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$stmt = $pdo->query("SELECT risposta FROM risposte_preassessment WHERE id_azienda = ".$_SESSION["tuoid"]." AND id_prova = ".$_SESSION["qualeprova"]." AND risposta = 'no'");     //Array di risposte "no"
$no = count($stmt->fetchALL());     // Numero di elementi nell'array
$stmt2 = $pdo->query("SELECT risposta FROM risposte_preassessment WHERE id_azienda = ".$_SESSION["tuoid"]." AND id_prova = ".$_SESSION["qualeprova"]." AND risposta = 'in parte'");     //Array di risposte "in parte"
$inparte = count($stmt2->fetchALL());     // Numero di elementi nell'array
$stmt3 = $pdo->query("SELECT risposta FROM risposte_preassessment WHERE id_azienda = ".$_SESSION["tuoid"]." AND id_prova = ".$_SESSION["qualeprova"]." AND risposta = 'sì'");     //Array di risposte "sì"
$si = count($stmt3->fetchALL());     // Numero di elementi nell'array

// TABELLA DEI VALORI INTERMEDI:
$vals = array_fill(0, 10, ["criterio_strategie", "criterio_politiche", "criterio_risorse", "criterio_obiettivi", "criterio_metriche"]);
foreach ($vals as $temind => $tem) {
  foreach ($tem as $critind => $crit) {
// DALLE DOMANDE SELEZIONA GLI INDICI DI QUELLE CON MACRO-AREA = $tem E $crit = 1
    $sqli4 = "SELECT id_domanda FROM domande WHERE macro_tematica = ".$temind." AND ".$crit." = 1";     //query salvata prima come stringa
    $stmt4 = $pdo->query($sqli4);
    $indici = $stmt4->fetchALL(PDO::FETCH_COLUMN, 0);
    $placehold = implode(',', array_fill(0, count($indici), '?'));
// DALLE RISPOSTE SELEZIONA GLI AUTOVAL E PRIOR CON QUELL'INDICE
    $sqli5 = "SELECT autovalutazione, priorità FROM risposte_preassessment WHERE id_azienda = ".$_SESSION["tuoid"]." AND id_prova = ".$_SESSION["qualeprova"]." AND id_domanda IN ($placehold)";
    $stmt5 = $pdo->prepare($sqli5);
    //$stmt5->bindValue(':tuoid', $_SESSION["tuoid"], PDO::PARAM_INT);
    //$stmt5->bindValue(':idpro', $_SESSION["qualeprova"], PDO::PARAM_INT);
    $stmt5->execute($indici);
    $coppie_valori = $stmt5->fetchALL();     //aggiungi PDO::FETCH_ASSOC ?
    $valori_combinati = [];
    foreach ($coppie_valori as $c) {
      try {$valori_combinati[] = ($c[1]/$c[0])/3*100;} catch (DivisionByZeroError | TypeError | Error $e) {$valori_combinati[] = "niente";};
    }
    try {$vals[$temind][$critind] = sommatoria($valori_combinati)/count($valori_combinati);} catch (DivisionByZeroError | TypeError | Error $e) {$vals[$temind][$critind] = "niente";};
  }
}

try {$e1 = sommatoria($vals[0])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$e1 = "valori non disponibili";};
try {$e2 = sommatoria($vals[1])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$e2 = "valori non disponibili";};
try {$e3 = sommatoria($vals[2])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$e3 = "valori non disponibili";};
try {$e4 = sommatoria($vals[3])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$e4 = "valori non disponibili";};
try {$e5 = sommatoria($vals[4])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$e5 = "valori non disponibili";};
try {$s1 = sommatoria($vals[5])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$s1 = "valori non disponibili";};
try {$s2 = sommatoria($vals[6])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$s2 = "valori non disponibili";};
try {$s3 = sommatoria($vals[7])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$s3 = "valori non disponibili";};
try {$s4 = sommatoria($vals[8])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$s4 = "valori non disponibili";};
try {$g1 = sommatoria($vals[9])/5;} catch (DivisionByZeroError | TypeError | Error $e) {$g1 = "valori non disponibili";};
try {$strategie = ($vals[0][0]+$vals[0][1]+$vals[0][2]+$vals[0][3]+$vals[0][4]+$vals[0][5]+$vals[0][6]+$vals[0][7]+$vals[0][8]+$vals[0][9])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$strategie = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$politiche = ($vals[1][0]+$vals[1][1]+$vals[1][2]+$vals[1][3]+$vals[1][4]+$vals[1][5]+$vals[1][6]+$vals[1][7]+$vals[1][8]+$vals[1][9])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$politiche = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$risorse = ($vals[2][0]+$vals[2][1]+$vals[2][2]+$vals[2][3]+$vals[2][4]+$vals[2][5]+$vals[2][6]+$vals[2][7]+$vals[2][8]+$vals[2][9])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$risorse = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$obiettivi = ($vals[3][0]+$vals[3][1]+$vals[3][2]+$vals[3][3]+$vals[3][4]+$vals[3][5]+$vals[3][6]+$vals[3][7]+$vals[3][8]+$vals[3][9])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$obiettivi = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$metriche =  ($vals[4][0]+$vals[4][1]+$vals[4][2]+$vals[4][3]+$vals[4][4]+$vals[4][5]+$vals[4][6]+$vals[4][7]+$vals[4][8]+$vals[4][9])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$metriche = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$environmental = ($e1+$e2+$e3+$e4+$e5)/5;} catch (DivisionByZeroError | TypeError | Error $e) {$environmental = "valori non disponibili";};
try {$social = ($s1+$s2+$s3+$s4)/4;} catch (DivisionByZeroError | TypeError | Error $e) {$social = "valori non disponibili";};
try {$governance = ($g1)/1;} catch (DivisionByZeroError | TypeError | Error $e) {$governance = "valori non disponibili";};
try {$complessivo = ($environmental+$social+$governance)/3;} catch (DivisionByZeroError | TypeError | Error $e) {$complessivo = "valori non disponibili";};
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
