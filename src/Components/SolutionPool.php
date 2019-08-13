<?php
/**
 * Created by PhpStorm.
 * User: arno
 * Date: 07.08.2019
 * Time: 17:56
 */

namespace App\Components;


class SolutionPool
{
    const iPoolSize = 1000;
    const iCull     = 200;

    private $problem = NULL;
    private $aSolutions = [];

    public function __construct(Problem $problem)
    {
        $this->problem = $problem;
    }

    /**
     * create a random solution pool:
     */
    public function generate() {
        $this->aSolutions = [];
        for($i=0;$i<self::iPoolSize;$i++) {
            $solution = new Solution($this->problem);
            $solution->generate();
            $this->addToPool($solution);
        }
        $this->sortMe();
    }

    public function sortMe() {
        // sort by key
        ksort($this->aSolutions);
    }

    private function addToPool($solution) {
        // add to solutions array with its performance as a key:
        $performance = $this->problem->distanceToTarget($solution->calc());
        if (!isset($this->aSolutions[$performance])) {
            $this->aSolutions[$performance] = [];
        }
        $this->aSolutions[$performance][] = $solution;
    }

    public function evolve() {
        // cull
        $this->cull();
        $this->mutate();
    }

    private function cull() {
        // assume array is sorted on performance descending
        $iMax = $this->iNumberOfSolutions() - self::iCull;
        $oldSolutions=$this->aSolutions;
        $this->aSolutions=[];
        $i = 0;
        if ($iMax > 0) do {
            $iKey = array_key_first($oldSolutions);
            $solutions = $oldSolutions[$iKey];
            foreach($solutions as $solution) {
                $i++;
                if ($i<=$iMax) $this->addToPool($solution);
            }
            unset($oldSolutions[$iKey]);
        } while($i<=$iMax);

    }

    public function iNumberOfSolutions() {
        $i=0;
        foreach($this->aSolutions as $solutions) $i=$i+count($solutions);
        return $i;
    }

    private function getRandomSolution() {
        $solutions = $this->aSolutions[array_rand($this->aSolutions)];
        if (count($solutions)==1) return $solutions[0];
        return $solutions[array_rand($solutions)];
    }

    private function mutate() {
        $iCulled = self::iPoolSize - $this->iNumberOfSolutions();
        for($i=0;$i<$iCulled;$i++) {
            $solution = $this->getRandomSolution();
            $solutionNew = new Solution($this->problem);
            $operationNew = clone $solution->getOperation();

            $solutionNew->setProblem($this->problem);
            $solutionNew->setOperation($operationNew);
            $solutionNew->mutate();

            $this->addToPool($solutionNew);
        }

        // sort by key
        $this->sortMe();
    }

    public function getSolutions() {
        return $this->aSolutions;
    }

    public function getBestSolution()
    {
        // assume array is sorted on performance descending
        reset($this->aSolutions);
        $iKey = array_key_first($this->aSolutions);
        return $this->aSolutions[$iKey][0];
    }
}