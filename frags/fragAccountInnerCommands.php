<?php
if ($first) {
    $id = "oncearticle";
} else {
    $id = "";
}
?>
<article id="<?php echo $id ?>">
    <div class="title">
        <img src="../imgs/delivery.svg">
        <h3><?php echo $key ?></h3>
    </div>
    <div class="tablecontain">
        <table>
            <thead>
            <tr>
                <th>Produit</th>
                <th>Lundi</th>
                <th>Mardi</th>
                <th>Mercredi</th>
                <th>Jeudi</th>
                <th>Vendredi</th>
                <th>Samedi</th>
                <th>Dimanche</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($products as $key2 => $product) {
                echo "<tr>";
                echo "<td>$key2</td>";
                for ($i = 0; $i <= 6; $i++) {
                    $quantity = $product[$i];
                    echo "<td>$quantity</td>";
                }
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</article>
