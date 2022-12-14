<?php

namespace App\Http\Controllers;

use App\Contracts\IScoreEmployee;
use App\Http\Requests\UploadEmployeeRequest;
use Illuminate\Http\RedirectResponse;

class FileController extends Controller
{
    /**
     * @var IScoreEmployee
     */
    private $uploadEmployeeService;

    /**
     * @param IScoreEmployee $uploadEmployeeService
     */
    public function __construct(
        IScoreEmployee $uploadEmployeeService
    )
    {
        $this->uploadEmployeeService = $uploadEmployeeService;
    }

    /**
     * Function upload employees data
     *
     * @param UploadEmployeeRequest $uploadEmployeeRequest
     * @return RedirectResponse
     */
    public function uploadEmployee(UploadEmployeeRequest $uploadEmployeeRequest): RedirectResponse
    {
        return back()->with([
            'scoreData' => $this->uploadEmployeeService->getScoreData($uploadEmployeeRequest->file)
        ]);
    }
}
