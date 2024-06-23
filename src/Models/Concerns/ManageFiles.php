<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait ManageFiles
{
    protected static function bootManageFiles(): void
    {
        static::updated(function (self $model) {
            $oldFiles = $model->getOriginal('files') ?? [];
            $deletedFiles = array_diff_key($oldFiles, $model->files ?? []);

            foreach ($deletedFiles as $file => $name) {
                $model->deleteFile($file);
            }
        });

        static::deleted(function (self $model) {
            foreach ($model->files ?? [] as $file => $name) {
                $model->deleteFile($file);
            }
        });
    }

    public function storeFile(UploadedFile $file, bool $save = false): string
    {
        $fileName = $file->getClientOriginalName();
        $path = basename($this->filesDisk()->putFile($this->filesBasePath(), $file));

        $this->files = array_merge($this->files ?? [], [$path => $fileName]);

        if ($save) {
            $this->save();
        }

        return $path;
    }

    public function downloadFile(string $file, string $name)
    {
        return $this->filesDisk()->download($this->filesPath($file), $name);
    }

    public function deleteFile(string $file): bool
    {
        if (! $this->filesDisk()->delete($this->filesPath($file))) {
            return false;
        }

        $this->files = array_diff_key($this->files ?? [], [$file => '']);

        return true;
    }

    protected function filesPath(string $file): string
    {
        return $this->filesBasePath().'/'.$file;
    }

    protected function filesBasePath(): string
    {
        return str_replace('_', '/', $this->getTable());
    }

    protected function filesDisk(): Filesystem
    {
        return Storage::disk();
    }
}
