<?php

/*
 * Plugin Name: BibTexNG
 * Plugin URI: http://neminis.org/software/wordpress/plugin-bibtex-ng/
 * Description: This Plugin allows to import your BibTeX databases in WordPress. From an admin interface you can insert your BibTeX entries; then, by using shorcode, you can embed your Bibliography (or part of it) in WordPress page or post.
 * Version: 0.1
 * Author: Vincenzo Russo (aka Nemo) 
 * Author URI: http://neminis.org
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Library General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
 *
 ***/

global $wpdb;
global $bibtexng_paper_table, $bibtexng_field_table;

$bibtexng_paper_table = $wpdb->prefix . "bibtexng_paper";
$bibtexng_field_table = $wpdb->prefix . "bibtexng_field";

/**
 * Executed on the activation of the plugin. 
 * Create required tables if they do not exist.
 **/
function BibTexNG_activate() {
    global $wpdb;
    global $bibtexng_paper_table, $bibtexng_field_table;    
    
    // The first table stores the fields that are surely present in every type 
    // of publications. The same fields are the only that can be used as filter 
    // in the output. Such fields are: entry type, citation key, bibtex entry, year
    $paperSQL = "CREATE TABLE IF NOT EXISTS $bibtexng_paper_table (
        id bigint(20) NOT NULL auto_increment,
        entry_type varchar(255) NOT NULL default '',
        citation varchar(255) NOT NULL default '',
        entry longtext NOT NULL,
        year int(10) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY `key` (citation)
     ) ENGINE=InnoDB";

    // The second table stores all other fields of publications
    // as couples "key, value", where key is the name of the field
    $fieldSQL = "CREATE TABLE IF NOT EXISTS $bibtexng_field_table (
        id bigint(20) NOT NULL auto_increment,
        paper_citation varchar(255) NOT NULL default '',
        f_key varchar(255) NOT NULL default '',
        f_value longtext NOT NULL default '',
        PRIMARY KEY (id, paper_citation)
    ) ENGINE=InnoDB";

    $r = $wpdb->query($paperSQL);
    if ($r !== 0) die("Cannot create BibTexNG paper table");
    $r = $wpdb->query($fieldSQL);
    if ($r !== 0) die("Cannot create BibTexNG field table");    
}

/**
 * Insert bibtex entries in the database.
 * 
 * @param string $data  A string containing all the BibTeX entries (typically a file content)
 * @param array  $allowed_fields  An array containing the fields that the parser have to consider.
 **/
