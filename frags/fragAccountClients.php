<article id="oncearticle">
    <div class="title">
        <img src="../imgs/datas.svg">
        <h3>Clients</h3>
    </div>
    <div id="tablecontainment">
        <table>
            <thead>
            <tr>
                <th>Nom d'utilisateur</th>
                <th>Mail</th>
                <th>Téléphone</th>
                <th>Solde</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($result as $user) {
                $request = $bdd->prepare("SELECT * FROM accounts WHERE username=:username");
                $request->execute(array(
                    "username" => $user["username"]
                ));
                $result2 = $request->fetchAll();
                echo "<tr>";
                $username2 = $user["username"];
                echo "<td>$username2</td>";
                $mail = $result2[0]["mail"];
                echo "<td>$mail</td>";
                $phone = $user["phone"];
                echo "<td>$phone</td>";
                $sold = $user["value"];
                echo "<td>$sold</td>";
                echo "<td><span class='editbutton editorbutton'>Ajouter du solde</span></td>";
            }
            ?>
            </tbody>
        </table>
    </div>
</article>