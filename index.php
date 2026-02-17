<?php
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
            getTask($pdo, $id);
            break;
        }
        else{
            getTasks($pdo);
            break;
        }
    case "POST":
        addTask($pdo, $_POST);
        break;
    case "PUT":
        if($id){
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            updatePost($pdo, $id, $data);
        }
        break;
    case "DELETE":
        if($id){
            deletePost($pdo, $id);
        }
        break;
    default:
        http_response_code(405);
        $result = [
            'status' => false,
            'message' => 'Method Not Allowed (Метод не поддерживается)'
        ];
        
        echo json_encode($result);
        break;
}
?>