function BibTexNG_insert($data, $allowed_fields = array()) {
    global $wpdb;
    global $bibtexng_paper_table, $bibtexng_field_table;
    
    // BEGIN - Code is extracted from 'bib2html' plugin
    // by Sergio Andreozzi (http://sergioandreozzi.com)
    $OSBiBPath = dirname(__FILE__) . '/OSBiB/';
    include_once($OSBiBPath . 'format/bibtexParse/PARSEENTRIES.php');

    // parse the content of bib string and generate associative array with valid entries
    $parse = NEW PARSEENTRIES();
    $parse->expandMacro = TRUE;
    $parse->fieldExtract = TRUE;    
    $parse->removeDelimit = TRUE;
    $parse->loadBibtexString($data);
    $parse->extractEntries();
    list($preamble, $strings, $entries) = $parse->returnArrays();
    // END - Code extracted from 'bib2html'

    // Entry type, Citation, Entry and Year are always present and accepted,
    // so they have not to be in the following array
    // TODO: make this variable a customizable option from admin interface
    if (sizeof($allowed_fields) === 0) {    	
        $allowed_fields = array('author', 'title', 'booktitle', 'editor', 'volume', 
        'pages', 'number', 'organization', 'series', 'publisher', 'address', 'month', 'keywords',
        'journal', 'url', 'note', 'school', 'institution');
    }
    
    // counters for tracking the number of papers per entry type
    $counters = array('article' => 0, 'book' => 0, 'booklet' => 0, 'commented' => 0, 'conference' => 0, 
    'glossdef' => 0, 'inbook' => 0, 'incollection' => 0, 'inproceedings' => 0, 'jurthesis' => 0, 
    'manual' => 0, 'mastersthesis' => 0, 'misc' => 0, 'periodical' => 0, 'phdthesis' => 0, 
    'proceedings' => 0, 'techreport' => 0, 'unpublished' => 0, 'url' => 0, 'electronic' => 0, 
    'webpage' => 0, 'total' => 0);

    // if the first entry is not an array, there is no bibtex in the data posted
    if (!is_array($entries[0])) return false;
    
  
    // we use transactions below
    // the keyword "START TRANSACTION" is available in MySQL >= 4.0.11
    // that's enough for including not the support for the old keyword BEGIN
    // (and so the support for MySQL < 4.0.11)
  
    $wpdb->query("START TRANSACTION;");
    
    // for all the entries 
    for ($i = 0; $i < sizeof($entries); $i++) {
    
        // START to build the query for inserting entry fields
        // ---------------------------------------------------
        // Because of the nature of this building process
        // we cannot use the $wpdb->prepare() 
        $fieldSQL = "INSERT INTO $bibtexng_field_table (paper_citation, f_key, f_value) VALUES ";
        foreach ($entries[$i] as $k => $v) {
            if (in_array($k, $allowed_fields)) {
                if ($k === 'pages') $v = str_replace("--", "-", $v);
                $v = $wpdb->escape($v);
                $fieldSQL .= "('" . $entries[$i]['bibtexCitation'] . "', '$k', '$v'),";
            }
        }        
        $fieldSQL = substr($fieldSQL, 0, strlen($fieldSQL) - 1); 
        // We could use multiple INSERTs instead of MySQL-INSERT with
        // multiple VALUES and so use the $wpdb->prepare()
        // But that would result in a slower process
        // ---------------------------------------------------
        // STOP to build the query for inserting entry fields
        
        // beautify and clean BibTeX entry
        $entries[$i]['bibtexEntry'] = BibTexNG_beautify($entries[$i]['bibtexEntry']);
        
        // Building the query for inserting an entry
        $paperSQL = $wpdb->prepare("INSERT INTO $bibtexng_paper_table (entry_type, 
            citation, entry, year) VALUES (%s, %s, %s, %d) ON DUPLICATE KEY 
            UPDATE entry_type=%s, entry=%s, year=%d", 
            $entries[$i]['bibtexEntryType'], $entries[$i]['bibtexCitation'], 
            $entries[$i]['bibtexEntry'], $entries[$i]['year'], 
            $entries[$i]['bibtexEntryType'], $entries[$i]['bibtexEntry'], 
            $entries[$i]['year']);

        // When a query fails, break the process and rollback.   
        $r = $wpdb->query($paperSQL);
        if (!$r) { 
            $wpdb->query("ROLLBACK;");
            return false;
        }   
        $r = $wpdb->query($fieldSQL);
        if (!$r) { 
            $wpdb->query("ROLLBACK;");
            return false;
        }

        // increment the counter for the current entry type        
        $counters[$entries[$i]['bibtexEntryType']]++;
    }

    $wpdb->query("COMMIT;");                // everything is ok, commit
    $counters['total'] = sizeof($entries);  // add the total counter
    
    return $counters;
}

/**
 * Create the panel for adding BibTeX entries, under "Write > BibTeX Entries"
 *
 **/
