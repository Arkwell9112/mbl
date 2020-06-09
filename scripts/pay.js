window.addEventListener("load", init);

function init() {
    setTimeout(redirect, 3000);
}

function redirect() {
    window.location.replace("https://monboulangerlivreur.fr/pages/account.php");
}