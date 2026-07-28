<?php
// CARICAMENTO LIBRERIE PER IL REPORT
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
// DEFINIZIONE FUNZIONE DI SOMMATORIA
function sommatoria(array $array) {
  foreach ($array as $value) {
    if (!is_numeric($value)) {
      throw new TypeError("L'array contiene valori non numerici.");
    }
  }
  return array_sum($array);
}
// INIZIALIZZAZIONE
session_start();     // Continuazione sessione precedente
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}
// CONTROLLA SE SI E' EFFETTUATO L'ACCESSO DALLA LISTA DI PROVE PASSATE
if (isset($_GET['provaperta'])) {
  $danumaid = $pdo->query("SELECT id_prova FROM prove_preassessment WHERE id_azienda = ".$_SESSION["tuoid"]);     //Prove fatte dalla presente azienda
  $listaprove = $danumaid->fetchAll(PDO::FETCH_COLUMN);
  $_SESSION["qualeprova"] = $listaprove[$_GET['provaperta']-1];
}
// DETERMINA IL NUMERO DI RISPOSTE NO, IN PARTE, SI
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
    $stmt5->execute($indici);
    $coppie_valori = $stmt5->fetchALL();     //aggiungi PDO::FETCH_ASSOC ?
    $valori_combinati = [];
    foreach ($coppie_valori as $c) {
      try {$valori_combinati[] = ($c[1]/$c[0])/3*100;} catch (DivisionByZeroError | TypeError | Error $e) {$valori_combinati[] = "niente";};
    }
    try {$vals[$temind][$critind] = sommatoria($valori_combinati)/count($valori_combinati);} catch (DivisionByZeroError | TypeError | Error $e) {$vals[$temind][$critind] = "niente";};
  }
}
// DEFINIZIONE VALORI FINALI
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
try {$strategie = ($vals[0][0]+$vals[1][0]+$vals[2][0]+$vals[3][0]+$vals[4][0]+$vals[5][0]+$vals[6][0]+$vals[7][0]+$vals[8][0]+$vals[9][0])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$strategie = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$politiche = ($vals[0][1]+$vals[1][1]+$vals[2][1]+$vals[3][1]+$vals[4][1]+$vals[5][1]+$vals[6][1]+$vals[7][1]+$vals[8][1]+$vals[9][1])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$politiche = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$risorse = ($vals[0][2]+$vals[1][2]+$vals[2][2]+$vals[3][2]+$vals[4][2]+$vals[5][2]+$vals[6][2]+$vals[7][2]+$vals[8][2]+$vals[9][2])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$risorse = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$obiettivi = ($vals[0][3]+$vals[1][3]+$vals[2][3]+$vals[3][3]+$vals[4][3]+$vals[5][3]+$vals[6][3]+$vals[7][3]+$vals[8][3]+$vals[9][3])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$obiettivi = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$metriche = ($vals[0][4]+$vals[1][4]+$vals[2][4]+$vals[3][4]+$vals[4][4]+$vals[5][4]+$vals[6][4]+$vals[7][4]+$vals[8][4]+$vals[9][4])/10;} catch (DivisionByZeroError | TypeError | Error $e) {$metriche = "valori non disponibili";};     // Media di tutti i valori attinenti a tale criterio
try {$environmental = ($e1+$e2+$e3+$e4+$e5)/5;} catch (DivisionByZeroError | TypeError | Error $e) {$environmental = "valori non disponibili";};
try {$social = ($s1+$s2+$s3+$s4)/4;} catch (DivisionByZeroError | TypeError | Error $e) {$social = "valori non disponibili";};
try {$governance = ($g1)/1;} catch (DivisionByZeroError | TypeError | Error $e) {$governance = "valori non disponibili";};
try {$complessivo = ($environmental+$social+$governance)/3;} catch (DivisionByZeroError | TypeError | Error $e) {$complessivo = "valori non disponibili";};

