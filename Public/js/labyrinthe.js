// On surveille les touches du clavier
window.addEventListener('keydown', (e) => {
    e.preventDefault();
    // On gère les 2 possibilités soit les touches "zqsd" soit les flèches directionnelles
    // On complète le formulaire (invisible) avec la valeur correspondante
    if ( e.key === "ArrowUp" || e.key === "z" )
    {
        document.getElementById("mvt_robot").value = "z";
    }
    else if( e.key === "ArrowRight" || e.key === "d" )
    {
        document.getElementById("mvt_robot").value = "d";
    }
    else if( e.key === "ArrowDown" || e.key === "s" )
    {
        document.getElementById("mvt_robot").value = "s";
    }
    else if( e.key === "ArrowLeft" || e.key === "q" )
    {
        document.getElementById("mvt_robot").value = "q";
    }
    // On soumet le formulaire (pour traitement en php)
    document.forms["form_robot"].submit();
}, true);
