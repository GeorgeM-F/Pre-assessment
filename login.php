<?php
session_start();     // Inizio nuova sessione
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}
$messaggio = "";     // Crea parti di HTML da visualizzare successivamente
$link = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
  $iva = isset($_POST['iva']) ? trim($_POST['iva']) : '';
  $fiscale = isset($_POST['fiscale']) ? trim($_POST['fiscale']) : '';
  $settore = isset($_POST['settore']) ? trim($_POST['settore']) : '';
  $data = isset($_POST['data']) ? trim($_POST['data']) : '';
  $sede = isset($_POST['sede']) ? trim($_POST['sede']) : '';
  $codice = isset($_POST['codice']) ? trim($_POST['codice']) : '';
  $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
  // QUERY SQL PER L'INSERIMENTO DEI DATI IN MODO SICURO: invece dei valori usiamo dei segnaposto (":")
  $stmt = $pdo->prepare("INSERT INTO aziende (ragione_sociale, partita_iva, codice_fiscale, settore, data_creazione, sede, codice_ateco, tipo) VALUES (:nome, :iva, :fiscale, :settore, :data, :sede, :codice, :tipo)");
  $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);     // qui si definiscono i segnaposto
  $stmt->bindParam(':iva', $iva, PDO::PARAM_STR);
  $stmt->bindParam(':fiscale', $fiscale, PDO::PARAM_STR);
  $stmt->bindParam(':settore', $settore, PDO::PARAM_STR);
  $stmt->bindParam(':data', $data, PDO::PARAM_STR);
  $stmt->bindParam(':sede', $sede, PDO::PARAM_STR);
  $stmt->bindParam(':codice', $codice, PDO::PARAM_STR);
  $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
  if ($stmt->execute()) {     // Definisce le parti di HTML da visualizzare a seconda dell'esito
    $_SESSION["tuonome"]=$_POST['nome'];     // Riempimento sessione
    $messaggio = "<p style='color: green;'>I dati della tua azienda sono stati inseriti con successo! Ora puoi iniziare il test:</p>
    <p style='color: green;'>(sei loggato come: <strong>".$_SESSION["tuonome"]."</strong>.)</p>";
    $link = "<a class='bot' href='questionnaire.php'>INIZIA IL TEST</a>";
  } else {
    $messaggio = "<p style='color: red;'>ERRORE: non è stato possibile salvare i dati dell'azienda.</p>";
  }
}

//RECUPERO VALORE DA USARE NELLA SESSIONE:
//$stmt = $pdo->prepare("SELECT tipo FROM merce WHERE id = :id");
//$stmt->execute(['id' => 5]);
//$valore = $stmt->fetch();

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
  <h1>INSERIMENTO DATI</h1>
  <p>Inserisci i dati della tua azienda:</p>

  <form action="" method="POST">
    <div>
      <label>Nome / ragione sociale:</label>
      <input type="text" name="nome" required>
    </div>
    <div>
      <label>Partita IVA:</label>
      <input type="text" name="iva" required>
    </div>
    <div>
      <label>Codice fiscale:</label>
      <input type="text" name="fiscale" required>
    </div>
    <div>
      <label>Settore:</label>
      <input type="text" name="settore" required>
    </div>
    <div>
      <label>Data creazione:</label>
      <input type="text" name="data" required>
    </div>
    <div>
      <label>Sede azienda:</label>
      <input type="text" name="sede" required>
    </div>
    <div>
      <label>Codice ATECO:</label>
      <input type="text" name="codice" required>
    </div>
    <div>
      <label>Tipo di impresa:</label>
      <select name="tipo" required>
        <option value="micro">Micro-impresa (meno di 10 impiegati)</option>
        <option value="mini">Piccola (tra 10 e 49 impiegati)</option>
        <option value="mid">Media (tra 50 e 249 impiegati)</option>
        <option value="maxi">Grande (almeno 250 impiegati)</option>
      </select>
    </div>
    <button type="submit">FINITO</button>
    <?php echo $messaggio; ?>
    <?php echo $link; ?>
  </form>

  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
<?php
echo "<p>(versione di prova)</p>";
?>
