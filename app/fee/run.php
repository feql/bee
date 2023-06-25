<?php
    //a page is made up of a content-wrapper for a header and content area
    function page($id){
        $html =         '<div class="content-wrapper content-height page_content page_content_hidden"';
        $html = $html . ' id="page_content_' . $id . '_page"> \n';
        $html = $html . ' <!-- Content Header (Page header) -->  \n';
        $html = $html . ' <section class="content-header" > \n';
        $html = $html . ' </section> \n';
        $html = $html . ' <!-- Main content -->  \n';
        $html = $html . ' <section class="content" > \n';
        $html = $html . ' </section> \n';
        $html = $html . '</div> \n';
        return $html;
    }

    echo page("clients");
?>

