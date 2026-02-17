<?php
/* Надеюсь я правильно понял задание. Дополнительно расписал комментарии для более удобной и быстрой проверки :3 
Не засчитывайте этот комментарий за ошибку, в итоговых проектах подобного не будет!
PHP-8.0, MySQL-8.0 Apache-2.4                                                                                       */
header('Content-type: application/json'); //возвращать всё в JSON
require_once "config/connect.php";     //подключение к БД
require_once "scripts/functions.php";  //CRUD-операции

$method = $_SERVER["REQUEST_METHOD"]; //получение метода
$q = $_GET['q']; //строка после http://api/ (tasks/1)
$params = explode('/', $q);

$type = $params[0];
$id = isset($params[1]) ? $params[1] : null;

if ($type !== 'tasks'){
    http_response_code(502);
    $result = [ 
        'status' => false,
        'message' => 'Bad Gateway (Ошибочный шлюз)'
    ];
    exit(json_encode($result));
}

switch ($method){
    case "GET":
        if ($id){
            getTask($pdo, $id);     //Поиск задачи по id
            break;
        }
        else{
            getTasks($pdo);         //Поиск всех задач
            break;
        }
    case "POST":
        addTask($pdo, $_POST);      //Создание задачи
        break;
    case "PUT":
        if($id){
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            updatePost($pdo, $id, $data);   //Обновление задачи (перезаписывание)
        }
        break;
    case "DELETE":
        if($id){
            deletePost($pdo, $id);  //Удаление задачи по id
        }
        break;
    default:               //При любом другом методе выдаст ошибку
        http_response_code(405);
        $result = [
            'status' => false,
            'message' => 'Method Not Allowed (Метод не поддерживается)'
        ];
        
        echo json_encode($result);
        break;
}
?>