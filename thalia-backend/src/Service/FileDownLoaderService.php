<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileDownloaderService
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    /**
     * Génère une BinaryFileResponse pour un fichier situé dans /uploads/
     * 
     * @param string $subFolder Exemple: 'shows/artistic_files'
     * @param string $filename Le nom du fichier en BDD
     * @param bool $forceDownload 'false' pour afficher dans le navigateur (inline), 'true' pour forcer le téléchargement
     */
    public function downloadFromUploads(string $subFolder, string $filename, bool $forceDownload = false): BinaryFileResponse
    {
        // Nettoyage du chemin pour éviter les traversées de répertoire (Security / Path Traversal)
        $cleanSubFolder = trim($subFolder, '/');
        $cleanFilename = basename($filename);

        $filePath = sprintf('%s/uploads/%s/%s', $this->projectDir, $cleanSubFolder, $cleanFilename);

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Le fichier demandé est introuvable.');
        }

        $response = new BinaryFileResponse($filePath);
        
        $disposition = $forceDownload 
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT 
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $response->setContentDisposition($disposition, $cleanFilename);

        return $response;
    }
}