<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

//    $data = ['A', 'B', 'C', 'D'];
//    $data = ['A', 'B', 'C', 'D', 'E', 'F'];
    $data = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    $dataCount = count($data);
    $inGroupItemsCount = $dataCount - 3;
    $itemCountInGroup = ($dataCount - 2) / 2 * $inGroupItemsCount;


    $allCombinations = [];
    foreach ($data as $key => $item) {
        for ($i = $key + 1; $i < count($data); $i++) {
            $allCombinations[] = $item . '/' . $data[$i];
        }
    }

    $groupedCombination = [];
    $alsoCheckedGroupIndex = [];
    $addedItemInGroupCount = 0;

    // @todo check logic of count
    $checkCount = $inGroupItemsCount * ($dataCount - 1);

    $result = [];
    for ($i = 0; $i < $checkCount; $i++) {

        foreach ($allCombinations as $key => $combination) {

            if (in_array($key, $alsoCheckedGroupIndex)) {
                continue;
            }

            if (empty($result[$i])) {

                $result[$i][$combination] = $combination;

                if ($i == 0 || $i % $inGroupItemsCount == 0) {
                    $groupedCombination[$combination] = [];
                }
            }

            if (count($result[$i]) <= $dataCount / 2) {

                $firstCompare = array_key_first($result[$i]);

                // @todo for now fast check
                $groupedAllData = collect($result[$i])->implode(',');
                $explodedCombination = explode('/', $combination);

                $addCombination = true;
                if (str_contains($groupedAllData, $explodedCombination[0]) || str_contains($groupedAllData, $explodedCombination[1])) {
                    $addCombination = false;
                }

                if ($addCombination && in_array($combination, $groupedCombination[$firstCompare])) {
                    $addCombination = false;
                }

                if ($addCombination) {

                    $result[$i][$combination] = $combination;

                    if ($key != 0) {

                        $groupedCombination[$firstCompare][] = $combination;

                        $addedItemInGroupCount++;

                        // ----------

                        if ($addedItemInGroupCount % $itemCountInGroup === 0) {
                            $addedItemInGroupCount = 0;

                            $alsoCheckedGroupIndex[] = array_search($firstCompare, $allCombinations);
                        }
                    }
                }
            }
        }
    }

    $result = array_chunk($result, $inGroupItemsCount);

    dd($result);

    return view('home');
});

Route::post('upload-employees', [FileController::class, 'uploadEmployee'])->name('upload.employees');
