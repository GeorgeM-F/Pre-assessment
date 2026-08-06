<?php
session_start();
$_SESSION['domatt'] = $_SESSION['domatt']+1;     // indicatore domanda attuale

try {
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}

$messaggio = "";
$link = "";

$domquer = $pdo->query("SELECT domanda FROM domande");     // Fa una query di tutte le domande presenti nella tabella...
$domarr = $domquer->fetchAll(PDO::FETCH_COLUMN);     // ...e le salva come array.
$infquer = $pdo->query("SELECT info FROM domande");     // Fa una query di tutti i suggerimenti presenti nella tabella...
$infarr = $infquer->fetchAll(PDO::FETCH_COLUMN);     // ...e li salva come array.


if ($_SESSION['domatt'] == 1) {
  // Aggiunta nuova prova e selezione id prova:
  $aggpro = $pdo->prepare("INSERT INTO prove_preassessment (id_azienda, data_prova) VALUES (:iaz, :dp)");     // Non c'è bisogno di inserire l'id prova (è automatico)
  $aggpro->execute([':iaz' => $_SESSION["tuoid"], ':dp' => date('Y-m-d H:i:s')]);
  //$numpro = $pdo->prepare("SELECT id_prova FROM prove_preassessment WHERE id_azienda = :tuoid ORDER BY id_prova DESC LIMIT 1");     // Prende l'id dell'ultima prova (quella appena aggiunta)
  //$numpro->execute([':tuoid' => $_SESSION["tuoid"]]);
  //$_SESSION['numpro'] = $numpro;
  $_SESSION['numpro'] = $pdo->lastInsertId();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Preparazione aggiunta nuove risposte:
  $stmt = $pdo->prepare("INSERT INTO risposte_preassessment (id_azienda, id_prova, id_domanda, risposta, descrizione, autovalutazione, priorità, note) VALUES (:iaz, :ipro, :idom, :risp, :desc, :autoval, :prior, :not)");
  // (nota: iaz e ipro sono già stati definiti prima)
  $iaz = $_SESSION["tuoid"];
  $ipro = $_SESSION['numpro'];
  $idom = $_POST['idom'];
  $risp = $_POST['risp'];
  $desc = $_POST['desc'];
  $autoval = $_POST['autoval'];
  $prior = $_POST['prior'];
  $not = $_POST['not'];
  $stmt->bindValue(':iaz', $iaz, PDO::PARAM_INT);
  $stmt->bindValue(':ipro', $ipro, PDO::PARAM_INT);
  $stmt->bindValue(':idom', $idom, PDO::PARAM_INT);
  $stmt->bindValue(':risp', $risp, PDO::PARAM_STR);
  $stmt->bindValue(':desc', $desc, PDO::PARAM_STR);
  $stmt->bindValue(':autoval', $autoval, PDO::PARAM_INT);
  $stmt->bindValue(':prior', $prior, PDO::PARAM_INT);
  $stmt->bindValue(':not', $not, PDO::PARAM_STR);
  $stmt->execute();
  // $stmt = $_SESSION['domatt'];
  if ($_SESSION['domatt'] > 70) {
    $messaggio = "<p class='mess' style='color: green;'>Hai completato il questionario! Ora puoi visualizzare il report:</p>";
    $_SESSION["qualeprova"] = $ipro;
    $link = "<a class='bot' href='results.php'>GUARDA I RISULTATI</a>";
    $canc = 'none';     // parametro per la cancellazione del form una volta concluso
  } else {
    $messaggio = "";
    //$messaggio = "<p class='mess' style='color: red;'>ERRORE: non è stato possibile salvare le tue risposte.</p>";
  }
}