function BibTexNG_write_panel() {
    // tell if URLs can be used as paths in file management functions
    $allow_url_fopen = ini_get('allow_url_fopen'); 
    
    // counters for entry types
    $counters = array();   
    
    // something was posted, get the data and insert them
    if ((sizeof($_POST) > 0) || (sizeof($_FILES) > 0)) {
        $data = "";
        
        if ((isset($_POST['entries']) && ($_POST['entries'] != "" ))) {
            $data .= $_POST['entries'];
        }
        if (($allow_url_fopen === "1") && (isset($_POST['url']) && ($_POST['url'] != "" ))) {
            $data .= file_get_contents($_POST['url']);
        }
        if (($_FILES['upload']['error'] === UPLOAD_ERR_OK)) {
            $data .= file_get_contents($_FILES['upload']['tmp_name']);
        }
        if ($data !== "") $counters = BibTexNG_insert($data); else { $counters = false;  echo "PPP";}
    }
    
    // init Write Panel 
    $panel = array();
    
    // if the insertion failed
    if (!$counters && (isset($_POST['entries']))) {
        $panel['message'] = "<div id='message' class='error'><p><strong>" . __("There was an error inserting your BibTeX entries. Please check the format and try again.", 'BibTexNG_domain') . "</strong></p><ul><li>No entries were inserted.</li></ul></div>";
    }
    else {
        // message of how many entries inserted - more than one
        if ($counters['total'] > 1) {
            $panel['message'] = "<div id='message' class='updated fade'><p><strong>" . __($counters['total'] . " BibTeX entries have been saved or updated", 'BibTexNG_domain') . "</strong></p>";
        }
        // message of how many entries inserted - only one
        if ($counters['total'] == 1) {
            $panel['message'] = "<div id='message' class='updated fade'><p><strong>" . __($counters['total'] . " BibTeX entry has been saved or updated", 'BibTexNG_domain') . "</strong></p>";
        }
        
        // details about the entries inserted (how many entries per entry type)
        $panel['message'] .= "<ul>";
        unset($counters['total']);
        foreach ($counters as $k => $v) {
            if ($v > 0) {
                $panel['message'] .= "<li><strong>" . $k . "</strong> (" . $v . ")</li>"; 
            }
        }
        $panel['message'] .= "</ul></div>";
    }

    // continue building the Write Panel after got the 'message'
    $panel['form_open'] = "<form enctype='multipart/form-data' accept='text/plain' name='addbibtex' id='addbibtex' method='post' action=''>";
    $panel['wrap_open'] = "<div class='wrap'>";
    $panel['title'] = "<h2>" . __('Add BibTeX entries', 'BibTexNG_domain') . "</h2>";
    $panel['poststuff_open'] = "<div id='poststuff'>";
    $panel['submitbox_open'] = "<br /><div class='submitbox' id='submitlink'><div id='previewview' style='text-align:justify'>" . __("You can directly paste entries, upload a BibTeX file and enter the URL of an online BibTeX file. All at the same time.", "BibTexNG_domain") . "</div>";
    $panel['submit'] = "<p class='submit'><input tabindex='4' type='submit' class='button button-highlighted' name='save' value='" . __('Save', 'BibTexNG_domain') . "' /></p>";
    $panel['submitbox_close'] = "</div>";
    $panel['postbody_open'] = "<div id='post-body'>";
    $panel['pasteentries_open'] = "<div id='paste' class='stuffbox'>";
    $panel['paste_title'] = "<h3><label for='entries'>" . __('Paste Entries', 'BibTexNG_domain') . "</label></h3>";
    $panel['paste_inside_open'] = "<div class='inside'>";
    $panel['paste_text_input'] = "<textarea tabindex='1' id='entries' rows='10' cols='82' name='entries'></textarea><br /><br />" . __('Copy BibTeX entries from your files and past them here.', 'BibTexNG_domain');
    $panel['paste_inside_close'] = "</div>";
    $panel['pasteentries_close'] = "</div>";

    // check if we can show the "URL" field in the write panel
    if ($allow_url_fopen === "1") {    
        $panel['bibfileurl_open'] = "<div id='bibfile' class='stuffbox'>";
        $panel['bibfileurl_title']  = "<h3><label for='url'>" . __('BibTeX file URL', 'BibTexNG_domain') . "</label></h3>";
        $panel['bibfileurl_inside_open'] = "<div class='inside'>";
        $panel['bibfileurl_text_input'] = "<input tabindex='2' id='url' name='url' value='' style='width: 98%'><br /><br />" . __('Paste the URL of a BibTeX file here.', 'BibTexNG_domain');
        $panel['bibfileurl_inside_close'] = "</div>";
        $panel['bibfileurl_close'] = "</div>";
    }
    
    $panel['bibfile_open'] = "<div id='bibfile' class='stuffbox'>";
    $panel['bibfile_title']  = "<h3><label for='upload'>" . __('BibTeX file upload', 'BibTexNG_domain') . " (" . sprintf(__('Maximum upload size: %s', 'BibTexNG_domain'), ini_get('upload_max_filesize')) . ")</label></h3>";
    $panel['bibfile_inside_open'] = "<div class='inside'>";
    $panel['bibfile_text_input'] = "<input tabindex='3' id='upload' name='upload' type='file'><br /><br />" . __('Choose the BibTeX file to upload from your local drives.', 'BibTexNG_domain');
    $panel['bibfile_inside_close'] = "</div>";
    $panel['bibfile_close'] = "</div>";
    
    $panel['postbody_close'] = "</div>";
    $panel['poststuff_close'] = "</div>";
    $panel['wrap_close'] = "</div>";
    $panel['form_close'] =  "</form>";
    
    // print Write Panel
    foreach ($panel as $element) {
        echo $element;
    }
}

/**
 *  Handler for attaching the write panel as submenu of Write menu.
 **/
function BibTexNG_show_write_panel() {
    add_menu_page(__('Manage Bibliography', 'BibTexNG_domain'), __('Bibliography', 'BibTexNG_domain'), 8, __FILE__, 'BibTexNG_write_panel');
	add_submenu_page(__FILE__, __('Add entries in BibTeX format', 'BibTexNG_domain'), __('Add BibTeX entries', 'BibTexNG_domain'), 8, __FILE__, 'BibTexNG_write_panel');
}


