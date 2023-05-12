<?php
    $dbhost = "localhost";
    $dblogin = "root";
    $dbpass = "";
    $dbname = "equipment";
    $tablename = "transformers";

    if (isset($_POST)) {
        $priceType = htmlspecialchars(trim($_POST['priceType']));
        $minPrice = htmlspecialchars(trim($_POST['minPrice']));
        $maxPrice = htmlspecialchars(trim($_POST['maxPrice']));
        $moreLess = htmlspecialchars(trim($_POST['moreLess']));
        $quantity = htmlspecialchars(trim($_POST['quantity']));

        if ($priceType == "" || $minPrice == "" || $maxPrice == "" || $moreLess == "" || $quantity == "") {
            echo "Все поля должны быть заполнены";
        } else if (!is_numeric($minPrice) || !is_numeric($maxPrice) || !is_numeric($quantity)) {
            echo "Введите корректные данные";
        } else if ($priceType !== "price" && $priceType !== "wholesale") {
            echo "Выберите розничную либо оптовую цену";
        } else if ($moreLess !== "more" && $moreLess !== "less") {
            echo "Выберите более или менее";
        } else {
            $mysql = new mysqli($dbhost, $dblogin, $dbpass, $dbname);
            if ($mysql->connect_error) {
                die("Ошибка подключения к базе данных: " . $mysql->connect_error);
            } else {
                $sql = "SELECT * FROM `$tablename` WHERE (`$priceType` BETWEEN '$minPrice' AND '$maxPrice') AND";
                $sql .= ($moreLess == 'more') ? " (`stock1` > '$quantity' OR `stock2` > '$quantity')" : " (`stock1` < '$quantity' OR `stock2` < '$quantity')";
                $result = $mysql->query($sql);
                $count = $result->num_rows;
                $mysql->close();
            }

            if($count) {
            ?>
            <table class="table">
                <tr class='text-center'>
                    <th>№</th>
                    <th>Наименование товара</th>
                    <th>Стоимость, руб</th>
                    <th>Стоимость опт, руб</th>
                    <th>Наличие на складе 1, шт</th>
                    <th>Наличие на складе 2, шт</th>
                    <th>Страна производства</th>
                    <th>Примечание</th>
                </tr>
                <?
                while ($row = $result->fetch_assoc()) {
                ?>
                    <tr>
                <?
                    foreach ($row as $key => $column) {?>
                        <td><?=$column?></td>
                <?}
                    if ($row['stock1'] < 20 || $row['stock2'] < 20) {
                        echo "<td>Осталось мало!! Срочно докупите!!!</td>";
                    } else echo "<td></td>";
                    echo "</tr>";
                    }
                    echo "</table>";
            } else {
                echo "Записей не найдено";
            }
        }

    }

