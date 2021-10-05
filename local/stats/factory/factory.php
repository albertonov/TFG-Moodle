<?php

class chart_factory {
    #factory pattern
    protected $labels = array();
    protected $series = array();

    
    public function __construct($arrayseries, $arraylabels,  $nameserie)
    {
        $this->series = array (new core\chart_series( $nameserie, $arrayseries));
        $this->labels = array ($arraylabels);
    }


    function create_chart($subtype)
    {
    
    }
}
class piechart_factory extends chart_factory{
    
    function create_chart($subtype)
    {
        $chart = new \core\chart_pie();
        if ($subtype == 'doughnut'){
            $chart->set_doughnut(true);
        }

        #foreach because can have multiply series or labels
        foreach ($this->series as &$series) {
            $chart->add_series($series);
        }

        foreach ($this->labels as &$labels) {
            $chart->set_labels($labels);
        }
        return $chart;
    }

}


class linechart_factory extends chart_factory{
    
    function create_chart($subtype)
    {
        $chart = new \core\chart_line();
        if ($subtype == 'smooth'){
            $chart->set_smooth(true);
        }
        
        #foreach because can have multiply series or labels
        foreach ($this->series as &$series) {
            $chart->add_series($series);
        }

        foreach ($this->labels as &$labels) {
            $chart->set_labels($labels);
        }
        return $chart;
    }

}

class barchart_factory extends chart_factory{
    #not used, but implemented
    function create_chart($subtype)
    {
        $chart = new \core\chart_bar();
        if ($subtype == 'horizontal'){
            $chart->set_horizontal(true);
        }
        elseif ($subtype == 'stacked'){
            $chart->set_stacked(true);
        }
        
        #foreach because can have multiply series or labels
        foreach ($this->series as &$series) {
            $chart->add_series($series);
        }

        foreach ($this->labels as &$labels) {
            $chart->set_labels($labels);
        }
        return $chart;
    }

}

/*
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
*/