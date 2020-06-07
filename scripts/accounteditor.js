window.addEventListener("load", init);

let addbutton;
let using;
let editbuttons;

function init() {
    addbutton = document.getElementById("addbutton");
    addbutton.addEventListener("click", addproduct);
    using = false;
    editbuttons = document.getElementsByClassName("editorbutton");
    for (const button in editbuttons) {
        editbuttons[button].addEventListener("click", editproduct);
    }
}

function addproduct(e) {
    if (!using) {
        let form = e.target.parentElement;
        let select = "";
        let products = document.getElementById("products");
        products = JSON.parse(products.getAttribute("value"));
        for (const product in products) {
            select = select + "<option value='" + products[product]["name"] + "'>" + products[product]["name"] + " - " + products[product]["price"] + "€</option>";
        }
        select = "<form action='accountaction.php' method='post'>" +
            "<input type='hidden' value='add' name='action'>" +
            "<select name='product' class='productselect'>" +
            "<option value='none'>Sélectionnez un produit</option>" +
            select +
            "</select><br>" +
            "<input type='submit' id='addsubmit' value='Valider'>" +
            "</form>";
        form.innerHTML = select;
        $(".productselect").select2();
        using = true;
    }
}

function editproduct(e) {
    if (!using) {
        let row = e.target.parentElement.parentElement;
        let cells = row.getElementsByTagName("td");
        for (const cell in cells) {
            if (cell == 0) {
                cells[cell].innerHTML = cells[cell].innerHTML + "<input type='hidden' name='product' value='" + cells[cell].innerHTML + "'>" + "<input type='hidden' name='action' value='edit'>";
            } else if (cell != 8) {
                if (cells[cell].innerHTML != "Non livré") {
                    cells[cell].innerHTML = "<input type='text'class='selectstyle' size='2' name='" + (cell - 1) + "' value='" + cells[cell].innerHTML + "'>";
                }
            } else {
                cells[cell].innerHTML = "<input type='submit' value='Valider' class='editbutton'>";
            }
        }
        let tablediv = document.getElementById("tablecontainment");
        tablediv.innerHTML = "<form action='accountaction.php' method='post'>" + tablediv.innerHTML + "</form>";
        using = true;
    }
}