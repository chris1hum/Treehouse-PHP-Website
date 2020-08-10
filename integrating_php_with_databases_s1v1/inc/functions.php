<?php

//get count of catalog items for pageanation
function get_catalog_count($category = null) {
    $category = strtolower($category);
    include("connection.php");

    try {
        $sql = "SELECT COUNT(media_id) FROM Media";
        if (!empty($category)) {
            $result = $db->prepare(
                $sql
                . " WHERE LOWER(category) = ?"
            );
            $result->bindParam(1,$category,PDO::PARAM_STR);
        } else {
            $result = $db->prepare($sql);
        }
        $result->execute();
    } catch (Exception $e) {
        echo "bad query";
    }

    $count = $result->fetchColumn(0);
    return $count;
}

//returns the full catalog array
function full_catalog_array($limit = null, $offset = 0) {
    include("connection.php");

    try {
        $sql = "SELECT media_id, title, category, img 
        FROM Media
        ORDER BY 
            REPLACE(
                REPLACE(
                    REPLACE(title, 'The ', ''),
                    'An ', ''
                ),
                'A ', ''
            )";
        if (is_integer($limit)) {
            $results = $db->prepare($sql . " LIMIT ? OFFSET ?");
            $results->bindParam(1,$limit,PDO::PARAM_INT);
            $results->bindParam(2,$offset,PDO::PARAM_INT);
        } else {
            $results = $db->prepare($sql);
        }
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results.";
        exit;
    }

    $catalog = ($results->fetchAll());
    return $catalog;
}

//returns the category catalog array
function category_catalog_array($category, $limit = null, $offset = 0) {
    include("connection.php");
    $category = strtolower($category);
    try {
        $sql = "SELECT media_id, title, category, img 
        FROM Media
        WHERE LOWER(category) = ?
        ORDER BY 
            REPLACE(
                REPLACE(
                    REPLACE(title, 'The ', ''),
                    'An ', ''
                ),
                'A ', ''
            )";
        if (is_integer($limit)) {
            $results = $db->prepare($sql . " LIMIT ? OFFSET ?");
            $results->bindParam(1,$category,PDO::PARAM_STR);
            $results->bindParam(2,$limit,PDO::PARAM_INT);
            $results->bindParam(3,$offset,PDO::PARAM_INT);
        } else { 
            $results = $db->prepare($sql);
            $results->bindParam(1,$category,PDO::PARAM_STR);
        }
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results.";
        exit;
    }

    $catalog = ($results->fetchAll());
    return $catalog;
}

//Returns 4 random catalog items
function random_catalog_array() {
    include("connection.php");

    try {
        $results = $db->query(
            "SELECT media_id, title, category, img 
            FROM Media
            ORDER BY RAND()
            LIMIT 4"
            );
    } catch (Exception $e) {
        echo "Unable to retrieve results.";
        exit;
    }

    $catalog = ($results->fetchAll());
    return $catalog;
}


//returns information from a single item, giving us the details
function single_item_array($id) {
    include("connection.php");
    
    try {
        $results = $db->prepare(
            "SELECT title, category, img, format, year, publisher, isbn, genre
            FROM Media
            JOIN Genres 
            ON Media.genre_id = Genres.genre_id
            LEFT OUTER JOIN Books
            ON Media.media_id = Books.media_id
            WHERE Media.media_id = ?"
        );
        $results->bindParam(1,$id,PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results.";
        exit;
    }

    $item = ($results->fetch());
    if (empty($item)) return $item;
    try {
        $results = $db->prepare(
            "SELECT fullname, role
            FROM Media_People
            JOIN People
            ON Media_People.people_id = People.people_id
            WHERE Media_People.media_id = ?"
        );
        $results->bindParam(1,$id,PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo "Unable to retrieve results.";
        exit;
    }
    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $item[$row["role"]][] = $row["fullname"];
    }
    return $item;
}


function get_item_html($item) {
    $output = "<li><a href='details.php?id="
        . $item["media_id"] . "'><img src='" 
        . $item["img"] . "' alt='" 
        . $item["title"] . "' />" 
        . "<p>View Details</p>"
        . "</a></li>";
    return $output;
}

function array_category($catalog,$category) {
    $output = array();
    
    foreach ($catalog as $id => $item) {
        if ($category == null OR strtolower($category) == strtolower($item["category"])) {
            $sort = $item["title"];
            $sort = ltrim($sort,"The ");
            $sort = ltrim($sort,"A ");
            $sort = ltrim($sort,"An ");
            $output[$id] = $sort;            
        }
    }
    
    asort($output);
    return array_keys($output);
}


function genre_drop_down() {}
//create a function that will pull the genres from the db
//function needs to include the ability to separate by category (books, movies, etc)
//should contain an if statement that uses the field above, and if someone selects books, movies, or music, only display the items from that category
//if no category is selected, then it will show all of them
//there needs to be a 'select one' option which evaluates as null, since this is an optional field.