<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'todolist';

try{
    $pdo = new PDO("mysql: host=$host; dbname=$db;", $user, $password);
}catch(PDOException $e){
    echo "Ошибка подключения к БД: {$e->getMessage()}";
}
?>