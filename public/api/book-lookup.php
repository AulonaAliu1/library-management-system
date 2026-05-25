<?php



declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');



require_once __DIR__ . '/../../app/services/ExternalApiService.php';

$isbn = trim($_GET['isbn']??'');

if(empty($isbn)){
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'message'=>'ISBN is required.'
    ]);
    exit;
}



$apiService=new ExternalApiService();
 

$book=$apiService->getBookByISBN($isbn);

if(!$book){
    http_response_code(404);
    echo json_encode([
    'success'=>false,
    'message'=>$apiService->lastError() ?? 'Book not found.'
    ]);
    exit;
}


echo json_encode([
    'success'=>true,
    'data'=>$book
]);



