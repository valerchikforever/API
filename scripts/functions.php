<?php
//Получить список заданий "-GET /tasks"
function getTasks($pdo){
    $sql = "SELECT * FROM `tasks`"; 
    $stmt = $pdo->query($sql);
    $tasks = []; //массив для задач

    while ($task = $stmt->fetch(PDO::FETCH_ASSOC)){
        $tasks[] = $task;
    }

    echo json_encode($tasks);
}

//Получить задание по id "-GET /tasks/{id}"
function getTask($pdo, $id){
    $sql = "SELECT * FROM `tasks` WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    try{
        $stmt->execute([
            'id' => $id
            ]);
        if ($task = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo json_encode($task);
        }
        else{
            http_response_code(404);
            $result = [
                'status' => false,
                'message' => 'Task not found (Задача не найдена)'
            ];

            echo json_encode($result);
        }
    }catch(PDOException $e){
        http_response_code(400);
        $result = [
            'status' => false,
            'message' => 'Invalid request (Некорректный запрос)'
        ];

        echo json_encode($result);
    }
}

//Добавить задание "-POST /tasks"
function addTask($pdo, $data){
    $title = trim($data['title']);
    $description = $data['description'];
    $status = statusConvert($data);

    if (empty($title)){ //Валидация title
        http_response_code(400);
        $result = [
            'status' => false,
            'message' => 'The task name is not specified! (Не указано название задачи!)',
            ];
        
        echo json_encode($result);
        return;
    }
    elseif(mb_strlen($title) > 255){    //Проверка длины. Максимальная длина названия 255 символов
        http_response_code(431);
        $result = [
            'status' => false,
            'message' => 'The request header fields are too large! (Поля заголовка запроса слишком большие!)',
            ];
        
        echo json_encode($result);
        return;
    }

    $sql = "INSERT INTO `tasks` (`title`, `description`, `status`) VALUES (:title, :description, :status)";
    $stmt = $pdo->prepare($sql);
    try{
        $stmt->execute([
            'title' => $title, 
            'description' => $description,
            'status' => $status
            ]);
            
        http_response_code(201);
        $result = [
            "status" => true,
            "post_id" => $pdo->lastInsertId()
            ];

        echo json_encode($result);
    }catch(PDOException $e){
        http_response_code(400);
        $result = [
            'status' => false,
            'message' => 'Error request (Ошибка запроса)'
        ];

        echo json_encode($result);
    }
}

//Полное обновление задания "-PUT /tasks/{id}"
function updatePost($pdo, $id, $data){
    $title = trim($data['title']);

    //Если поле "status" или "description" не указаны, то поле очищается в БД
    if (empty($data['status'])){
        $data['status'] = 0;
    }

    if (empty($data['description'])){
        $description = "";
    }
    else{
        $description = $data['description'];
    }
    
    $status = statusConvert($data);

    if (empty($title)){ //Валидация title
        http_response_code(400);
        $result = [
            'status' => false,
            'message' => 'The task name is not specified! (Не указано название задачи!)',
            ];
        
        echo json_encode($result);
        return;
    }
    elseif(mb_strlen($title) > 255){    //Проверка длины. Максимальная длина названия 255 символов
        http_response_code(431);
        $result = [
            'status' => false,
            'message' => 'The request header fields are too large! (Поля заголовка запроса слишком большие!)',
            ];
        
        echo json_encode($result);
        return;
    }

    $sql = "UPDATE `tasks` SET `title` = :title, `description` = :description, `status` = :status WHERE `tasks`.`id` = :id";
    $stmt = $pdo->prepare($sql);
    try{
        $stmt->execute([
            'id' => $id,
            'title' => $title, 
            'description' => $description,
            'status' => $status,
            ]);
            
        http_response_code(200);
        $result = [
            "status" => true,
            "message" => 'Task updated (Задание обновлено)'
            ];

        echo json_encode($result);
    }catch(PDOException $e){
        http_response_code(400);
        $result = [
            'status' => false,
            'message' => 'Error request (Ошибка запроса)'
        ];

        echo json_encode($result);
    }
}

//Удаление задания "-DELETE /tasks/{id}"
function deletePost($pdo, $id){
    $sql = "DELETE FROM `tasks` WHERE `tasks`.`id` = :id";
    $stmt = $pdo->prepare($sql);
    
    try{
        $stmt->execute([
            'id' => $id
            ]);

        if($stmt->rowCount() === 0){    //Проверка сколько строк этот запрос задел. Если ни одного, то вывод ошибки (Без проверки пишет "Задача удалена")
            http_response_code(404);
            $result = [
                'status' => false,
                'message' => 'Task not found (Задача не найдена)',
                ];
            echo json_encode($result);
            return;
        }    

        http_response_code(200);
        $result = [
                "status" => true,
                "message" => "Task is deleted (Задача удалена)"
                ];
        echo json_encode($result);
    }catch(PDOException $e){
        http_response_code(400);
        $result = [
            'status' => false,
            'message' => 'Error request (Ошибка запроса)'
        ];

        echo json_encode($result);
    }
}

//Фильтрация поля status. Принимается только 1, 0, true, false. 1 - Задача выполнена, 0 - Задача в процессе выполнения.
function statusConvert($data){
    $result = [
            'status' => false,
            'message' => 'The status must be of a logical type: 1, 0, true, false (Статус должен быть логического типа: 1, 0, true, false)',
            ];

    $filter = filter_var($data["status"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($filter === NULL) {         //Если было введено число >1 или <0, то выдаст ошибку.
        http_response_code(400);
        echo json_encode($result);
        exit();
    }

    $status = is_numeric($data['status']) ? $data['status'] : (int)filter_var($data['status'], FILTER_VALIDATE_BOOLEAN); //Фильтрация ввода. TRUE FALSE будут указаны в БД как 1 или 0
    return $status;                 //Возврат статуса всегда 1 или 0.
}