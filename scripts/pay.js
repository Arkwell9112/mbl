window.addEventListener("load", init);

// Redirection quand on est sur la page pay.php.

function init() {
    setTimeout(redirect, 3000);
}

function redirect() {
    window.location.replace("https://monboulangerlivreur.fr/pages/account.php");
}