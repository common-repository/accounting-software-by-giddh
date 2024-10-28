<?php
class GiddhPagination {
	public $perpage;
	
	function __construct() {
		$this->perpage = GIDDH_PAGINATION_LIMIT;
	}
	
	function getPageLinks($count) {
        if(!$count) {
            return false;
        }

        $output = '';
        
        if(!isset($_POST["page"])) {
            $_POST["page"] = 1;
        }

        $pages  = ceil($count / $this->perpage);

		if($pages > 1) {
			if($_POST["page"] != 1) {
                $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="1">First</a></li>';
                $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="1"><span class="fa fa-caret-left" aria-hidden="true"></span></a></li>';
            }
			
			if(($_POST["page"] - 3) > 0) {
				if($_POST["page"] == 1) {
					$output = $output . '<li class="page-item"><a class="page-link active" href="javascript:;" page="1">1</a></li>';
                } else {
                    $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="1">1</a></li>';
                }
            }
            
			if(($_POST["page"] - 3) > 1) {
				$output = $output . '<li class="dot">...</li>';
			}
			
			for($i = ($_POST["page"] - 2); $i <= ($_POST["page"] + 2); $i++)	{
				if($i < 1) { 
                    continue;
                }
				if($i > $pages) {
                    break;
                }
				if($_POST["page"] == $i) {
					$output = $output . '<li class="page-item"><a class="page-link active" href="javascript:;" page="'.$i.'">'.$i.'</a></li>';
                } else {
                    $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="'.$i.'">'.$i.'</a></li>';
                }
			}
			
			if(($pages - ($_POST["page"] + 2)) > 1) {
				$output = $output . '<li class="dot">...</li>';
            }
            
			if(($pages - ($_POST["page"] + 2)) > 0) {
				if($_POST["page"] == $pages) {
					$output = $output . '<li class="page-item"><a class="page-link active" href="javascript:;" page="'.$pages.'">'.$pages.'</a></li>';
                } else {				
                    $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="'.$pages.'">'.$pages.'</a></li>';
                }
			}
			
			if($_POST["page"] < $pages) {
                $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="'.(sanitize_text_field($_POST["page"]) + 1).'"><span class="fa fa-caret-right" aria-hidden="true"></span></a></li>';
                $output = $output . '<li class="page-item"><a class="page-link" href="javascript:;" page="'.$pages.'">Last</a></li>';
            }
		}
		return $output;
    }
}
?>