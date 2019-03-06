<?php
require 'simplexlsx.class.php';

if ( $xlsx = SimpleXLSX::parse($_POST['target_file']) ) {
    $xls_to_arr = array_slice($xlsx->rows(), 1);
    $items_amount = count($xls_to_arr);

    $answer['item'] = $_POST['item'];
    $answer['success_counter'] = createMessage ($xls_to_arr, $_POST['item']);

    $answer['items_amount'] = $items_amount;
    $real_item = $answer['item'] + 1;

    $answer['done'] = "<div><hr><strong>$real_item</strong> item(s) successfully processed from <strong>$items_amount</strong></div>";

    echo json_encode($answer);

} else {
    echo "<hr>";
    echo "Error!<br>";
    echo SimpleXLSX::parse_error();
}

function createMessage ($xls_to_arr, $i = 0) {
    //Airtable connection init
    $query = new AirpressQuery();
    $query->setConfig("tsb");
    $query->table("Messages");
    //$query->setExpireAfter(0);
    $messages = new AirpressCollection($query);
    $stop = $i+1;
    $success_counter = 0;

    for ($i; $i<$stop; $i++) {

        foreach ($messages as $message) {
            if (isset($message['Lookupfullname_ARRAY'])){
                if (in_array($xls_to_arr[$i][0], $message['Lookupfullname_ARRAY']) || in_array($xls_to_arr[$i][1], $message['Lookupfullname_ARRAY'])) {
                    continue 2;
                }
            }
        }

        $result = array(
            'From' => $xls_to_arr[$i][0],
            'To' => $xls_to_arr[$i][1],
            'Date' => date("Y-m-d H:i", strtotime($xls_to_arr[$i][2])),
            'Subject' => $xls_to_arr[$i][3],
            'Message' => $xls_to_arr[$i][4],
            'Direction' => ucfirst(strtolower($xls_to_arr[$i][5])),
            'Folder' => $xls_to_arr[$i][6]
        );

        $new_joint_data = AirpressConnect::create('tsb',"Messages", $result);
        $new_query = new AirpressQuery("Messages", 'tsb');
        $new_collection = new AirpressCollection($new_query, false);
        $new_collection->setRecords(array($new_joint_data));

        if ( $query->hasErrors() ) {
            print_r($query->getErrors());
            print_r($query->toString());
        }
        $success_counter++;
    }
    return $success_counter;
}

