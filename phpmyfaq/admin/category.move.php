<?php
/**
* $Id: category.move.php,v 1.4 2005-11-22 20:11:50 b33blebr0x Exp $
*
* Select a category to move
*
* @author       Thorsten Rinne <thorsten@phpmyfaq.de>
* @since        2004-04-29
* @copyright    (c) 2004-2005 phpMyFAQ Team
* 
* The contents of this file are subject to the Mozilla Public License
* Version 1.1 (the "License"); you may not use this file except in
* compliance with the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
* 
* Software distributed under the License is distributed on an "AS IS"
* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
* License for the specific language governing rights and limitations
* under the License.
*/

if (!defined('IS_VALID_PHPMYFAQ_ADMIN')) {
    header('Location: http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']));
    exit();
}

if ($permission["editcateg"]) {
    $id = $_GET['cat'];
    $parent_id = $_GET['parent_id'];
    $cat = new category;
    $categories = $cat->getAllCategories();
    unset($cat->categories);
    $cat->getCategories($parent_id, false);
    $cat->buildTree($parent_id);
    print "<h2>".$PMF_LANG["ad_categ_edit_1"]." <em>".$categories[$id]["name"]."</em> ".$PMF_LANG["ad_categ_edit_2"]."</h2>\n";
?>
	<form action="<?php print $_SERVER["PHP_SELF"].$linkext; ?>" method="post">
    <fieldset>
        <legend><?php print $PMF_LANG["ad_categ_edit_1"]; ?></legend>
	    <input type="hidden" name="aktion" value="changecategory" />
	    <input type="hidden" name="cat" value="<?php print $id; ?>" />
	    <div class="row"><span class="label"><strong><?php print $PMF_LANG["ad_categ_change"]; ?>:</strong></span>
        <select name="change" size="1">
        <?php print $cat->printCategoryOptions(); ?>
        </select></div>
        <div class="row"><span class="label"><strong>&nbsp;</strong></span>
        <input class="submit" type="submit" name="submit" value="<?php print $PMF_LANG["ad_categ_updatecateg"]; ?>" /></div>
    </fieldset>
    </form>
<?php
} else {
    print $PMF_LANG["err_NotAuth"];
}
?>
