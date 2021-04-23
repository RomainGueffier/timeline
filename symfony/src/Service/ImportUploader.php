<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImportUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.timeline'; //$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new FileException($e);
        }

        // remove automatically old import files older than 10 minutes
        $this->clearAll();

        return $fileName;
    }

    public function delete($fileName) {
        $filesystem = new Filesystem();
        $filePath = $this->getTargetDirectory().'/'.$fileName;
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    // https://stackoverflow.com/a/20548138
    public function clearAll() {
        $directory = $this->getTargetDirectory();

        foreach (new \DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getExtension() === 'gitignore') {
                continue;
            }
            // clear all .timeline files older than 10 minutes
            if ($fileInfo->isFile() && time() - $fileInfo->getCTime() >= 10*60) {
                unlink($fileInfo->getRealPath());
            }
        }
    }
}
