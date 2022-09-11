<?php

namespace App\Service;

use App\Contracts\IScoreEmployee;
use App\Imports\EmployeesImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ScoreEmployeeService implements IScoreEmployee
{
    /**
     * @var int
     */
    const PERCENT_BY_DIVISION = 30;

    /**
     * @var int
     */
    const PERCENT_BY_AGE = 30;

    /**
     * @var int
     */
    const PERCENT_BY_AGE_DIFFERENCE = 5;

    /**
     * @var int
     */
    const PERCENT_BY_TIMEZONE = 40;

    /**
     * Function to get score data of uploaded file
     *
     * @param UploadedFile $file
     * @return array
     */
    public function getScoreData(UploadedFile $file): array
    {
        $employeesData = Excel::toArray(new EmployeesImport, $file)[0];
        $employeesAllScores = $this->getEmployeesAllScores($employeesData);

        $checkedEmployeeEmail = null;
        $result = [];
        foreach ($employeesAllScores as $employee) {

            $employeeEmail = $employee['first_employee_email'];

            if ($checkedEmployeeEmail === $employeeEmail) {
                continue;
            }

            $firstEmployeeMaxScore = collect($employeesAllScores)
                ->where('first_employee_email', $employeeEmail)
                ->sortByDesc('score')
                ->first();

            $secondEmployeeMaxScore = collect($result)
                ->where('second_employee_email', $employeeEmail)
                ->sortByDesc('score')
                ->first();

            if (!is_null($secondEmployeeMaxScore)) {

                if ($employee['score'] > $secondEmployeeMaxScore['score']) {
                    $result[] = $firstEmployeeMaxScore;
                }

            } else {
                $result[] = $firstEmployeeMaxScore;
            }

            $checkedEmployeeEmail = $employeeEmail;
        }

        $result = collect($result)->sortByDesc('score');
        $average = number_format($result->sum('score') / count($result), 2);
        $employeesCount = count($employeesData);

        return [
            'result' => $result,
            'averageText' => "In the case of {$employeesCount} employees the highest average match score is {$average}%"
        ];
    }

    /**
     * Function to sum score 2 employees by type
     *
     * @param array $firstEmployee
     * @param array $secondEmployee
     * @return int
     */
    private function sumScore(array $firstEmployee, array $secondEmployee): int
    {
        $score = 0;
        if ($firstEmployee['division'] === $secondEmployee['division']) {
            $score += self::PERCENT_BY_DIVISION;
        }

        if (abs($firstEmployee['age'] - $secondEmployee['age']) <= self::PERCENT_BY_AGE_DIFFERENCE) {
            $score += self::PERCENT_BY_AGE;
        }

        if ($firstEmployee['timezone'] === $secondEmployee['timezone']) {
            $score += self::PERCENT_BY_TIMEZONE;
        }

        return $score;
    }

    /**
     * Function to get all employees score data
     *
     * @param array $employeesData
     * @return array
     */
    private function getEmployeesAllScores(array $employeesData): array
    {
        $employeesAllScores = [];
        foreach ($employeesData as $key => $firstEmployee) {

            $firstEmployeeEmail = $firstEmployee['email'];

            for ($i = $key + 1; $i < count($employeesData); $i++) {

                $secondEmployee = $employeesData[$i];
                $score = $this->sumScore($firstEmployee, $secondEmployee);

                $employeesAllScores[] = [
                    'first_employee_email' => $firstEmployeeEmail,
                    'second_employee_email' => $secondEmployee['email'],
                    'text' => $firstEmployee['name'] . ' will be matched with ' . $secondEmployee['name'] . ' ' . $score . '%',
                    'score' => $score
                ];
            }
        }

        return $employeesAllScores;
    }
}
