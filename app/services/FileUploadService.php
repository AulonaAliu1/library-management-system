<?php
declare(strict_types=1);

class FileUploadService{
    private string $uploadDir;
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    private int $maxFileSize = 2100000;

    public function __construct(?string $uploadDir = null){
        $this->uploadDir = $uploadDir ?? __DIR__ . '/../../uploads/books/';
        
        if(!is_dir($this->uploadDir)){
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function uploadImage(array $file): ?string{
        if($file['error'] !== UPLOAD_ERR_OK){
            return null;
        }

        if($file['size'] > $this->maxFileSize){
            throw new Exception("Imazhi eshte shume i madh. Limiti eshte 2MB.");
        }

        $fileName = (string) $file['name'];
        $fileCmps = explode('.', $fileName);
        $fileExtension = strtolower(end($fileCmps));

        if(!in_array($fileExtension, $this->allowedExtensions, true)){
            throw new Exception("Format i palejuar. Lejohen vetem: " . implode(', ', $this->allowedExtensions));
        }

        $newFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
        $destinationPath = $this->uploadDir . $newFileName;
        
        if(move_uploaded_file($file['tmp_name'], $destinationPath)){
            return 'uploads/books/' . $newFileName;
        }

        return null;
    }
}
?>