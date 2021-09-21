<?php
class factory {
    #factory pattern
    function create_doughnut_chart($arrayofseries, $arrayoflabels)
    {
    
        $chart = new \core\chart_pie();
        $chart->set_doughnut(true);
        
        foreach ($arrayofseries as &$series) {
            $chart->add_series($series);
        }

        foreach ($arrayoflabels as &$labels) {
            $chart->set_labels($labels);
        }
    
        return $chart;
    }
    
    function create_line_chart($arrayofseries, $arrayoflabels)
    {
    
        $chart = new \core\chart_line();
                
        foreach ($arrayofseries as &$series) {
            $chart->add_series($series);
        }

        foreach ($arrayoflabels as &$labels) {
            $chart->set_labels($labels);
        }
    
        return $chart;
    }
    

}

