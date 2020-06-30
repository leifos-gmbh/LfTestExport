<?php

class lfTestExportFileReader
{
    private $logger = null;
    private $settings = null;

    private $files = [];


    public function __construct()
    {
        $this->settings = \lfTestExportSettings::getInstance();
        $this->logger = \ilLfTestExportPlugin::getInstance()->getLogger();

        $this->read();
    }

    public function getIds()
    {
        return array_keys($this->files);
    }

    public function idExists(int $file_id) : bool
    {
        $this->logger->dump($this->files);
        return array_key_exists($file_id, $this->files);
    }

    /**
     * @param int    $file_id
     * @param string $a_version_id
     * @return bool
     */
    public function versionIdExists(int $file_id, string $a_version_id) : bool
    {
        if (!$this->idExists($file_id)) {
            return false;
        }
        foreach ($this->files[$file_id] as $spl_file) {

            $fn = (string) $file_id . '_' . $a_version_id . '.xml';
            $this->logger->info($fn);
            if ($spl_file->getFilename() == $fn) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int    $file_id
     * @param string $a_version_id
     * @return bool
     */
    public function deleteVersion(int $file_id, string $a_version_id) : bool
    {
        if (!$this->versionIdExists($file_id, $a_version_id)) {
            return true;
        }
        foreach ($this->files[$file_id] as $spl_file) {

            $fn = (string) $file_id . '_' . $a_version_id . '.xml';
            if ($spl_file->getFilename() !== $fn) {
                continue;
            }
            $path = $spl_file->getPathname();
            $spl_file = null;
            $this->logger->info('Deleting file ' . $path);
            unlink($path);
        }
        $this->read();
        return true;
    }

    /**
     * @param int $file_id
     * @return string
     */
    public function getLatestVersion(int $file_id) : string
    {
        if (!$this->idExists($file_id)) {
            $this->logger->warning('Cannot find file with id: ' . $file_id);
            return '';
        }

        $unsorted_files = $this->files[$file_id];
        usort(
            $unsorted_files,
            function ($a, $b) {
                $fn_a = $a->getFilename();
                $fn_b = $b->getFilename();
                return (strcmp($fn_a, $fn_b)) < 0 ? false : true;
            }
        );
        $latest = end($unsorted_files);
        $latest_filename = $latest->getFilename();

        $matches = [];
        if (preg_match('/[0-9]+_([0-9]{8}_[0-9]{4})\.xml/', $latest->getFilename(), $matches) === 1) {
            $this->logger->info('Latest file version is: ' . $matches[1]);
            return $matches[1];
        }
        $this->logger->warning('Cannot find latest files version: ' . $latest_filename);
        return '';
    }

    /**
     * @param int    $file_id
     * @param string $a_version_id
     * @return SplFileObject|null
     */
    public function getFile(int $file_id, string $a_version_id) : ?SplFileObject
    {
        if (!$this->versionIdExists($file_id, $a_version_id)) {
            return null;
        }
        foreach ($this->files[$file_id] as $spl_file) {

            $fn = (string) $file_id . '_' . $a_version_id . '.xml';
            $this->logger->info($fn);
            if ($spl_file->getFilename() == $fn) {
                return $spl_file;
            }
        }
        return null;
    }

    public function getFileVersions(int $file_id)
    {
        if (!$this->idExists($file_id)) {
            $this->logger->warning('Cannot find file with id: ' . $file_id);
            return [];
        }
        $file_versions = [];
        foreach ($this->files[$file_id] as $spl_file)
        {
            $matches = [];
            $this->logger->info($spl_file->getFilename());
            if (preg_match('/[0-9]+_([0-9]{8}_[0-9]{4})\.xml/', $spl_file->getFilename(), $matches) === 1) {
                $file_versions[] = $matches[1];
            }
        }

        $this->logger->dump($file_versions);
        return $file_versions;
    }

    /**
     *
     */
    protected function read()
    {
        $ite = null;
        try {
            $ite = new \DirectoryIterator($this->settings->getDirectory());
        }
        catch (UnexpectedValueException $e) {
            $this->logger->warning('Configuration error: ' . $e->getMessage());
            $this->files = [];
            return;
        }
        foreach ($ite as $fileInfo) {

            if ($fileInfo->isDot()) {
                continue;
            }

            $this->logger->info('Handling file: ' . $fileInfo->getFilename());

            $filename = $fileInfo->getFilename();
            if (preg_match('/[0-9]+_[0-9]{8}_[0-9]{4}\.xml/', $filename) === 1) {

                $file_parts = explode('_', basename($filename, '.xml'));
                $this->files[$file_parts[0]][] = new SplFileObject($fileInfo->getPathname());
            }

        }
    }
}