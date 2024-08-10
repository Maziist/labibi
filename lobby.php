<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="lobby.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Kalnia+Glaze:wght@100..700&display=swap" rel="stylesheet">
  <title>Acceuil</title>
</head>

<body>
  <header>
    <div class="fog">
      <div class="fog-img"></div>
      <div class="fog-img fog-img-second"></div>
    </div>
    <h1>
      Qui aime bien ChaRis bien
    </h1>
  </header>
  <main>
    <form id="inscription" action="/inscription" method="POST">
      <h2>Inscription</h2>
      <label for="pseudo">Pseudo :</label>
      <input type="text" id="pseudo" name="pseudo" required>
      <label for="email">Email :</label>
      <input type="email" id="email" name="email" required>
      <label for="password">Mot de passe :</label>
      <input type="password" id="password" name="password" required>
      <label for="confirm-password">Confirmer le mot de passe :</label>
      <input type="password" id="confirm-password" name="confirm-password" required>
      <button type="submit">S'inscrire</button>
    </form>
    <form id="connexion" action="/connexion" method="POST">
      <h2>Connexion</h2>
      <label for="pseudo">Pseudo :</label>
      <input type="text" id="pseudo" name="pseudo" required>
      <label for="password">Mot de passe :</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Se connecter</button>
    </form>
  </main>
</body>

</html>