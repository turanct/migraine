<?php

namespace Turanct\Migrations;

final class FilesystemSymfony implements Filesystem
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * FilesystemSymfony constructor.
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(\Symfony\Component\Filesystem\Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function touch(string $path): void
    {
        try {
            $this->filesystem->touch($path);
        } catch (\Exception $e) {
            // Do nothing for now
        }
    }
}
