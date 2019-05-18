<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use League\Flysystem\Config;

class FileController extends Controller
{
    public function upload()
    {

        $this->validate(\request(), [
            'file' => 'required|file',
            'key'  => [
                'required_if:generate_key,0',
            ],
        ]);

        $file = request()->file('file');

        $key = "";

        if (request('generate_key') === '1') {
            $key = Str::random(32);
        } else {
            $key = request('key');

            if (\strlen($key) !== 32) {
                return redirect()->back()
                    ->withErrors([
                        'key' => [
                            'Key must be 32 characters exactly.',
                        ],
                    ])
                    ->withInput();
            }

        }

        $encrypter = new Encrypter(
            $key,
            config('app.cipher')
        );
        $contents  = $file->get();

        $encryptedContent = $encrypter->encrypt($contents);

        $name = Str::random(16) . ".dat";

        Storage::put('public/encrypted_files/' . $name, $encryptedContent);

        $fileModel                     = new File;
        $fileModel->size               = $file->getSize();
        $fileModel->original_name      = $file->getClientOriginalName();
        $fileModel->original_extension = $file->getClientOriginalExtension();
        $fileModel->name               = $name;
        $fileModel->storage_location   = 'public/encrypted_files/' . $name;
        $fileModel->access_location    = '/storage/encrypted_files/' . $name;
        $fileModel->save();

        $redirect = redirect()->route('home')
            ->with('status', 'Successfully uploaded file');

        if (request('generate_key') === '1') {
            $redirect = $redirect->with('generated_key', $key);
        }

        return $redirect;
    }

    public function download(File $file)
    {
        $key = request('encryption_key');

        if (\strlen($key) !== 32) {
            return redirect()->back()
                ->withErrors([
                    'encryption_key' => [
                        'Key must be 32 characters exactly.',
                    ],
                ])
                ->withInput();
        }
        $encrypter   = new Encrypter(
            $key,
            config('app.cipher')
        );
        $stored_file = Storage::get($file->storage_location);

        try {
            $decrypted = $encrypter->decrypt($stored_file);
        } catch (DecryptException $exception) {
            return redirect()->back()
                ->with('modal', "download_file{$file->id}")
                ->withErrors([
                    'encryption_key' => [
                        'Encryption key must be invalid',
                    ],
                ])
                ->withInput();
        }

        return response()->streamDownload(function () use ($decrypted) {
            echo $decrypted;
        }, "{$file->original_name}");
    }
}
