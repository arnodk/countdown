<?php
/**
 * Created by PhpStorm.
 * User: arno
 * Date: 07.08.2019
 * Time: 16:41
 */

namespace App\Components;


class Problem
{
    const iMaxNumbersToSelect           = 6;
    const iNumberOfLargeNumbersToSelect = 2;
    const iMaxDistance                  = 99999999;

    private $iTargetNumber = 0;
    private $aAllowedNumbers = array();

    public function __construct($iTarget = 0, $aNumbers=[])
    {
        if (!empty($iTarget)) $this->iTargetNumber = $iTarget;
        if (count($aNumbers) == self::iMaxNumbersToSelect) $this->aAllowedNumbers = $aNumbers;
    }

    public function distanceToTarget($iCalc)
    {
        return intval(abs($this->getTarget() - $iCalc));
    }

    public function generate() {
        $this->iTargetNumber = rand(1,999);

        $remainingLargeNumbers = [25,50,75,100];
        $remainingSmallNumbers = [1,2,3,4,5,6,7,8,9];

        // sanity checks for setup:
        if (self::iNumberOfLargeNumbersToSelect > count($remainingLargeNumbers)) throw new \Exception("Configuration error. Not enough large numbers.");
        if (self::iMaxNumbersToSelect - self::iNumberOfLargeNumbersToSelect > count($remainingSmallNumbers)) throw new \Exception("Configuration error. Not enough small numbers.");

        // generate allowed numbers, by selected a few from the large number set, and the remaining necessary numbers from the small number set.
        // only use a number once.
        for($i=0;$i<self::iMaxNumbersToSelect;$i++) {
            if ($i<self::iNumberOfLargeNumbersToSelect) {
                $key = rand(0,count($remainingLargeNumbers) -  1);
                $this->aAllowedNumbers[] = $remainingLargeNumbers[$key];
                unset($remainingLargeNumbers[$key]);
                // reindex array
                $remainingLargeNumbers = array_values($remainingLargeNumbers);
            } else {
                $key = rand(0,count($remainingSmallNumbers) -  1);
                $this->aAllowedNumbers[] = $remainingSmallNumbers[$key];
                unset($remainingSmallNumbers[$key]);
                // reindex array
                $remainingSmallNumbers = array_values($remainingSmallNumbers);
            }
        }
    }

    public function getNumbers() {
        return $this->aAllowedNumbers;
    }

    public function getTarget() {
        return intval($this->iTargetNumber);
    }

    public function toJson() {
        return json_encode([
            'target'=>$this->getTarget(),
            'numbers'=>$this->getNumbers()
        ]);
    }
}