if ($_SESSION['domatt'] < 33) {
  $sezcol = 'rgba(64, 192, 64, 0.5)';
  $sezcolb = 'rgba(64, 192, 64, 1)';
  $seztit = 'Sezione: ENVIRONMENTAL';
}
if ($_SESSION['domatt'] > 32 && $_SESSION['domatt'] < 64) {
  $sezcol = 'rgba(255, 192, 128, 0.5)';
  $sezcolb = 'rgba(255, 192, 128, 1)';
  $seztit = 'Sezione: SOCIAL';
}
if ($_SESSION['domatt'] > 63 && $_SESSION['domatt'] < 71) {
  $sezcol = 'rgba(0, 64, 128, 0.5)';
  $sezcolb = 'rgba(0, 64, 128, 1)';
  $seztit = 'Sezione: GOVERNANCE';
}

?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Pre-assessment - Questionario</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="sez">
  <h1>QUESTIONARIO</h1>
  <?php echo "<p class='log'>Azienda: <strong>".$_SESSION["tuonome"]."</strong></p>";?>
  <?php echo $messaggio; ?>
  <?php echo $link; ?>
  <canvas id="BarraCompletamento" width="800" height="75"></canvas>
  <?php echo "<h1>".$seztit."</h1>"; ?>
  <form action="" method="POST" style="background-color: <?php echo $sezcol; ?>; border-color: <?php echo $sezcolb; ?>; display: <?php echo $canc; ?>;">
      <p>Domanda <?php echo $_SESSION['domatt'] ?> di 70:</p>
      <h2><?php echo $domarr[$_SESSION['domatt']-1] ?></h2>
      <div class="vis">SUGGERIMENTI?</div>
      <p class="info"><?php echo $infarr[$_SESSION['domatt']-1] ?></p>
      <input type="hidden" name="idom" value="<?php echo $_SESSION['domatt']; ?>">
      <div>
        <label><br>Risposta:<br></label>
        <input type="radio" name="risp" value="no"><label class="d"><strong>no</strong></label>
        <input type="radio" name="risp" value="in parte"><label class="d"><strong>in parte</strong></label>
        <input type="radio" name="risp" value="sì"><label class="d"><strong>sì</strong></label>
      </div>
      <div>
        <label><br>Descrizione delle attività attualmente implementate dall\'Organizzazione:<br></label>
        <textarea name="desc" rows="5" cols="30"></textarea>
      </div>
      <div>
        <label><br>Auto-valutazione delle attività attualmente implementate dall\'Organizzazione:<br>(1: minima importanza; 2: ridotta importanza; 3: media importanza; 4: elevata importanza; 5: massima importanza)<br></label>
        <input type="range" name="autoval" min="1" max="5">
      </div>
      <div>
        <label><br>Grado di priorità nell\'implementazione o potenziamento delle pratiche aziendali:<br></label>
        <input type="radio" name="prior" value=1><label class="d"><strong>bassa</strong></label>
        <input type="radio" name="prior" value=2><label class="d"><strong>media</strong></label>
        <input type="radio" name="prior" value=3><label class="d"><strong>alta</strong></label>
      </div>
      <div>
        <label><br>Note (facoltativo):<br></label>
        <input type="text" name="not">
      </div>

    <button type="submit">Prossima domanda</button>
  </form>

</div>

<script>
Chart.defaults.font.size = 18;     // imposta le dimensioni dei caratteri di tutti i grafici
Chart.defaults.color = 'rgba(0, 0, 0, 1)';     // imposta il colore dei caratteri di tutti i grafici
const ctx = document.getElementById('BarraCompletamento').getContext('2d');
new Chart(ctx, {
  type: 'bar',     // tipo di grafico: a barre
  data: {
    labels: <?php echo json_encode([""]); ?>,
          datasets: [{
            label: 'Completamento questionario',
            data: <?php echo json_encode([$_SESSION['domatt']]); ?>,
          backgroundColor: <?php echo json_encode($sezcol); ?>
          }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    scales: {
      x: {
        beginAtZero: true,
        max: 70
      }
    },
    plugins: {
      legend: {
        display: true
      }
    }
  }
})
</script>

</body>
</html>
