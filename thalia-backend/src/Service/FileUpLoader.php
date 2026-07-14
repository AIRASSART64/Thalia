<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUpLoader
{

    public function __construct(private SluggerInterface $slugger)
    { }
    public function upload(UploadedFile $file, string $targetDirectory) : ?string
    {
        //  Récupérartion du nom d'origine sans extension (jpg, png,...)
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // transformation du nom récupéré en un nom néttoyé et sécurisé grâce à l'outil SlugInterface
        // ce nouveau nom est au format compatible avec les urls et les serveurs Linux
        $safeFilename = $this->slugger->slug($originalFilename);
        
        // le nom de chaque image est rendu unique et on réeinjecte l'extension réel 
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // try catch pour éviter le crash du systéme au momment de l'enregistrement de l'image dans son dossier final
        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            return null; // En cas de problème d'écriture (ex: droits sur le dossier)
        }

        // Return du nom définitif de l'image pour enregistrement en base de données
        return $fileName;
    }
}
