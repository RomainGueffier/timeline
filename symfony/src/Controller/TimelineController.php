<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TimelineController extends AbstractController
{
    /**
     * @Route("/timeline", name="timeline")
     */
    public function index(Request $request)
    {
        // Valeurs par défaut
        $start = -4100; // année de début de la frise
        $end = 2000; // année de fin de la frise
        $unit = 100; // en années par 50px
        $range = 2; // équivalent unit dans le form (2=>100ans,1=>10ans,0=>1an)

        // GET modifications des valeurs
        $range = $request->query->get('range') != null ? $request->query->get('range') : $range;

        // !!! todo vérification date de fin pas inférieure date de début
        $start = $request->query->get('start') != null ? $request->query->get('start') : $start;
        $end = $request->query->get('end') != null ? $request->query->get('end') : $end;

        if ($range == 2) {
            $unit = 100;
        } elseif ($range == 1) {
            $unit = 10;
        } else {
            $unit = 1;
        }
        $ratio = $unit / 50;

        $date = $start;

        $timeline = '<div class="timeline-period"></div>';
        while ($date < $end) {
            $date += $unit;
            if ($date == 0) {
                $timeline .= '<div class="timeline-period timeline-period-zero"><label>0</label></div>';
            } elseif (is_integer(abs($date) / 1000)) {
                $timeline .= '<div class="timeline-period timeline-period-strong"><label>' . $date . '</label></div>';
            } elseif (is_integer(abs($date - 500) / 1000)) {
                $timeline .= '<div class="timeline-period timeline-period-light"><label>' . $date . '</label></div>';
            } elseif (is_integer(abs($date) / 100)) {
                $timeline .= '<div class="timeline-period timeline-period-extralight"><label>' . $date . '</label></div>';
            } else {
                $timeline .= '<div class="timeline-period"><label>' . $date . '</label></div>';
            }
        }

        return $this->render('timeline/index.html.twig', [
            'ratio' => $ratio,
            'start' => $start,
            'end' => $end,
            'unit' => $unit,
            'timeline' => $timeline,
            'range' => $range
        ]);
    }
}
