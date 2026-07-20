<?php
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
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';

  // CONTROLLO EVENTUALI DOPPIONI
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM aziende WHERE indirizzo_email = :email OR password = :password");
  $stmt->execute([':email' => $email, ':password' => $password]);
  if ($stmt->fetchColumn() > 0) {
    $messaggio = "<p style='color: red;'>Impossibile procedere: esistono già aziende con tali credenziali. Scegli un altro indirizzo e-mail o password.";
  } else {

    // QUERY SQL PER L'INSERIMENTO DEI DATI IN MODO SICURO: invece dei valori usiamo dei segnaposto (":")
    $stmt = $pdo->prepare("INSERT INTO aziende (ragione_sociale, partita_iva, codice_fiscale, settore, data_creazione, sede, codice_ateco, tipo, indirizzo_email, password) VALUES (:nome, :iva, :fiscale, :settore, :data, :sede, :codice, :tipo, :email, :password)");
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);     // qui si definiscono i segnaposto
    $stmt->bindParam(':iva', $iva, PDO::PARAM_STR);
    $stmt->bindParam(':fiscale', $fiscale, PDO::PARAM_STR);
    $stmt->bindParam(':settore', $settore, PDO::PARAM_STR);
    $stmt->bindParam(':data', $data, PDO::PARAM_STR);
    $stmt->bindParam(':sede', $sede, PDO::PARAM_STR);
    $stmt->bindParam(':codice', $codice, PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);


    if ($stmt->execute()) {     // Definisce le parti di HTML da visualizzare a seconda dell'esito
      $messaggio = "<p style='color: green;'>I dati della tua azienda sono stati inseriti con successo! Vai alla pagina di login:</p>";
      $link = "<a class='bot' href='login.php'>ACCEDI</a>";
    } else {
      $messaggio = "<p style='color: red;'>ERRORE: non è stato possibile salvare i dati dell'azienda.</p>";
    }
  }
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
    <div>
      <label>Indirizzo e-mail:</label>
      <input type="text" name="email" required>
    </div>
    <div>
      <label>Password:</label>
      <input type="text" name="password" required>
    </div>
    <button type="submit">FINITO</button>
    <?php echo $messaggio; ?>
    <?php echo $link; ?>
  </form>

  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
