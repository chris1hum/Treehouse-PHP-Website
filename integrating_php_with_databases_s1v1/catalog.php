<?php 

include("inc/functions.php");

$pageTitle = "Full Catalog";
$section = null;
$items_per_page = 8;

//query string to set which page is displayed
if (isset($_GET["cat"])) {
    if ($_GET["cat"] == "books") {
        $pageTitle = "Books";
        $section = "books";
    } else if ($_GET["cat"] == "movies") {
        $pageTitle = "Movies";
        $section = "movies";
    } else if ($_GET["cat"] == "music") {
        $pageTitle = "Music";
        $section = "music";
    }
}

//Query string used for pageanation
if (isset($_GET["pg"])) {
    $current_page = filter_input(INPUT_GET, "pg", FILTER_SANITIZE_NUMBER_INT);
}
if (empty($current_page)) {
    $current_page = 1;
}

$total_items = get_catalog_count($section);
$total_pages = ceil($total_items / $items_per_page);

//limit results in redirect
$limit_results = '';
if(!empty($section)) {
    $limit_results = "cat=" . $section . "&";
}

//redirect too-large page numbers to the last page
if ($current_page > $total_pages) {
    header("location:cataog.php?"
    . $limit_results
    . "pg=" .$total_pages);
}

// redirect too-small page numbers to the first page
if ($current_page < 1) {
    header("location:cataog.php?pg=1"
    . $limit_results
    . "pg=1");
}

//determine the offset (number of items to skip) for the current page
//For example: on page 3 with 8 items per page, the offset would be 16
$offset = ($current_page - 1) * $items_per_page;

//Gets catalog entries based on categories.
if (empty($section)) {
    $catalog = full_catalog_array($items_per_page, $offset);
} else {
    $catalog = category_catalog_array($section,$items_per_page,$offset);
}

$pagination = "<div class=\"pagination\">";
$pagination .= "Pages:";
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == $current_page) {
        $pagination .= " <span>$i</span>";
    } else {
        $pagination .= " <a href='catalog.php?";
        if (!empty($section)) {
            $pagination .= "cat=".$section."&";
        }
        $pagination .= "pg=$i'>$i</a>";
    }
}
$pagination .= "</div>";

include("inc/header.php"); ?>

<div class="section catalog page">
    
    <div class="wrapper">
        
        <h1><?php 
        if ($section != null) {
            echo "<a href='catalog.php'>Full Catalog</a> &gt; ";
        }
        echo $pageTitle; ?></h1>
        <?php echo $pagination ?>
        <ul class="items">
            <?php
            foreach ($catalog as $item) {
                echo get_item_html($item);
            }
            ?>
        </ul>
        <?php echo $pagination ?>
        
    </div>
</div>

<?php include("inc/footer.php"); ?>