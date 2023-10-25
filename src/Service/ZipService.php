<?php

declare(strict_types=1);

namespace App\Service;

use App\Exceptions\InvalidFileException;

class ZipService
{
    /**
     * @throws InvalidFileException
     */
    public function extract(string $zipPath, array $filesToExtract): array
    {
        $zip = new \ZipArchive;
        $resOpen = $zip->open($zipPath);
        if ($resOpen !== true) {
            throw new InvalidFileException('L\'ouverture du fichier a échoué.');
        }

        try {
            $tmpDir = sys_get_temp_dir();
            $resExtract = $zip->extractTo($tmpDir, $filesToExtract);
            if (!$resExtract) {
                throw new InvalidFileException('L\'extraction de l\'archive a échoué.');
            }
            $filePaths = [];
            foreach ($filesToExtract as $requiredFile) {
                $filePaths[$requiredFile] = $tmpDir . DIRECTORY_SEPARATOR . $requiredFile;
            }
            return $filePaths;
        } finally {
            $zip->close();
        }
    }
}
