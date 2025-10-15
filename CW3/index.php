<?php
// echo "Присваивание по ссылке копия значений<br>";
// echo "<br>\n-----------------------------------------------------\n<br>";
// $original = '<br>оригинальное значение<br>';
// $prisvaivanie = &$original;
// echo "Original - $original \n";
// echo "prisvaivanie - $prisvaivanie \n";
// $prisvaivanie = 'novoe zna4enir prisvaivanie';
// echo "<br>\n-----------------------------------------------------\n<br>";
// echo "<br>Original - $original \n<br>";
// echo "<br>prisvaivanie - $prisvaivanie \n<br>";
// echo "<br>\n-----------------------------------------------------\n<br>";
// echo "<br>\n-----------------------------------------------------\n<br>";
// echo "<br>Передача по ссылке<br>";
// echo "<br>\n-----------------------------------------------------\n<br>";
// function addArgument(string &$text2)
// {
//     $text2 = 'bla bla bla';
// }
// $text1 = '<br> бла бла бла <br>';
// addArgument($text1);
// echo "$text1 \n";
// echo "<br>\n-----------------------------------------------------\n<br>";
// echo "<br>\n-----------------------------------------------------\n<br>";
// echo "<br>Возврат по ссылке<br>";
// echo "<br>\n-----------------------------------------------------\n<br>";

function &getDataRef()   
{
    return 4;
}

$obj = &getDataRef();
echo $obj;


?>