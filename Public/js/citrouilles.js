// On initialise les variables utiles
let compteur = 0;
let isClicable = true; // boolean permettant d'éviter le multiclique
let afficheCompteur = document.getElementsByTagName('span');
let citrouilles = document.getElementsByClassName('trou');
let boutonJouer = document.getElementsByClassName('btn_jouer');

// Cette fonction permet de gérer les actions à réaslier lorsque l'utilisateur clic sur une citrouille
function clicCitrouille()
{
   // Si on a le droit de cliquer, on augmente le score
   if (isClicable) compteur++;
   // On bloque l'incrémentation du compteur après le 1er clique
   isClicable = false; 
   // On affiche en directe le score actuel
   afficheCompteur[0].innerText = compteur;
}

// Cette fonction gère une partie de "Tape citrouilles"
function jouer()
{
   // Une fois la partie lancée On cache le bouton 'Jouer' pour éviter de lancer 2 parties en même temps
   boutonJouer[0].style.visibility = "hidden";
   // Le compteur de score actuel est remis à zéro
   afficheCompteur[0].innerText = 0;
   // On fait apparaitre les citrouilles aléatoirement toutes les secondes
   let intervalId = setInterval(function() 
   {
      // On réactive l'incrémentation du compteur
      isClicable = true;
      // On choisit aléatoirement duquel des 3 trous la citrouille va sortir
      let citrouilleAleatoire = Math.floor(Math.random()*3);
      citrouilles[citrouilleAleatoire].classList.toggle("up");
      // On fait disparaitre la citrouille au bout de 500 ms
      let timeoutId = setTimeout(function () 
      {
         citrouilles[citrouilleAleatoire].classList.toggle("up");
      }, 500);
   }, 1000);

   // On défini un temps de jeu de 30 secondes (30 apparitions de citrouilles max)
   let tempJeu = setTimeout(function()
   {
      // A la fin de la partie, on arrète l'intervalle pour l'apparition des citrouilles
      clearInterval(intervalId);
      // On réaffiche le bouton Jouer
      boutonJouer[0].style.visibility = "visible";
      // On envoie le score à php pour enregistrement dans la base de données
      envoiScore(function(resp)
      {
         document.getElementById("score_actuel").innerHTML = resp.citrouilles_dernier_score;
         document.getElementById("score_max").innerHTML = resp.citrouilles_meilleur_score;
         compteur = 0;
      });
   }, 30000);
}


function envoiScore(callback)
{
   const request = new XMLHttpRequest(); 
   // On ouvre la connexion avec les paramètres : méthode, url, asynchrone (true ou false)
   request.open("POST", "../src/Ajax/ajaxCitrouilles.php", true); 
   // On surveille le changement d'état de la requete http
   request.addEventListener(
		"readystatechange",
      function()
      {
         // Si la requêtes est terminée
			if( request.readyState === XMLHttpRequest.DONE )
			{
            // et si la status correspond à un code 200 (succès de la requête)
				if (Math.floor(request.status / 100) === 2)
				{
               // on execute le calback avec en paramètre la reponse en JSON
					callback(JSON.parse(request.responseText));
				}
				else
				{
               // Sinon on execute le callback avec une erreur envoyée en format JSON
					callback({ "error" : "La requête n'a pu aboutir - " + request.status + " : " + request.statusText });
				}
			}
      });
   // On ajoute l'en-tête de la réponse
   request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   // On envoie le score obtenu
   request.send(`score=${compteur}`);
}
