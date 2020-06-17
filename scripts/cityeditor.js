window.addEventListener("load", first);
window.addEventListener("load", init);
window.addEventListener("load", reinit);

let inuse;

// Trois fonctions d'initialisation des events listener.
function first() {
    let editbuttonss = document.getElementsByClassName("editorbutton");
    for (const index in editbuttonss) {
        editbuttonss[index].addEventListener("click", editproduct);
    }
}

function init() {
    let addbutton1 = document.getElementById("addbutton");
    addbutton1.addEventListener("click", newProduct);
    inuse = false;
}

function reinit() {
    document.getElementById("addbutton2").addEventListener("click", addcity);
    let buttonedit = document.getElementsByClassName("editorbutton2");
    for (const index in buttonedit) {
        buttonedit[index].addEventListener("click", editcity);
    }
}

// Permet de modifier le prix d'un produit. Ajoute les inputs nécessaire.
function editproduct(e) {
    if (!inuse) {
        let row = e.target.parentElement.parentElement;
        let cells = row.getElementsByTagName("td");
        cells[0].innerHTML = cells[0].innerHTML + "<input type='hidden' name='product' value='" + cells[0].innerHTML + "'>" + "<input type='hidden' name='action' value='editproduct'>";
        cells[1].innerHTML = "<input size='2' type='text' name='price' value='" + cells[1].innerHTML + "'>";
        cells[2].innerHTML = "<input class='editbutton' type='submit' value='Valider'>";
        let tablediv = document.getElementById("tablecontainment1");
        tablediv.innerHTML = "<form action='adminaccountaction.php' method='post'>" + tablediv.innerHTML + "</form>";
        inuse = true;
        $(".deleter").remove();
    }
}

// Permet d'afficher l'input pour ajouter un nouveau produit.
function newProduct(e) {
    if (!inuse) {
        let cell1 = document.getElementById("addcell");
        cell1.innerHTML = "<form action='adminaccountaction.php' method='post'>Entrez le nom du produit :<br><br><input type='hidden' name='action' value='addproduct'><input type='text' name='product'><br><br><input class='editbutton' type='submit' value='Valider'></form>";
        inuse = true;
    }
}

// Permet d'afficher l'input pour un  nouveau village.
function addcity() {
    if (!inuse) {
        let cell = document.getElementById("addcell2");
        cell.innerHTML = "<form action='adminaccountaction.php' method='post'>Entrez le nom du village :<br><br><input type='hidden' name='action' value='addcity'><input type='text' name='city'><br><br><input class='editbutton' type='submit' value='Valider'></form>";
        inuse = true;
    }
}

// Permet l'ajout des inputs pour modifier une ville.
function editcity(e) {
    if (!inuse) {
        let row = e.target.parentElement.parentElement;
        let cells = row.getElementsByTagName("td");
        cells[0].innerHTML = cells[0].innerHTML + "<input type='hidden' name='city' value='" + cells[0].innerHTML + "'>" + "<input type='hidden' name='action' value='editcity'>";
        for (let i = 0; i <= 6; i++) {
            cells[i + 1].innerHTML = "<input type='text' name='" + i + "' value='" + cells[i + 1].innerHTML + "' size='2'>";
        }
        cells[8].innerHTML = "<input type='submit' value='Valider' class='editbutton'>";
        let tablediv = document.getElementById("tablecontainment2");
        tablediv.innerHTML = "<form action='adminaccountaction.php' method='post'>" + tablediv.innerHTML + "</form>";
    }
}