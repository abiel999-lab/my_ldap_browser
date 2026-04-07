<?php

namespace App\Helpers\Admin;

use Illuminate\Support\Facades\Storage;
use Throwable;

class S3Helper
{
    public static function store($request, $field, $driver = 's3')
    {
        $folder = config('filesystems.folder');

        try {
            $image = $request->file($field);
            $imageFileName = date('Ymdhis_') . $image->getClientOriginalName();
            $s3 = Storage::disk($driver);
            $filePath = $folder . '/' . $imageFileName;
            $s3->put($filePath, file_get_contents($image));

            return $imageFileName;
        } catch (Throwable $th) {
            return $th->getMessage();
        }
    }

    public static function show($filename, $driver = 's3')
    {
        try {
            $folder = config('filesystems.folder');
            $s3 = Storage::disk($driver);
            $filePath = $folder . '/' . $filename;
            $data = $s3->get($filePath);

        } catch (Throwable $th) {
            return null;
        }

        return $data;
    }

    public static function check($filename, $driver = 's3')
    {
        try {
            $folder = config('filesystems.folder');
            $s3 = Storage::disk($driver);
            $filePath = $folder . '/' . $filename;
            $content = $s3->exists($filePath);

            return $content;
        } catch (Throwable $th) {
            return false;
        }
    }

    public static function destroy($filename, $driver = 's3')
    {
        try {
            $folder = config('filesystems.folder');
            $s3 = Storage::disk($driver);
            $filePath = $folder . '/' . $filename;
            $content = $s3->delete($filePath);
            $data = [
                'status' => '1',
                'msg' => 'Informasi berhasil dihapus',
            ];
        } catch (Throwable $th) {
            $data = [
                'status' => '0',
                'msg' => 'Informasi gagal dihapus',
            ];
        }

        return $data;
    }

    public static function destroyDir($driver = 's3')
    {
        try {
            $folder = config('filesystems.folder');
            $s3 = Storage::disk($driver);
            $s3->deleteDirectory($folder);
            $data = [
                'status' => '1',
                'msg' => 'Folder berhasil dihapus',
            ];
        } catch (Throwable $th) {
            return $th;
            $data = [
                'status' => '0',
                'msg' => 'Folder gagal dihapus',
            ];
        }

        return $data;
    }
}