/**
 *  Handler for parsing the shortcode.
 * 
 *  @param array    $atts       Attributes of the shortcode
 *  @content string $content    The content enclosed (if the shortcode is used in its enclosing form)
 **/
function BibTexNG_shortcode_handler($atts, $content = null) {
    // by default, all filter attributes are null because in such a case 
    // we want to list all publications without any filter
    $default_atts = array(
        'key' => null,          //  filter
        'allowtype' => null,    //  filter
        'denytype' => null,     //  filter
        'year' => null,         //  filter
        'orderby' => 'year',
        'orderdir' => 'desc',    // default sorting: most recent publications first
        'template' => 'ieee'     // default TPL template for output
    );
    
    extract(shortcode_atts($default_atts, $atts));    
   
    global $bibtexng_paper_table, $bibtexng_field_table;
    
    // default query for extracting bibtex entries from DB
    $q = "SELECT entry_type, citation, entry, year, f_key, f_value 
        FROM $bibtexng_paper_table as p INNER JOIN $bibtexng_field_table as f
        ON p.citation=f.paper_citation WHERE 1";
        
    if ($key != null) { 
        $key = explode(",", $key);
        $or = "(p.citation='" . $key[0] . "'";
        for ($i = 1; $i < sizeof($key); $i++) {
            $or .=  " OR p.citation='" . $key[$i] . "'";
        }
        $or .= ")";
        
        $where = " AND $or";
        $q .= $where;
    } 
    
    if ($allowtype != null) {
        $allowtype = explode(",", $allowtype);
        $or = "(p.entry_type='" . $allowtype[0] . "'";
        for ($i = 1; $i < sizeof($allowtype); $i++) {
            $or .=  " OR p.entry_type='" . $allowtype[$i] . "'";
        }
        $or .= ")";
        
        $where = " AND $or";
        $q .= $where;
    } 

    if ($denytype != null) {
        $denytype = explode(",", $denytype);
        $and = "(p.entry_type<>'" . $denytype[0] . "'";
        for ($i = 1; $i < sizeof($denytype); $i++) {
            $and .=  " AND p.entry_type<>'" . $denytype[$i] . "'";
        }
        $and .= ")";
        
        $where = " AND $and";
        $q .= $where;
    }
    
    if ($year != null) {
        $year = explode(",", $year);
        $or = "(p.year=" . $year[0];
        for ($i = 1; $i < sizeof($year); $i++) {
            $or .=  " OR p.year=" . $year[$i];
        }
        $or .= ")";
        
        $where = " AND $or";
        $q .= $where;
    }

    if (($orderby == 'year') || ($orderby == 'entry_type') || ($orderby == 'citation')) {
        $q .= " ORDER BY $orderby " . strtoupper($orderdir);
    }
  
    return BibTexNG_format_entries($q, $template);    
}

/**
 * Prepare the entries for the HTML presentation
 *
 * @param $query The query built in 'BibTexNG_shortcode_handler'
 **/
