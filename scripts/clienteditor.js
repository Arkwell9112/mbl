window.addEventListener("load", init);

let using;

function init() {
    let buttons = document.getElementsByClassName("editbutton");
    for (const index in buttons) {
        buttons[index].addEventListener("click", editValue);
    }
    using = false;
}

// Permet d'afficher les inputs pour ajouter du solde par le boulanger.
function editValue(e) {
    if (!using) {
        let row = e.target.parentElement.parentElement;
        let cells = row.getElementsByTagName("td");
        cells[0].innerHTML = cells[0].innerHTML + "<input name='username' type='hidden' value='" + cells[0].innerHTML + "'>" + "<input type='hidden' name='action' value='editvalue'>";
        cells[3].innerHTML = "<input size='2' name='amount' type='text' value='0'>";
        cells[4].innerHTML = "<input class='editbutton' type='submit' value='Valider'>";
        let tablediv = document.getElementById("tablecontainment");
        tablediv.innerHTML = "<form action='../pages/adminaccountaction.php' method='post'>" + tablediv.innerHTML + "</form>";
        using = true;
    }
}