<?php
/*
Template Name: Upload
*/
get_header();

/*
 * include SimpleXLSX class https://github.com/shuchkin/simplexlsx
*/
require 'simplexlsx.class.php';
?>

<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <div><h3><a href="/upload-form/">Back to upload form</a></h3></div>

<?php
$target_dir = plugin_dir_path( __FILE__ )."uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

$xlsx = SimpleXLSX::parse($target_file);
$xls_to_arr = array_slice($xlsx->rows(), 1);

// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($fileType != "xls" && $fileType != "csv" && $fileType != "xlsx") {
    echo "Sorry, only XML, XLS & XLSX files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
            <div id="result"></div>
            <div>Added: <strong><span id="added"></span></strong> items</div>
            <div id="done"></div>

        </main><!-- #main -->
    </div><!-- #primary -->
<?php get_sidebar(); ?>
</div><!-- .wrap -->

<?php get_footer();?>

<script>
    jQuery(document).ready(function () {
        var added = 0;
        function ajaxwithi(i) {
            jQuery.ajax({
                url: '/process',
                type: 'POST',
                dataType: 'json',
                data: {
                    item: i,
                    target_file: '<?php echo $target_file;?>',
                },
                complete: function(result) {
                        // console.log('done');
                },
                success: function(result) {
                    jQuery('#result').html(result['done']);
                    jQuery('#added').html(added += result['success_counter']);
                    console.log(result);
                    if (i < result['items_amount'] - 1) {
                        ajaxwithi(i + 1);
                    }
                    else {
                        <?php set_contact_status($xls_to_arr);?>
                        jQuery('#done').html("<hr><span style='color: #1b8300;'>Done!</span>");
                    }
                }
            });
        }
        ajaxwithi(0);
    });
</script>

<?php
function set_contact_status ($xls_to_arr) {
    $contacts = array_group_by($xls_to_arr, 0);
    foreach ($contacts as $contact) {
        $last = end($contact);
        if ($last[5] == 'INCOMING') {

            $query = new AirpressQuery("Contacts", 'tsb');
            $query->table("Contacts");
            $query->filterByFormula("{Full name} = '".$last[0]."'");
            $query->setExpireAfter(0);
            $profile = new AirpressCollection($query);

            if ( $query->hasErrors() ) {
                print_r($query->getErrors());
                print_r($query->toString());
            }

            $field_to_update = array();
            $field_to_update['status_on_contact'] = 'Urgent Escalation';

            $profile[0]->update($field_to_update);
        }
    }
}
function array_group_by($array, $key) {
    $resultArr = [];
    foreach($array as $val) {
        $resultArr[$val[$key]][] = $val;
    }
    return $resultArr;
}
?>
