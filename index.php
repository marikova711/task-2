<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<?php
  require_once "excelReader/excel_reader2.php";
  require_once "excelReader/SpreadsheetReader.php";

  $dbhost = "localhost";
  $dblogin = "root";
  $dbpass = "";
  $dbname = "equipment";
  $tablename = "transformers";

  $connect = new mysqli($dbhost, $dblogin, $dbpass);
  if($connect->connect_error) {
      die("Ошибка подключения: ".$connect->connect_error);
  }
  if (!$connect->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) echo "Ошибка: ".$connect->error;
  $connect->close();


  $mysql = new mysqli($dbhost, $dblogin, $dbpass, $dbname);
  $mysql->query("SET NAMES 'utf8'");

  if($mysql->connect_error) {
       die("Ошибка подключения к базе данных: ".$mysql->connect_error);
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
      $reader = new SpreadsheetReader('pricelist.xls');
      foreach ($reader as $key => $col) {
          if ($key == 1) continue;
          $name = $col[0];
          $price = $col[1];
          $wholesale = $col[2];
          $stock1 = $col[3];
          $stock2 = $col[4];
          $country = $col[5];
          $mysql->query("INSERT INTO `$tablename` (`name`, `price`, `wholesale`, `stock1`, `stock2`, `country`) VALUES ('$name', '$price', '$wholesale', '$stock1', '$stock2', '$country')");
      }

      $result = $mysql->query("SELECT * FROM `$tablename`");
      $max = $mysql->query("SELECT MAX(price) FROM `$tablename`");
      $max_price = $max->fetch_row()[0];
      $min = $mysql->query("SELECT MIN(`wholesale`) FROM `$tablename`");
      $min_price = $min->fetch_row()[0];
      $sum_stock1 = $mysql->query("SELECT SUM(`stock1`) FROM `$tablename`");
      $sum_stock2 = $mysql->query("SELECT SUM(`stock2`) FROM `$tablename`");
      $avg_price = $mysql->query("SELECT AVG(`price`) FROM `$tablename`");
      $avg_wholesale = $mysql->query("SELECT AVG(`wholesale`) FROM `$tablename`");
      $mysql->close();

      if($result) {
          echo "<form action='action.php'>
                <input type='submit' value='Обновить таблицу'>
                </form>
                <table>
                    <tr>
                        <th>№</th>
                        <th>Наименование товара</th>
                        <th>Стоимость, руб</th>
                        <th>Стоимость опт, руб</th>
                        <th>Наличие на складе 1, шт</th>
                        <th>Наличие на складе 2, шт</th>
                        <th>Страна производства</th>
                        <th>Примечание</th>
                    </tr>";
                    while ($row = $result->fetch_assoc()) {
                        if ($row['price'] == $max_price) echo "<tr style='background:#b34242;'>";
                        else if ($row['wholesale'] == $min_price) echo "<tr style='background:green;'>";
                        else echo "<tr>";
                        foreach ($row as $key => $column) echo "<td>$column</td>";
                        if ($row['stock1'] < 20 || $row['stock2'] < 20) {
                            echo "<td>Осталось мало!! Срочно докупите!!!</td>";
                        } else echo "<td></td>";
                        echo "</tr>";
                    }
               echo "</table>";
      }
  }
?>
</body>
</html>