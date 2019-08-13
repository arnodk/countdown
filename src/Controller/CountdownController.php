<?php
/**
 * Created by PhpStorm.
 * User: arno
 * Date: 07.08.2019
 * Time: 21:30
 */

namespace App\Controller;


use App\Components\Countdown;
use App\Components\Problem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CountdownController extends AbstractController
{
    public function run() {
        /*
        $countdown = new Countdown();
        $countdown->run();
        */

        return $this->render('countdown/main.html.twig');
    }

    public function generateProblem() {
        $countdown = new Countdown();
        return new Response($countdown->generateProblem());
    }

    public function solveProblem($target,$numbers) {
        $aAnswer=[];
        $aNumbers = explode(",",$numbers);
        // sanitize
        $iTarget = intval($target);
        foreach($aNumbers as $i=>$number) $aNumber[$i] = intval($number);
        if (!empty($iTarget) && count($aNumbers) == Problem::iMaxNumbersToSelect) {
            $problem = new Problem($iTarget, $aNumbers);
            $countdown = new Countdown();
            $aAnswer = $countdown->solve($problem);
        }

        return new Response(json_encode($aAnswer));
    }
}