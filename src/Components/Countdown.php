<?php
/**
 * Created by PhpStorm.
 * User: arno
 * Date: 07.08.2019
 * Time: 17:48
 */

namespace App\Components;


class Countdown
{
    const iRunTime = 5; // max run time in seconds.

    /**
     * @var Problem
     */
    private $problem = NULL;

    public function generateProblem() {
        $this->problem = new Problem();
        $this->problem->generate();
        return $this->problem->toJson();
    }

    public function solve(Problem $problem=NULL)
    {
        if (!is_null($problem)) $this->problem = $problem;

        $solutionPool = new SolutionPool($this->problem);
        // generate a pool of solutions
        $solutionPool->generate();

        // as with the human game version, only 30s is allowed to solve the problem.
        // so, start the timer... now!
        $iStart = time();
        $solutionBest = false;
        $iCurrentDistanceToTarget = Problem::iMaxDistance;

        // evolve until time is up or a complete solution (i.e. a solution that exactly matches the target of the problem) is found
        do {

            $solutionLocalBest = $solutionPool->getBestSolution();
            $solutionLocalBestDistanceToTarget = $this->problem->distanceToTarget($solutionLocalBest->calc());

            if (!$solutionBest || $solutionLocalBestDistanceToTarget < $iCurrentDistanceToTarget ) {
                $solutionBest = $solutionLocalBest;
                $iCurrentDistanceToTarget = $solutionLocalBestDistanceToTarget;
            }

            // echo "Target:" . $problem->getTarget() . "<br>";
            // echo "Local distance to target: " . $solutionLocalBestDistanceToTarget . "<br>";
            // echo "Best found distance to target: " . $iCurrentDistanceToTarget . "<br>";
            // echo "Calculation: " . $solutionBest->getBestOperation()->display();

            $solutionPool->evolve();

        } while (time() - $iStart <= self::iRunTime);

        // time is up, display best performing solution.
        /*
        echo "Target:" . $this->problem->getTarget() . "<br>";
        echo "Final result: " . $solutionBest->calc() . "<br>";
        echo "Final calculation: " . $solutionBest->display();
        */
        $iResult = intval($solutionBest->calc());
        return [
            "target"=>$this->problem->getTarget(),
            "result"=>$iResult,
            "score"=>$this->problem->distanceToTarget($iResult),
            "proof"=>$solutionBest->display(),

        ];
    }
}