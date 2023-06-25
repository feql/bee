<?php

function bee_hive_run_get_depence_flat_tree($bee){
    $flat_tree = [];
    $tables = $bee["BEE_HIVE_STRUCTURE"]["combs"];
    foreach ($tables as $table_name => $table_column_def) {
        if(tools_startsWith($table_name,"_")){
            continue;
        }
        $flat_tree[$table_name] = [];
        $fk = $table_name . "_id";
        foreach ($tables as $table_name_search => $table_column_search) {
            if(tools_startsWith($table_name,"_")){
                continue;
            }
            if(array_key_exists($fk,$table_column_search) == true) {
                array_push($flat_tree[$table_name], $table_name_search);
            }
        }
    }
    return $flat_tree;
}

function bee_hive_run_process_dependency_report(
    $yeild,
    $seeds,
    $flat_tree,
    $bee,
    $finished = [],
    $nn = 0
){
    $use_limits = false;
    $scan_limit = 0;
    $res = [null,[]];
    if(array_key_exists("limit",$yeild) == true){
        $use_limits = true;
        $scan_limit = $yeild["limit"];
    }
    if($nn > 10){
        //tools_dumpx("seeds ", __FILE__, __LINE__, $finished);
        //return $res;
    }
    foreach ($seeds as $foreign_key_table_name => $foreign_key_values) {
        //check if this is already finished
        if(in_array($foreign_key_table_name,$finished) == true){
            continue;
        }
        $foreign_key_name = $foreign_key_table_name . "_id";
        $next_branch = [];
        //process the where clause
        $_w = [];
        for ($w=0; $w < count($foreign_key_values); $w++) { 
            $foreign_key_value = $foreign_key_values[$w];
            if(count($_w) > 0){
                $temp = $_w[0];
                $_w = [
                    [$temp,"OR", [$foreign_key_name , "e", $foreign_key_value]]
                ];
            }else{
                $_w = [
                    [$foreign_key_name , "e", $foreign_key_value]
                ];
            }
        }
        if(count($_w) == 0){
            // we cannot delete with an empty where clause
            continue;
        }
        $children_tables = $flat_tree[$foreign_key_table_name];
        $select_query = [];
        $has_queries = false;
        $has_next_branch = false;
        $plural_child_table_names = [];
        for ($i=0; $i < count($children_tables); $i++) { 
            $child_table_name = $children_tables[$i];
            $plural_child_table_name  = Inflect::pluralize($child_table_name);
            if($use_limits == true){
                $select_query[$plural_child_table_name] = [
                        "_a" => "id " . $foreign_key_name  . " ",
                        "_pg" => $scan_limit,
                        "_w" => $_w
                ];
            }else{
                $select_query[$plural_child_table_name] = [
                    "_a" => "id " . $foreign_key_name  . " ",
                    "_w" => $_w
                ];
            }
            $has_queries = true;
            $plural_child_table_names[$plural_child_table_name] = $child_table_name;
        }
        //execute the query
        if($has_queries == true){
            //tools_dump("select_query ", __FILE__, __LINE__, $select_query);
            $brg_res = bee_run_get($select_query,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
            $res[BEE_EI] = array_merge($res[BEE_EI],$brg_res[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                //no errors so far
                //read in results
                foreach ($plural_child_table_names as $plural_child_table_name => $child_table_name) {
                    $affected_records = $brg_res[BEE_RI][$plural_child_table_name];
                    //tools_dumpx("affected_records ",__FILE__,__LINE__,$affected_records);
                    if(array_key_exists($child_table_name,$next_branch) == false){
                        $next_branch[$child_table_name] = [];
                    }
                    //yield
                    if(array_key_exists($child_table_name,$yeild["records"]) == false){
                        $yeild["records"][$child_table_name] = [];
                    }
                    if(count($affected_records) > 0){
                        //fill the target array
                        for ($t=0; $t < count($affected_records); $t++) { 
                            $affected_record = $affected_records[$t];
                            //tools_dumpx("next_branch ",__FILE__,__LINE__,$next_branch);
                            if(in_array($affected_record["id"],$next_branch[$child_table_name]) == false){
                                array_push($next_branch[$child_table_name],$affected_record["id"]);
                                $has_next_branch = true;
                            }
                            if(in_array($affected_record["id"],$yeild["records"][$child_table_name]) == false){
                                array_push($yeild["records"][$child_table_name],$affected_record["id"]);
                                
                            }
                        }
                        $yeild["total_affected"] += count($affected_records);
                    }else{

                    }
                }
            }else{
                //nyd
                //what show we do about this
            }
        }
        array_push($finished, $foreign_key_table_name);
        // tools_dumpx("seeds ", __FILE__, __LINE__, $finished);
        if($has_next_branch == true){
            //recurse
            $new_res = bee_hive_run_process_dependency_report(
                $yeild,
                $next_branch,
                $flat_tree,
                $bee,
                $finished,
                $nn + 1
            );
            $yeild = $new_res[BEE_RI];
            $res[BEE_EI] = array_merge($res[BEE_EI],$new_res[BEE_EI]);
        }
    }
    $res[BEE_RI] = $yeild;
    return $res;
}
?>