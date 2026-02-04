<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface VideoUploader
{
    public function upload(UploadedFile $file, User $user): string;
}