// GENERAZIONE REPORT SCARICABILE
if (isset($_GET['azione']) && $_GET['azione'] === 'scarica') {
  // CARICAMENTO FOGLIO
  $read = IOFactory::createReader('Xlsx');
  $read->setIncludeCharts(true);
  $spread = $read->load('report_template_simplified.xlsx');
  // INSERIMENTO VALORI
  $sheet = $spread->getSheetByName('Foglio1');
  $sheet->setCellValue('B1', $complessivo);
  $sheet->setCellValue('B2', $environmental);
  $sheet->setCellValue('B3', $social);
  $sheet->setCellValue('B4', $governance);
  $sheet->setCellValue('B5', $e1);
  $sheet->setCellValue('B6', $e2);
  $sheet->setCellValue('B7', $e3);
  $sheet->setCellValue('B8', $e4);
  $sheet->setCellValue('B9', $e5);
  $sheet->setCellValue('B10', $s1);
  $sheet->setCellValue('B11', $s2);
  $sheet->setCellValue('B12', $s3);
  $sheet->setCellValue('B13', $s4);
  $sheet->setCellValue('B14', $g1);
  $sheet->setCellValue('B15', $strategie);
  $sheet->setCellValue('B16', $politiche);
  $sheet->setCellValue('B17', $risorse);
  $sheet->setCellValue('B18', $obiettivi);
  $sheet->setCellValue('B19', $metriche);
  $sheet->setCellValue('B20', $no/70*100);
  $sheet->setCellValue('B21', $inparte/70*100);
  $sheet->setCellValue('B22', $si/70*100);
  // (...)
  if (ob_get_level()) {ob_end_clean();}     // Pulisce il buffer di output
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');     // Imposta le intestazioni HTTP per il download
  header('Content-Disposition: attachment;filename="Ultimo_Report.xlsx"');
  header('Cache-Control: max-age=0');
  // INVIA IL RISULTATO AL BROWSER PER IL SALVATAGGIO
  $written = IOFactory::createWriter($spread, 'Xlsx');
  $written->setIncludeCharts(true);
  $written->save('php://output');
  exit;
}

//PREPARAZIONE GIUDIZI FINALI
$stmt6 = $pdo->query("SELECT tema, testo FROM suggerimenti");
$giu = $stmt6->fetchALL();
?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Pre-assessment - risultati</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <?php echo "<h1>Punteggio complessivo:   ".round($complessivo)."%</h1>" ?>
    </div>
    <div class="oriz">
      <div class="risez">
        <h1>Pilastri ESG</h1>
        <?php //echo "<h2>Environmental:   ".round($environmental)."%</h2>" ?>
        <?php //echo "<h2>Social:   ".round($social)."%</h2>" ?>
        <?php //echo "<h2>Governance:   ".round($governance)."%</h2>" ?>
        <canvas id="PilastriGrafico" width="400" height="200"></canvas>
      </div>
      <div class="risez">
        <h1>Grado di priorità per ESRS specifico</h1>
        <?php //echo "<h2>E1:   ".round($e1)."%</h2>" ?>
        <?php //echo "<h2>E2:   ".round($e2)."%</h2>" ?>
        <?php //echo "<h2>E3:   ".round($e3)."%</h2>" ?>
        <?php //echo "<h2>E4:   ".round($e4)."%</h2>" ?>
        <?php //echo "<h2>E5:   ".round($e5)."%</h2>" ?>
        <?php //echo "<h2>S1:   ".round($s1)."%</h2>" ?>
        <?php //echo "<h2>S2:   ".round($s2)."%</h2>" ?>
        <?php //echo "<h2>S3:   ".round($s3)."%</h2>" ?>
        <?php //echo "<h2>S4:   ".round($s4)."%</h2>" ?>
        <?php //echo "<h2>G1:   ".round($g1)."%</h2>" ?>
        <canvas id="MacroTemiGrafico" width="400" height="200"></canvas>
      </div>
    </div>
    <div class="oriz">
      <div class="risez">
        <h1>Prioritizzazione delle categorie</h1>
        <?php //echo "<h2>Strategie:   ".round($strategie)."%</h2>" ?>
        <?php //echo "<h2>Politiche:   ".round($politiche)."%</h2>" ?>
        <?php //echo "<h2>Risorse:   ".round($risorse)."%</h2>" ?>
        <?php //echo "<h2>Obiettivi:   ".round($obiettivi)."%</h2>" ?>
        <?php //echo "<h2>Metriche:   ".round($metriche)."%</h2>" ?>
        <canvas id="CategorieGrafico" width="400" height="200"></canvas>
      </div>
      <div class="risez">
        <h1>Distribuzione delle risposte al questionario</h1>
        <?php //echo "<h2>No:   ".round($no/70*100)."%</h2>" ?>
        <?php //echo "<h2>In parte:   ".round($inparte/70*100)."%</h2>" ?>
        <?php //echo "<h2>Sì:   ".round($si/70*100)."%</h2>" ?>
        <canvas id="RisposteGrafico" width="400" height="200"></canvas>
      </div>
    </div>
  </div>
  <div class="sez">
    <h1>GIUDIZI FINALI E SUGGERIMENTI</h1>
    <?php     // REPLICAZIONE GIUDIZI PER OGNI AREA TEMATICA
    $temars = [$e1, $e2, $e3, $e4, $e5, $s1, $s2, $s3, $s4, $g1];
    $n=0;     // Indice per la numerazione dei giudizi
    foreach ($temars as $i) {
      $n=$n+1;     // Aggiorna l'indice
      switch (true) {     // Scelta giudizio in base al punteggio
        case (0<=$i && $i<=20):
          $nplus = 0;
          $col = '192,64,64';
          break;
        case (20<$i && $i<=40):
          $nplus = 1;
          $col = '192,128,64';
          break;
        case (40<$i && $i<=60):
          $nplus = 2;
          $col = '192,192,64';
          break;
        case (60<$i && $i<=80):
          $nplus = 3;
          $col = '128,192,64';
          break;
        case (80<$i && $i<=100):
          $nplus = 4;
          $col = '64,192,64';
          break;
      }
      echo '
      <div class="risez" style="border-color: rgba('.$col.', 1); background-color: rgba('.$col.', 0.5)">
        <h2>'.$giu[$n*5-5][0].'</h2>
        <p>'.$giu[$n*5-5+$nplus][1].'</p>
      </div>';
    }
    ?>
  </div>
  <p>Puoi scaricare il report completo in formato .xlsx cliccando nel link sottostante:</p>
  <a class="bot" href="results.php?azione=scarica">SCARICA IL REPORT</a>
  <a class="bot" href="area.php">TORNA ALLA TUA AREA RISERVATA</a>
  <a class="bot" href="index.php">TORNA ALL'INIZIO</a>
