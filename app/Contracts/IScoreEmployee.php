<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface IScoreEmployee
{
    public function getScoreData(UploadedFile $file): array;
}
