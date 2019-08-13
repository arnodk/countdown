<?php
/**
 * Created by PhpStorm.
 * User: arno
 * Date: 07.08.2019
 * Time: 14:40
 */

namespace App\Components;


class Operation
{
    const ADD       = 1;
    const SUBTRACT  = 2;
    const MULTIPLY  = 3;
    const DIVIDE    = 4;
    const NOOP      = 5; // used a filler, if static operations array sizes are used.

    private $iOperationType   = 0;
    private $opLeft           = 0;
    private $opRight          = 0;
    private $problem          = null;


    public function __construct(Problem $problem)
    {
        $this->problem = $problem;
    }

    private function generateTerm($aWorkset,$iDepth) {
        $aValues = [];

        // do we go into recursion?
        if ((rand(1, 3)) == 1 && count($aWorkset) >= 2) {
            // yes, create a new operation:
            $operation = new Operation($this->problem);
            $aWorkset = $operation->generate($aWorkset, $iDepth++);
            $aValues=[
                'value'=>$operation,
                'workset'=>$aWorkset
            ];
        } else {
            // no, pick a number:
            $iKey = array_rand($aWorkset);
            $iNumber = $aWorkset[$iKey];
            unset($aWorkset[$iKey]);
            $aValues=[
                'value'=>$iNumber,
                'workset'=>$aWorkset
            ];
        }

        return $aValues;
    }

    public function generate($aAllowedNumbers = false,$iDepth = 0) {
        if (!$aAllowedNumbers) $aAllowedNumbers = $this->problem->getNumbers();

        // we do not have enough numbers to play with:
        if (count($aAllowedNumbers)<2) return false;

        do {
            $bRetry = false;

            $aWorkset = $aAllowedNumbers;

            $this->iOperationType = rand(1, 4);
            $a = $this->generateTerm($aWorkset,$iDepth);
            $aWorkset = $a['workset'];
            $this->opLeft = $a['value'];

            if (count($aWorkset)==0) {
                // nothing left for right term, try again
                $bRetry=true;
                continue;
            }

            $a = $this->generateTerm($aWorkset,$iDepth);
            $aWorkset = $a['workset'];
            $this->opRight = $a['value'];

        } while(!$this->valid() || $bRetry);

        return $aWorkset;
    }

    public function setLeft($operation) {
        if ($operation instanceof Operation && $operation != NULL) {
            $this->opLeft = $operation;
        } else {
            $this->opLeft = intval($operation);
        }
    }

    public function setRight($operation) {
        if ($operation instanceof Operation && $operation != NULL) {
            $this->opRight = $operation;
        } else {
            $this->opRight = intval($operation);
        }
    }

    public function setOperationType($i) {
        $this->iOperationType=intval($i);
    }

    public function getOperationType() {
        return $this->iOperationType;
    }

    // mainly checks divisions:
    public function valid() {
        // debug:
        // return true;

        // can't have equal number values on both sides:
        if (
                $this->iOperationType!=self::NOOP &&
                !($this->opLeft instanceof Operation) &&
                !($this->opRight instanceof Operation) &&
                $this->opLeft === $this->opRight
        ) return false;

        // division by zero:
        if ($this->iOperationType===self::DIVIDE && $this->calcOperationValue(1) == 0) {
            return false;
        }

        // no fractions are allowed:
        if ($this->iOperationType===self::DIVIDE && $this->calcOperationValue(0) % $this->calcOperationValue(1) != 0) {
            return false;
        }

        return true;
    }

    private function calcOperationValue($which, $bDisplay = false) {

        if ($which===0) {
            $opValue = $this->opLeft;
        } else {
            $opValue = $this->opRight;
        }
        if ($opValue instanceof Operation && $opValue != NULL) {
            return $opValue->calc($bDisplay);
        }

        if ($bDisplay) {
            return ["iResult"=>intval($opValue),"sResult"=>strval($opValue)];
        }

        return $opValue;
    }

    public function calc($bDisplay=false) {
        if ($this->iOperationType<1 || $this->iOperationType>5) {
            throw new \Exception("Invalid operation");
        }

        if ($bDisplay) {
            $aLeft = $this->calcOperationValue(0,true);
            $aRight = $this->calcOperationValue(1, true);
            $iLeft = $aLeft["iResult"];
            $sLeft = $aLeft["sResult"];
            $iRight = $aRight["iResult"];
            $sRight = $aRight["sResult"];
            // if the result includes a reference to an operation,
            // add parenthesises
            if (strpos($sLeft, " ")!==false) $sLeft = "(".$sLeft.")";
            if (strpos($sRight," ")!==false) $sRight = "(".$sRight.")";

        } else {
            $iLeft = $this->calcOperationValue(0);
            $iRight = $this->calcOperationValue(1);
        }


        $sResult = "";
        $iResult = 0;
        switch($this->iOperationType) {
            case 1:
                $iResult = $iLeft + $iRight;
                if ($bDisplay) $sResult = $sLeft  . " + " . $sRight;
                break;
            case 2:
                $iResult = $iLeft - $iRight;
                if ($bDisplay) $sResult = $sLeft . " - " . $sRight;
                break;
            case 3:
                $iResult = $iLeft * $iRight;
                if ($bDisplay) $sResult = $sLeft . " * " . $sRight;
                break;
            case 4:
                $iResult = $iLeft / $iRight;
                if ($bDisplay) $sResult = $sLeft . " / " . $sRight;
                break;
            case 5:
                if ($bDisplay) $sResult = "NO-OP";
                $iResult = 0;
        }

        if ($bDisplay) {
            return ["iResult"=>$iResult,"sResult"=>$sResult];
        } else {
            return $iResult;
        }

    }

    public function display()
    {
        $aResult = $this->calc(true);
        return $aResult["sResult"];
    }

    public function isLeftANumber()
    {
        return !($this->opLeft instanceof Operation);
    }

    public function isRightANumber()
    {
        return !($this->opRight instanceof Operation);
    }

    public function getLeft() {
        return $this->opLeft;
    }

    public function getRight() {
        return $this->opRight;
    }

    // traverse operation tree, and get all the scalar ints:
    public function getAllLeaveNumbers() {
        $aResult = [];
        if ($this->opLeft instanceof Operation && $this->opLeft != NULL) {
            $aResult = array_merge($aResult, $this->opLeft->getAllLeaveNumbers());
        } else {
            if (!in_array($this->opLeft,$aResult)) $aResult[] = $this->opLeft;
        }
        if ($this->opRight instanceof Operation && $this->opRight != NULL) {
            $aResult = array_merge($aResult, $this->opRight->getAllLeaveNumbers());
        } else {
            if (!in_array($this->opRight,$aResult)) $aResult[] = $this->opRight;
        }
        return $aResult;
    }
}