</div>

<script>
const ctx1 = document.getElementById('PilastriGrafico').getContext('2d');
new Chart(ctx1, {
  type: 'bar', // Può essere 'line', 'pie', 'doughnut', 'radar', ecc.
  data: {
    labels: <?php echo json_encode(["ENVIRONMENTAL", "SOCIAL", "GOVERNANCE"]); ?>,
          datasets: [{
            label: '',
            data: <?php echo json_encode([$environmental, $social, $governance]); ?>,
          backgroundColor: ['rgba(64, 192, 64, 0.5)', 'rgba(255, 128, 128, 0.5)', 'rgba(0, 0, 128, 0.5)']
          }]
  },
  options: {
    responsive: true,
    color: 'rgb(0, 0, 0)',
    font: {
      size: 16
    },
    scales: {
      y: {
        beginAtZero: true,
        max: 100
      }
    }
  }
});
// Definizione grafico 2
const ctx2 = document.getElementById('MacroTemiGrafico').getContext('2d');
new Chart(ctx2, {
  type: 'bar', // Può essere 'line', 'pie', 'doughnut', 'radar', ecc.
  data: {
    labels: <?php echo json_encode(["E1", "E2", "E3", "E4", "E5", "S1", "S2", "S3", "S4", "G1"]); ?>,
    datasets: [{
      label: '',
      data: <?php echo json_encode([$e1, $e2, $e3, $e4, $e5, $s1, $s2, $s3, $s4, $g1]); ?>,
      backgroundColor: ['rgba(64, 192, 64, 0.5)', 'rgba(64, 192, 64, 0.5)', 'rgba(64, 192, 64, 0.5)', 'rgba(64, 192, 64, 0.5)', 'rgba(64, 192, 64, 0.5)', 'rgba(255, 128, 128, 0.5)', 'rgba(255, 128, 128, 0.5)', 'rgba(255, 128, 128, 0.5)', 'rgba(255, 128, 128, 0.5)', 'rgba(0, 0, 128, 0.5)']
    }]
  },
  options: {
    responsive: true,
    color: 'rgb(0, 0, 0)',
    font: {
      size: 16
    },
    scales: {
      y: {
        beginAtZero: true,
        max: 100
      }
    }
  }
});
// Definizione grafico 3
const ctx3 = document.getElementById('CategorieGrafico').getContext('2d');
new Chart(ctx3, {
  type: 'radar', // Può essere 'line', 'pie', 'doughnut', 'bar', ecc.
  data: {
    labels: <?php echo json_encode(["Strategie", "Politiche", "Risorse", "Obiettivi", "Metriche"]); ?>,
          datasets: [{
            label: '',
            data: <?php echo json_encode([$strategie, $politiche, $risorse, $obiettivi, $metriche]); ?>,
          backgroundColor: 'rgba(0, 128, 0, 0.5)'
          }]
  },
  options: {
    responsive: true,
    color: 'rgb(0, 0, 0)',
    font: {
      size: 16
    },
    scales: {
      r: {
        beginAtZero: true,
        max: 100
      }
    }
  }
});
// Definizione grafico 4
const ctx4 = document.getElementById('RisposteGrafico').getContext('2d');
new Chart(ctx4, {
  type: 'pie', // Può essere 'line', 'bar', 'doughnut', 'radar', ecc.
  data: {
    labels: <?php echo json_encode(["No", "In parte", "Sì"]); ?>,
          datasets: [{
            label: '',
            data: <?php echo json_encode([round($no/70*100), round($inparte/70*100), round($si/70*100)]); ?>,
          backgroundColor: ['rgba(255, 0, 0, 0.5)', 'rgba(128, 128, 0, 0.5)', 'rgba(0, 255, 0, 0.5)']
          }]
  },
  options: {
    responsive: true,
    color: 'rgb(0, 0, 0)',
    font: {
      size: 16
    }
  }
});
</script>

</body>
</html>
