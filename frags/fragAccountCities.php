<article id="oncearticle">
    <div class="title">
        <img src="../imgs/softbread.svg">
        <h3>Produits</h3>
    </div>
    <div id="tablecontainment1" class="tablecontain">
        <table>
            <thead>
            <tr>
                <th>Produit</th>
                <th>Prix</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $request = $bdd->prepare("SELECT * FROM products");
            $request->execute();
            $result2 = $request->fetchAll();
            foreach ($result2 as $product) {
                echo "<tr>";
                $name = $product["name"];
                echo "<td>$name</td>";
                $price = $product["price"];
                echo "<td>$price</td>";
                echo "<td><span class='editbutton editorbutton'>Modifier </span><br><br><form action='adminaccountaction.php' method='post'><input type='hidden' name='action' value='deleteproduct'><input type='hidden' name='product' value='$name'><input class='editbutton' type='submit' value='Supprimer'></form></td>";
                echo "</tr>";
            }
            echo "<tr><td colspan='3' id='addcell'><span id='addbutton' class='editbutton'>Ajouter un produit</span></td></tr>"
            ?>
            </tbody>
        </table>
    </div>
</article>
<article>
    <div class="title">
        <img src="../imgs/delivery.svg">
        <h3>Villages</h3>
    </div>
    <div id="tablecontainment2" class="tablecontain">
        <table>
            <thead>
            <tr>
                <th>Village</th>
                <th>Lundi</th>
                <th>Mardi</th>
                <th>Mercredi</th>
                <th>Jeudi</th>
                <th>Vendredi</th>
                <th>Samedi</th>
                <th>Dimanche</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $request = $bdd->prepare("SELECT * FROM cities");
            $request->execute();
            $result3 = $request->fetchAll();
            foreach ($result3 as $city) {
                echo "<tr>";
                $name = $city["name"];
                echo "<td>$name</td>";
                for ($i = 0; $i <= 6; $i++) {
                    $delivered = $city[$i . "delivery"];
                    echo "<td>$delivered</td>";
                }
                echo "<td><span class='editbutton editorbutton2'>Modifier</span></td>";
                echo "</tr>";
            }
            ?>
            <tr>
                <td colspan="9" id="addcell2"><span id="addbutton2" class="editbutton">Ajouter un village</span></td>
            </tr>
            </tbody>
        </table>
    </div>
</article>