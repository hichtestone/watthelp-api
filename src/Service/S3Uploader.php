<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Entity\File;
use App\Exceptions\UploadFailedException;
use League\Flysystem\FilesystemInterface;

class S3Uploader
{
    private string $bucket;
    private FilesystemInterface $s3Storage;
    private string $awsS3Url;

    public function __construct(FilesystemInterface $s3Storage, string $bucket, string $awsRegion)
    {
        $this->bucket = $bucket;
        $this->s3Storage = $s3Storage;

        $this->awsS3Url = 'https://' . $this->bucket . '.s3.' . $awsRegion . '.amazonaws.com/';
    }

    /**
     * @throws UploadFailedException
     */
    public function uploadFile(\Symfony\Component\HttpFoundation\File\File $receivedFile, Client $client): File
    {
        $mimeType = $receivedFile->getMimeType();
        // hardcoding can be removed when updating to symfony 5.2
        // cf https://github.com/symfony/symfony/pull/38407
        $fileExtension = $mimeType === 'image/jpeg' ? 'jpg' : $receivedFile->guessExtension();

        $filename = \sprintf('%s/%s.%s', $client->getId(), $receivedFile->getFilename(), $fileExtension);
        $content = file_get_contents($receivedFile->getPathname());

        $uploadSucceeded = $this->s3Storage->put($filename, $content, [
            'visibility' => 'public',
            'mimetype' => $mimeType,
        ]);

        if (!$uploadSucceeded) {
            throw new UploadFailedException("Could not upload file.");
        }

        $file = new File();
        $file->setMime($mimeType);
        $file->setName($filename);
        $file->setRaw($this->awsS3Url . $filename);
        $file->setThumb($this->awsS3Url . str_replace($client->getId().'/', $client->getId().'/thumb/', $filename));

        return $file;
    }

    public function uploadContent(string $path, string $content, string $fileName, string $mimeType): string
    {
        $path = \sprintf('%s/%s', trim($path, '/'), $fileName);

        $this->s3Storage->put($path, $content, [
            'visibility' => 'public',
            'mimetype' => $mimeType,
        ]);

        return $this->awsS3Url . $path;
    }
}
