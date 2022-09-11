<?php

namespace App\Rules\Uploads;

use App\Imports\EmployeesImport;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class CheckEmployeeImportFileRule implements Rule
{
    /**
     * @var string[]
     */
    const ALLOW_HEADER_COLUMNS = [
        'name', 'email', 'division', 'age', 'timezone'
    ];

    /**
     * @var string
     */
    private string $message = 'Something went wrong with uploaded file';

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->checkHeading()) {

            $employeesData = Excel::toArray(new EmployeesImport, request()->file('file'));

            return $this->checkData($employeesData[0]);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->getMessage();
    }

    /**
     * @return bool
     */
    private function checkHeading(): bool
    {
        try {
            $headings = (new HeadingRowImport)->toArray(request()->file('file'));
            $headings = $headings[0][0];

            if (count($headings) !== 5) {

                $this->setMessage('Header Row is Invalid');

                return false;
            }

            if ($headings !== self::ALLOW_HEADER_COLUMNS) {
                $this->setMessage('Header Columns is Invalid');

                return false;
            }

        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function checkData(array $data): bool
    {
        $validator = Validator::make($data, [
            '*.name' => 'required|string|max:256',
            '*.email' => 'required|email|string|max:256',
            '*.division' => 'required|string|max:256',
            '*.age' => 'required|int|max:100',
            '*.timezone' => 'required|int|min:-12|max:12',
        ]);

        if ($validator->fails()) {

            $this->setMessage('Upload data is invalid');

            return false;
        }

        return true;
    }

    /**
     * @param string $message
     * @return void
     */
    private function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    private function getMessage(): string
    {
        return $this->message;
    }
}
