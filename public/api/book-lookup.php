<?php



declare(strict_types=1);

header('Content-Type: application/json');



require_once '../../app/services/ExternalApiService.php';

$isbn = trim($_GET['isbn']??'');

if(empty($isbn)){
    echo json_encode([
        'success'=>false,
        'message'=>'isbn is required'
    ]);
    exit;
}



$apiService=new ExternalApiService();
 

$book=$apiService->getBookByISBN($isbn);

if(!$book){
    echo json_encode([
    'success'=>false,
    'message'=>'Book not found'
    ]);
    exit;
}


echo json_encode([
    'success'=>true,
    'data'=>$book
]);



