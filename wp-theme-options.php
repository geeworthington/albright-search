<?php

// Add the ajax fetch for Product Sidebar Search
add_action( 'wp_footer', 'ajax_sidebar_search_fetch' );

function ajax_sidebar_search_fetch() {
    ?>
    <script type="text/javascript">
        function product_sidebar_fetch(){

            var productSidebarForm = jQuery('#product-sidebar-filter-form');

            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: productSidebarForm.attr('method'),
                data: { action: 'data_sidebar_search_fetch_after', form: productSidebarForm.serialize()},
            }).done(function(response1) {
                jQuery('.product-listing__search-listings__content').html( response1 );
            });
        }
    </script>

    <?php
}


// The ajax function for Product Sidebar Search
add_action('wp_ajax_data_sidebar_search_fetch' , 'data_sidebar_search_fetch');
add_action('wp_ajax_nopriv_data_sidebar_search_fetch','data_sidebar_search_fetch');

function data_sidebar_search_fetch(){

    parse_str($_POST["form"], $_POST);

    global $searchFilter;

    $filterArray = array();

    if(isset($_POST["contactTerminalConfig"]) && !empty($_POST["contactTerminalConfig"]) ) {
        $contactTerminalConfigValue = $_POST["contactTerminalConfig"];
        $filterArray['Main_Contact_Terminal_Configuration'] = $contactTerminalConfigValue;
    }

    if(isset($_POST["currentRatingInterrupted"]) && !empty($_POST["currentRatingInterrupted"]) ) {
        $currentRatingInterruptedValue = (int)$_POST["currentRatingInterrupted"];
        $filterArray['Thermal_Current_Rating_Interrupted_Current'] = $currentRatingInterruptedValue;
    }

    if(isset($_POST["Thermal_Current_Rating_Uninterrupted_Current"]) && !empty($_POST["Thermal_Current_Rating_Uninterrupted_Current"]) ) {
        $currentRatingUninterruptedValue = (int)$_POST["currentRatingUninterrupted"];
        $filterArray['Thermal_Current_Rating_Uninterrupted_Current'] = $currentRatingUninterruptedValue;
    }

    if(isset($_POST["config"]) && !empty($_POST["config"]) ) {
        $configValue = $_POST["config"];
        $filterArray['Configuration'] = $configValue;
    }

    if(isset($_POST["pole"]) && !empty($_POST["pole"]) ) {
        $configValue = $_POST["pole"];
        $filterArray['Pole'] = $configValue;
    }

    if(isset($_POST["throw"]) && !empty($_POST["throw"]) ) {
        $configValue = $_POST["throw"];
        $filterArray['Throw'] = $configValue;
    }

    if(isset($_POST["largeTips"]) && !empty($_POST["largeTips"]) ) {
        $configValue = $_POST["largeTips"];
        $filterArray['Contact_Tips_Configuration'] = $configValue;
    }


    $searchFilter = new MultipleKeyValueFilter(
        $filterArray
    );

    dump($searchFilter);

}


// The ajax function for Product Sidebar Search
add_action('wp_ajax_data_sidebar_search_fetch_after' , 'data_sidebar_search_fetch_after');
add_action('wp_ajax_nopriv_data_sidebar_search_fetch_after','data_sidebar_search_fetch_after');

function data_sidebar_search_fetch_after(){

    data_sidebar_search_fetch();

    global $searchFilter;

    $args = array(
        'post_type' => 'products',
        'relevanssi' => true,
        'orderby' => 'date',
        'posts_per_page' => -1,
    );

    // Get Wordress Product Data
    $preQuery = new WP_Query($args);
    $data = array_map(
        function ($post) {
            return (array)$post;
        },
        $preQuery->posts
    );

    $repo = new \App\Repository\ProductRepository();
    $products = $repo->getAllProducts();


    // Get Product Data from external Database
    $productDataArray = [];

    $contactTipsArray = [];
    $productsArray = [];

    foreach ($products as $row) {

        $productsArray[] = array(
            "Data" => $productDataArray[] = $row->getData(),
        );

        $contactTips[] = [ "Contact_Tips" => $row->getContactTipOptions()];
    }


    foreach ($productsArray as $products) {

        $productTitles = array('post_title' => $products['Data']['Type']);
        $productData = $products['Data'];
        $productContactTips = ["Contact_Tips" => $products['Contact_Tips']];

        $productResults[] = $productData + $productContactTips;
        
    }

    $indexedResult = array_values($productResults);

    dump(array_filter($indexedResult, array($searchFilter, 'filter')));

}