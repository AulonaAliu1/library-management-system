<?php


declare(strict_types=1);
class ExternalApiService{
    public function getBookByISBN(string $isbn): ?array {

    $url="https://openlibrary.org/isbn/" .urlencode($isbn).".json";
    try{
        $response=@file_get_contents($url);

        if($response===false){
            return null;

        }

        $data=json_decode($response,true);

        if(!$data){
            return null;

        }
        return[ // title, author, category, isbn, quantity, description, book_cover
            'title'=>$data['title']??'',

            'publish_date'=>$data['publish_date']??'',

            'description' =>is_array($data['description']??null)
            ?($data['description']['value']??'')
            :($data['description']??''),

            'author' => $data['authors'][0]['key'] ?? '',
            
            'cover' => isset($data['covers'][0])
            ? 'https://covers.openlibrary.org/b/id/' .
            $data['covers'][0] . '-L.jpg'
            : '',




        

        ];


    }
    catch(Exception $e){
        return null;

    }
    }
}
