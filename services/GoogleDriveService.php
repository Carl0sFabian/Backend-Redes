<?php

require_once __DIR__ . '/../vendor/autoload.php';

class GoogleDriveService {
    private $client;
    private $service;
    private $folderId = '1UGm3g1s1JMgkZ_d8V2ab0MBXi4wJxPQt';

    

    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(__DIR__ . '/../config/client_secret.json');
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->client->setAccessType('offline');

        $tokenPath = __DIR__ . '/../config/token.json';
        
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }

        
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                
                file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
            } else {
                throw new Exception("Falta autorizar la aplicación. Ejecuta get_token.php primero.");
            }
        }

        $this->service = new Google_Service_Drive($this->client);
    }

    public function uploadFile($fileTempPath, $fileName, $mimeType) {
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $fileName,
            'parents' => array($this->folderId)
        ));

        $content = file_get_contents($fileTempPath);

        
        $file = $this->service->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink, webContentLink'
        ));

        
        $permission = new Google_Service_Drive_Permission(array(
            'type' => 'anyone',
            'role' => 'reader'
        ));
        $this->service->permissions->create($file->id, $permission);

        
        return array(
            'id_drive' => $file->id,
            'url_ver' => $file->webViewLink, 
            'url_descarga' => $file->webContentLink 
        );
    }

    public function getFile($fileId, $rawContent = false) {
        try {
            if ($rawContent) {
                
                
                $response = $this->service->files->get($fileId, array('alt' => 'media'));
                
                return $response->getBody()->getContents();
            } else {
                
                $file = $this->service->files->get($fileId, array(
                    'fields' => 'id, name, mimeType, webViewLink, webContentLink'
                ));
                
                return array(
                    'id_drive' => $file->id,
                    'nombre_original' => $file->name,
                    'tipo' => $file->mimeType,
                    'url_ver' => $file->webViewLink,
                    'url_descarga' => $file->webContentLink
                );
            }
        } catch (Exception $e) {
            
            error_log("Error al obtener el archivo de Drive: " . $e->getMessage());
            return null;
        }
    }
}
?>