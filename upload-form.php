<?php
/*
Template Name: Upload form
*/
get_header();

$query = new AirpressQuery();
$query->setConfig("tsb");
$query->table('Database');
$query->addFilter("{Status}='Open'");

$res = new AirpressCollection($query);

?>

<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <div class="search-wrapper">
                <form method="post" enctype="multipart/form-data" action="/upload">
                    <div class="form-fieldset">
                        <div class="label-field">
                            <label for="table-for-upload">For Database</label>
                        </div>
                        <select name="table-for-upload" id="table-for-upload">
                            <?php foreach ($res as $re):?>
                                <option value="<?php echo $re['Name']; ?>"><?php echo $re['Name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-fieldset">
                        <div class="label-field">
                            <label for="fileToUpload">Import file</label>
                        </div>
                        <input type="file" name="fileToUpload" id="fileToUpload" accept=".xml, .xls, .xlsx">
                    </div>
                        <button type="submit" id="upload-submit">Upload</button>
                </form>
            </div>
        </main><!-- #main -->
    </div><!-- #primary -->
    <?php get_sidebar(); ?>
</div><!-- .wrap -->

<?php get_footer();