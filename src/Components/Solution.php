<?php
/**
 * Created by PhpStorm.
 * User: arno
 * Date: 07.08.2019
 * Time: 15:29
 */

namespace App\Components;


class Solution
{
    private $problem         = NULL;
    /**
     * @var operation
     */
    private $operation       = NULL;

    public function __construct(Problem $problem) {
        $this->problem = $problem;
    }

    public function setOperation(Operation $operation) {
        $this->operation = $operation;
    }

    /**
     * generate a random, but valid, solution:
     */
    public function generate() {
        $this->operation = new Operation($this->problem);
        $this->operation->generate();
    }

    public function setProblem(Problem $problem) {
        $this->problem = $problem;
    }

    public function mutate() {
        $mutationType = rand(1,2);

        if ($mutationType === 1) {
            // add an operation:
            $myUsedNumbers = $this->getMyNumbers();
            $aUnusedNumbers = array_diff($this->problem->getNumbers(), $myUsedNumbers);
            if (count($aUnusedNumbers) >= 2) {
                do {
                    do {
                        $operation = new Operation($this->problem);
                        $operation->generate($aUnusedNumbers);
                        $operation->setOperationType(rand(1, 4));
                    } while (!$operation->valid());
                    if (rand(0, 1) == 0) {
                        $this->operation->setLeft($operation);
                    } else {
                        $this->operation->setRight($operation);
                    }
                } while (!$this->operation->valid());
            }
        } elseif ($mutationType === 2) {
            // change operation type:
            do {
                $this->operation->setOperationType(rand(1,4));
            } while(!$this->operation->valid());
        }

        // swap numbers between randomly selected operations

        // remove an operation
    }

    public function calc() {
        return $this->operation->calc();
    }

    private function getMyNumbers() {
        return $this->operation->getAllLeaveNumbers();
    }

    public function getOperation() {
        return $this->operation;
    }

    public function display() {
        return $this->operation->display();
    }
}