function BibTexNG_format_entries($query, $template) {
    $templatePowerPath = dirname(__FILE__) . "/TemplatePower";
    include_once($templatePowerPath . '/class.TemplatePower.inc.php');
    
    $OSBiBPath = dirname(__FILE__) . '/OSBiB/';
    include_once($OSBiBPath . 'format/BIBFORMAT.php');

    /* Format the entries array  for html output */
    $bibformat = new BIBFORMAT($OSBiBPath, true); // TRUE implies that the input data is in bibtex format
    $bibformat->cleanEntry = true; // convert BibTeX (and LaTeX) special characters to UTF-8
    list($info, $citation, $styleCommon, $styleTypes) = $bibformat->loadStyle($OSBiBPath . "styles/bibliography/", "IEEE");
    $bibformat->getStyle($styleCommon, $styleTypes);
    
    $tpl = new TemplatePower(dirname(__FILE__) . "/templates/$template.tpl");
    $tpl->prepare();
    
    global $wpdb;
    $entries = $wpdb->get_results($query, ARRAY_A);
    
    if (sizeof($entries) == 0) return "<p>" . __("No bibliographic entries found.", "BibTexNG_domain") . "</p>";
    
    $last_citation = "";
    $b_entry = array();
    foreach ($entries as $entry) {
        if ($entry['citation'] != $last_citation) {
            
            if (sizeof($b_entry) > 0) {
                //  adds all the resource elements automatically to the BIBFORMAT::item array
		        $bibformat->preProcess($b_entry['bibtexEntryType'], $b_entry);
		        $tpl->assign("formatted_entry", stripslashes(str_replace(array('{', '}'), '', $bibformat->map())));
            }
            
            $last_citation = $entry['citation'];
            
            $tpl->newBlock("bibtex_entry");
            $tpl->assign("citation", $entry['citation']);
            $tpl->assign("entry", $entry['entry']);
            $tpl->assign("year", $entry['year']);            
            $tpl->assign($entry['f_key'], $entry['f_value']);
            $tpl->assign('link', __('no download', 'BibTexNG_domain'));
            
            $b_entry['bibtexEntryType'] = $entry['entry_type'];
            $b_entry['bibtexCitation'] = $entry['citation'];
            $b_entry['bibtexEntry'] = $entry['entry'];
            $b_entry['year'] = $entry['year'];
            $b_entry[$entry['f_key']] = $entry['f_value'];
        }
        else {
            $b_entry[$entry['f_key']] = $entry['f_value'];
            if ($entry['f_key'] != 'url') {
                $tpl->assign($entry['f_key'], $entry['f_value']);
            }
            else {
                $tpl->assign('link', "<a href='" . $entry['f_value'] ."' title='Download'>" . __('download', 'BibTexNG_domain'). "</a>");
            }
            

        }
    }
    if (sizeof($b_entry) > 0) {
        //  adds all the resource elements automatically to the BIBFORMAT::item array
        $bibformat->preProcess($b_entry['bibtexEntryType'], $b_entry);
        $tpl->assign("formatted_entry", stripslashes(str_replace(array('{', '}'), '', $bibformat->map())));
    }

    return $tpl->getOutputContent();
}

function BibTexNG_head() {
    echo '<link rel="stylesheet" href="' . get_option('siteurl') . '/wp-content/plugins/bibtex-ng/templates/highslide.css" type="text/css" media="screen" />';
    echo "<script type='text/javascript'>
    hs.graphicsDir = 'wp-content/plugins/bibtex-ng/highslide3/highslide/graphics/';
    hs.outlineType = 'rounded-white';
    hs.outlineWhileAnimating = true;
    hs.showCredits = false;
    </script>";
}

/**
 *  Beautifies the code of a BibTeX entry in order to be human-readable.
 *  Moreover, it removes some annoying non-standard fields.
 *
 *  @param  string  $entry  The BibTeX entry
 **/
function BibTexNG_beautify($entry){
    $entry = stripslashes($entry);

    // BEGIN - Code from 'bib2html' WordPress plugin by
    // Sergio Andreozzi (http://sergioandreozzi.com)
    $order = array("},");
    $replace = "}, <br />\n &nbsp;";
    
    // beautify
    $entry = preg_replace('/\s\s+/', ' ', trim($entry));
    $new_entry = str_replace($order, $replace, $entry);
    $new_entry = str_replace(", author", ", <br />\n &nbsp;&nbsp;author", $new_entry);
    $new_entry = str_replace(", Author", ", <br />\n &nbsp;&nbsp;author", $new_entry);
    $new_entry = str_replace(", AUTHOR", ", <br />\n &nbsp;&nbsp;author", $new_entry);
    $new_entry = preg_replace('/\},?\s*\}$/', "}\n}", $new_entry); 
    
    // END - Code from 'bib2html' WordPress plugin by
    
    // remove *really* unwanted bibtex field
    $ee = explode("\n", $new_entry);
    $new_entry = "";
    foreach ($ee as $e) {
        // * Bdsk is the prefix of the custom fields added by BibDesk 
        //   (a BibTeX manager for Mac). Such fields are typically
        //   huge and/or redundant, and do not add any useful information 
        //   for the end-users. 
        // * Local-Url: who want their own local path on the web? ;)
        if (!stripos($e, 'Bdsk') && !stripos($e, 'Local-Url')) { 
            $new_entry .= $e . "\n";
        }
    }
    return $new_entry;
}

wp_enqueue_script('highslide', "/wp-content/plugins/bibtex-ng/highslide3/highslide/highslide-with-html.packed.js", null, "3.0");
register_activation_hook(__FILE__, 'BibTexNG_activate');
add_shortcode('bib', 'BibTexNG_shortcode_handler');
add_action ('admin_menu', 'BibTexNG_show_write_panel');
add_action('wp_head', 'BibTexNG_head');
?>