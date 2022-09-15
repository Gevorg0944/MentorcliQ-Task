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

        $higherScoreGroup = $this->getHigherScoreGroup($employeesData);
        $average = collect($higherScoreGroup)->avg('score');
        $employeesCount = count($employeesData);

        return [
            'result' => $higherScoreGroup,
            'averageText' => "In the case of {$employeesCount} employees the highest average match score is {$average}%"
        ];
    }

    /**
     * Function to get higher score group of employees
     *
     * @param array $employeesData
     * @return array
     */
    private function getHigherScoreGroup(array $employeesData): array
    {
        $groupedCombinations = $this->getUniqueGroupedCombinations($employeesData);

        $maxTotalScore = 0;
        $maxTotalScoreIndex = 0;
        foreach ($groupedCombinations as $key => $scoreData) {

            $totalScore = collect($scoreData)->sum('score');

            if ($totalScore > $maxTotalScore) {
                $maxTotalScore = $totalScore;

                $maxTotalScoreIndex = $key;
            }
        }

        return $groupedCombinations[$maxTotalScoreIndex];
    }

    /**
     * Function to get unique combination data of employees
     *
     * @param array $employeesData
     * @return array
     */
    private function getUniqueGroupedCombinations(array $employeesData): array
    {
        $employeesCombinationScores = $this->getEmployeesAllScores($employeesData);

        $dataCount = count($employeesData);
        $inGroupItemsCount = $dataCount - 3;
        $needItemCountInGroup = ($dataCount - 2) / 2 * $inGroupItemsCount;
        $checkCount = $inGroupItemsCount * ($dataCount - 1);

        $result = $alsoCheckedMainCombination = $groupedCombination = [];
        $addedItemCountInGroup = 0;

        for ($i = 0; $i < $checkCount; $i++) {

            foreach ($employeesCombinationScores as $scoreData) {

                $combination = $scoreData['first_employee_email'] . '/' . $scoreData['second_employee_email'];

                if (in_array($combination, $alsoCheckedMainCombination)) {
                    continue;
                }

                if (empty($result[$i])) {

                    $result[$i][$combination] = $scoreData;

                    continue;
                }

                if (count($result[$i]) <= $dataCount / 2) {

                    $firstCompare = reset($result[$i]);
                    $firstCompareCombination = $firstCompare['first_employee_email'] . '/' . $firstCompare['second_employee_email'];

                    if (!$this->checkInGroupExistEmployees($result[$i], $scoreData) && !in_array($combination, $groupedCombination[$firstCompareCombination] ?? [])) {

                        $result[$i][$combination] = $scoreData;

                        // ----------

                        if (empty($groupedCombination[$firstCompareCombination])) {
                            $groupedCombination[$firstCompareCombination] = [];
                        }

                        $groupedCombination[$firstCompareCombination][] = $combination;

                        $addedItemCountInGroup++;

                        // ----------

                        if ($addedItemCountInGroup === $needItemCountInGroup) {

                            $addedItemCountInGroup = 0;

                            $alsoCheckedMainCombination[] = $firstCompareCombination;
                        }
                    }
                }
            }
        }

        return $result;
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

    /**
     * Function to check employees exists in current group
     *
     * @param array $groupEmployees
     * @param array $checkEmployee
     * @return bool
     */
    private function checkInGroupExistEmployees(array $groupEmployees, array $checkEmployee): bool
    {
        $allEmails = [];
        foreach ($groupEmployees as $employee) {
            array_push($allEmails, $employee['first_employee_email'], $employee['second_employee_email']);
        }

        if (in_array($checkEmployee['first_employee_email'], $allEmails) || in_array($checkEmployee['second_employee_email'], $allEmails)) {
            return true;
        }

        return false;
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
}
