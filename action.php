<link rel="stylesheet" href="styles/style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<?php
require_once "excelReader/excel_reader2.php";
require_once "excelReader/SpreadsheetReader.php";

if ($_REQUEST['SEND_DATA'] == 'Table') {

    $dbhost = "localhost";
    $dblogin = "root";
    $dbpass = "";
    $dbname = "equipment";
    $tablename = "transformers";

    $connect = new mysqli($dbhost, $dblogin, $dbpass);
    if ($connect->connect_error) {
        die("Ошибка подключения: " . $connect->connect_error);
    }
    if (!$connect->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) echo "Ошибка: " . $connect->error;
    $connect->close();

    $mysql = new mysqli($dbhost, $dblogin, $dbpass, $dbname);
    $mysql->query("SET NAMES 'utf8'");

    if ($mysql->connect_error) {
        die("Ошибка подключения к базе данных: " . $mysql->connect_error);
    } else {
        $mysql->query("CREATE TABLE IF NOT EXISTS `$tablename` (
        id INT (11) NOT NULL AUTO_INCREMENT,
        name VARCHAR (80) NOT NULL,
        price DECIMAL (10, 2) NOT NULL,
        wholesale INT(11) NOT NULL,
        stock1 INT (5) NOT NULL,
        stock2 INT (5) NOT NULL,
        country VARCHAR (30) NOT NULL,
        PRIMARY KEY (id)
        )");

        $mysql->query("TRUNCATE TABLE `$tablename`");

        $reader = new SpreadsheetReader('pricelist.xls');
        $sql = "INSERT INTO `$tablename` (`name`, `price`, `wholesale`, `stock1`, `stock2`, `country`) VALUES ";
        $len = count($reader);
        foreach ($reader as $key => $col) {
            if ($key == 1) continue;
            if ($col[1] == "Стоимость") continue;
            $sql .= "('$col[0]', '$col[1]', '$col[2]', '$col[3]', '$col[4]', '$col[5]')";
            if ($key !== $len) {
                $sql .= ", ";
            }
        }
        $mysql->query($sql);

        $result = $mysql->query("SELECT * FROM `$tablename`");
        $max = $mysql->query("SELECT MAX(price) FROM `$tablename`");
        $max_price = $max->fetch_row()[0];
        $min = $mysql->query("SELECT MIN(`wholesale`) FROM `$tablename`");
        $min_price = $min->fetch_row()[0];
        $sum_1 = $mysql->query("SELECT SUM(`stock1`) FROM `$tablename`");
        $sum_stock1 = $sum_1->fetch_row()[0];
        $sum_2 = $mysql->query("SELECT SUM(`stock2`) FROM `$tablename`");
        $sum_stock2 = $sum_2->fetch_row()[0];
        $avg_1 = $mysql->query("SELECT AVG(`price`) FROM `$tablename`");
        $avg_price = $avg_1->fetch_row()[0];
        $avg_2 = $mysql->query("SELECT AVG(`wholesale`) FROM `$tablename`");
        $avg_wholesale = $avg_2->fetch_row()[0];
        $mysql->close();
    }
?>
    <div class="filters">
        <div class="container">
            <form>
                <span class="text">Показать товары, у которых:</span>
                <div class="select-wrapper">
                    <select id="priceType">
                        <option value="price">Розничная цена</option>
                        <option value="wholesale">Оптовая цена</option>
                    </select>
                </div>
                <span class="text">от</span>
                <input type="text" id="minPrice" class="input">
                <span class="text">до</span>
                <input type="text" id="maxPrice" class="input">
                <span class="text">рублей и на складе</span>
                <div class="select-wrapper">
                    <select id="moreLess">
                        <option value="more">Более</option>
                        <option value="less">Менее</option>
                    </select>
                </div>
                <input type="text" id="quantity" class="input">
                <span class="text">штук</span>
                <input type="submit" id="showGoods" value="Показать товары" class="btn">
            </form>
            <div id="errorMessage"></div>
        </div>
    </div>
    <div class="content">
        <div class="container">
            <table class="table">
                    <tr>
                        <th>№</th>
                        <th>Наименование товара</th>
                        <th>Стоимость, руб</th>
                        <th>Стоимость опт, руб</th>
                        <th>Наличие на складе 1, шт</th>
                        <th>Наличие на складе 2, шт</th>
                        <th>Страна производства</th>
                        <th>Примечание</th>
                    </tr>
        <?php
            while ($row = $result->fetch_assoc()) {
                if ($row['price'] == $max_price) {?>
                    <tr style="background: #b34242;">
                <?} else if ($row['wholesale'] == $min_price) {?>
                    <tr style="background: green;">
                <?} else {?>
                    <tr>
                <?}
                foreach ($row as $key => $column) {?>
                    <td><?=$column?></td>
                    <?}
                    if ($row['stock1'] < 20 || $row['stock2'] < 20) {
                        echo "<td>Осталось мало!! Срочно докупите!!!</td>";
                    } else echo "<td></td>";
                    echo "</tr>";
                }
                echo "</table>
            <p>Общее количество товаров на складе 1: " . $sum_stock1 . " шт.</p>
            <p>Общее количество товаров на складе 2: " . $sum_stock2 . " шт.</p>
            <p>Средняя стоимость розничной цены товара: " . $avg_price . " руб.</p>
            <p>Средняя стоимость оптовой цены товара: " . $avg_wholesale . " руб.</p>
        </div>
    </div>";
    }
?>
<script src="js/ajax.js"></script>

