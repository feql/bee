<?php
$pg="
    content_header
        row
            col8
                row 
                    col6
                        search_text_input#guests_page_q_input
                    col6
            col4
    
    content_main
        row
            col8
                box_page
                    box_body#guests_page_table_box_body
                    overlay#clients_page_overlay
            col4
                box_form
";

$arr = explode("\n", $pg); 
echo "<pre>";
print_r($arr);
echo "</pre>";

$ana = array();
for ($i=0; $i < count($arr); $i++) { 
    $line = $arr[$i];
    $f = strspn($line, " ");
    $c = trim($line);
    array_push($ana, $f . ": " . $c);
}

echo "<br/><pre>";
print_r($ana);
echo "</pre>";
?>


        
    
