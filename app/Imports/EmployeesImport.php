<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToArray, WithHeadingRow
{
    /**
    * @param array $data
    */
    public function array(array $data)
    {

    }

}
