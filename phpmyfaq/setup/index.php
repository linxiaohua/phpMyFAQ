<?php
/**
 * The main phpMyFAQ Setup
 *
 * This script checks the complete environment, writes the database connection
 * parameters into the file config/database.php and the configuration into the database.
 *
 * PHP Version 5.3
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @category  phpMyFAQ
 * @package   Setup
 * @author    Thorsten Rinne <thorsten@phpmyfaq.de>
 * @author    Tom Rochester <tom.rochester@gmail.com>
 * @author    Johannes Schlüter <johannes@php.net>
 * @author    Uwe Pries <uwe.pries@digartis.de>
 * @author    Matteo Scaramuccia <matteo@phpmyfaq.de>
 * @author    Florian Anderiasch <florian@phpmyfaq.de>
 * @copyright 2002-2013 phpMyFAQ Team
 * @license   http://www.mozilla.org/MPL/2.0/ Mozilla Public License Version 2.0
 * @link      http://www.phpmyfaq.de
 * @since     2002-08-20
 */

define('COPYRIGHT', '&copy; 2001-2013 <a href="http://www.phpmyfaq.de/">phpMyFAQ Team</a> | Follow us on <a href="http://twitter.com/phpMyFAQ">Twitter</a> | All rights reserved.');
define('PMF_ROOT_DIR', dirname(dirname(__FILE__)));
define('PMF_INCLUDE_DIR', PMF_ROOT_DIR . '/inc');
define('IS_VALID_PHPMYFAQ', null);

use Symfony\Component\ClassLoader\UniversalClassLoader;

if (! defined('DEBUG')) {
    define('DEBUG', true);
}

if ((@ini_get('safe_mode') != 'On' || @ini_get('safe_mode') !== 1)) {
    set_time_limit(0);
}

session_name('phpmyfaq-setup');
session_start();

require PMF_ROOT_DIR . '/config/constants.php';
require PMF_ROOT_DIR . '/setup/questionnaire.php';

//
// Setting up PSR-0 autoloader for Symfony Components
//
require PMF_INCLUDE_DIR . '/libs/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', PMF_INCLUDE_DIR . '/libs');
$loader->registerPrefix('PMF_', PMF_INCLUDE_DIR);
$loader->registerPrefix('Twig_', PMF_INCLUDE_DIR . '/libs');
$loader->register();

//
// Initialize static string wrapper
//
PMF_String::init('en');

$installer = new PMF_Installer();
$system    = new PMF_System();

//
// Initializing Twig
//
$twig = new Twig_Environment(
    new Twig_Loader_Filesystem(PMF_ROOT_DIR . '/setup/assets/twig')
);

$tplLayoutVars = array(
    'version'           => PMF_System::getVersion(),
    'currentYear'       => date('Y'),
    'criticalErrors'    => $installer->checkBasicStuff(),
    'filePermErrors'    => $installer->checkFilesystemPermissions(),
    'nonCriticalErrors' => $installer->checkNoncriticalSettings()
);

$twig->loadTemplate('layout.twig')->display($tplLayoutVars);

// not yet POSTed
if (!isset($_POST["sql_server"]) && !isset($_POST["sql_user"]) && !isset($_POST["sql_db"])) {

    $tplDatabaseVars = array(
        'databases' => $system->getSupportedSafeDatabases(true),
        'dirname'   => dirname(__DIR__)
    );
?>

        <form class="form-horizontal" action="setup.php" method="post">
        <div class="row">
            <div class="span6">
                <?php
                $twig->loadTemplate('database.twig')->display($tplDatabaseVars);
                ?>
            </div>

<?php if (extension_loaded('ldap')): ?>
            <div class="span6">
                <fieldset>
                <legend>Add your LDAP setup</legend>
                    <div class="control-group">
                        <label class="control-label" for="ldap_enabled">&nbsp;</label>
                        <div class="controls">
                            <label class="checkbox">
                                <input id="ldap_enabled" type="checkbox" name="ldap_enabled" value="yes" />
                                Enable LDAP support?
                            </label>
                            <p class="help-block">You can enable LDAP later if you like.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="ldap_server">LDAP server host:</label>
                        <div class="controls">
                            <input type="text" name="ldap_server" id="ldap_server" />
                            <p class="help-block">Please enter the host of your LDAP server.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="ldap_port">LDAP server port:</label>
                        <div class="controls">
                            <input type="number" name="ldap_port" value="389" id="ldap_port" />
                            <p class="help-block">Please enter the port of your LDAP server.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="ldap_user">LDAP user DN:</label>
                        <div class="controls">
                            <input type="text" name="ldap_user" id="ldap_user" />
                            <p class="help-block">Please enter your specified RDN username.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="ldap_password">LDAP password:</label>
                        <div class="controls">
                            <input name="ldap_password" type="password" id="ldap_password" />
                            <p class="help-block">Please enter your LDAP password.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="ldap_base">LDAP base DN:</label>
                        <div class="controls">
                        <input type="text" name="ldap_base" id="ldap_base" />
                            <p class="help-block">
                                Please enter your distinguished name, e.g. 'cn=John Doe,ou=Accounts,o=My Company,c=US'.
                            </p>
                        </div>
                    </div>
                    <p class="alert alert-info">
                        You can add additional LDAP configuration and more LDAP servers in the file
                        config/constants_ldap.php.
                    </p>

                </fieldset>
            </div>
        </div>
        <div class="row">
<?php endif; ?>

            <div class="span6">
                <fieldset>
                <legend>Your phpMyFAQ setup</legend>
                    <div class="control-group">
                        <label class="control-label" for="language">Default language:</label>
                        <div class="controls">
                            <select name="language" size="1" id="language">
                            <?php
                                if ($dir = @opendir(PMF_ROOT_DIR . '/lang')) {
                                    while ($dat = @readdir($dir)) {
                                        if (substr($dat, -4) == '.php') {
                                            printf('<option value="%s"', $dat);
                                            if ($dat == "language_en.php") {
                                                echo ' selected="selected"';
                                            }
                                            printf(
                                                '>%s</option>',
                                                $languageCodes[substr(strtoupper($dat), 9, 2)]
                                            );
                                        }
                                    }
                                } else {
                                    echo '<option>English</option>';
                                }
                            ?>
                            </select>
                            <p class="help-block">Please select your default language.</p>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="permLevel">Permission level:</label>
                        <div class="controls">
                                <select id="permLevel" name="permLevel" size="1" required="required">
                                    <option value="basic">Basic (no group support)</option>
                                    <option value="medium">Medium (with group support)</option>
                            </select>
                            <p class="help-block">
                                Complexity of rights and permissions.
                            </p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="realname">Your name:</label>
                        <div class="controls">
                            <input type="text" name="realname" id="realname" required="required" />
                            <p class="help-block">Please enter your real name.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="email">Your email address:</label>
                        <div class="controls">
                            <input type="email" name="email" id="email" required="required" />
                            <p class="help-block">Please enter your email adress.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="loginname">Your login name:</label>
                        <div class="controls">
                            <input type="text" name="loginname" id="loginname" required="required" />
                            <p class="help-block">Please enter your login name.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="password">Your password:</label>
                        <div class="controls">
                            <input type="password" name="password" id="password" required="required" />
                            <p class="help-block">Please enter your password.</p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="password_retype">Retype password:</label>
                        <div class="controls">
                            <input type="password" name="password_retyped" id="password_retype" required="required" />
                            <p class="help-block">Please retype your password.</p>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="row" style="padding-left: 20px; text-align: center;">

            <button class="btn btn-primary btn-large" type="submit">
                   Click to install phpMyFAQ <?php echo PMF_System::getVersion(); ?>
            </button>
        </div>
        <div class="row" style="padding-left: 20px;">
            <p class="alert alert-info" style="margin-top: 20px;">
                Your password will be saved with a <strong><?php echo PMF_ENCRYPTION_TYPE; ?></strong>
                encryption. You can change the encryption type for passwords in <em>config/constants.php</em>.
            </p>
        </div>
        </form>
<?php
    PMF_System::renderFooter();
} else {
    $installer->startInstall();
?>
        <p class="alert alert-success">
            Wow, looks like the installation worked like a charm. This is pretty cool, isn't it? :-)
        </p>

        <script type="text/javascript">
        //<![CDATA[
        var iframect = 0;

        function iframeUpdated() {
            if (iframect++ == 0) {
                return;
            }

            $('#questionnaireForm').hide();
            $('#questionnaireThanks').show();
        }

        function hide(item) {
            $('#configliste').hide();
        }

        function show(item) {
            $('#configliste').show();
        }
        //]]>
        </script>
        <iframe onload="iframeUpdated();" name="questionaireResult" style="display:none"></iframe>
        <form action="http://www.phpmyfaq.de/stats/getstatdata.php" method="post" target="questionaireResult"
              id="questionnaireForm" class="form-horizontal">

            <p>
                For further development we would like to get some feedback from our users. Therefore we'd ask you to
                take a few minutes of your time to answer a few questions.
            </p>
            <p class="alert alert-success">
                If you don't want to participate in the survey, you can directly visit
                <a href="../index.php">your version of phpMyFAQ</a> or login into your
                <a href="../admin/index.php">admin section</a>.
            </p>

            <h3>The survey</h3>

            <fieldset>
                <legend>General questions</legend>
                <div class="control-group">
                    <label class="control-label">How do you act like?</label>
                        <div class="controls">
                        <select name="q[individual]">
                            <option value="as an individual">as an individual</option>
                            <option value="as an organisation">as an organisation</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">What kind of organisation is that?</label>
                    <div class="controls">
                        <select name="q[organisation]">
                             <option value="private held">privately held</option>
                             <option value="public held">publicly held</option>
                             <option value="government organisation">governmental organisation</option>
                             <option value="foundation">foundation</option>
                             <option value="other">other</option>
                         </select>
                    </div>
                </div>
             </fieldset>

            <fieldset>
                <legend>Technical questions</legend>
                <div class="control-group">
                    <label class="control-label">Where did you install phpMyFAQ?</label>
                    <div class="controls">
                        <select name="q[server]">
                            <option value="server run by a hosting company">server run by a hosting company</option>
                            <option value="public server run by you/your organisation">public server run by you/your organisation</option>
                            <option value="private server run by you/your organisation">private server run by you/your organisation</option>
                            <option value="Don't know">Don't know</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Beyond our own nose</legend>
                <div class="control-group">
                    <label class="control-label">Which PHP software do you also use?</label>
                    <div class="controls">
                        <input type="text" name="q[other]" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Are you using other web technologies?</label>
                    <div class="controls">
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="ASP" />ASP
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="ASP.NET" />ASP.NET
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="jsp" />JAVA JSP
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="perl" />Perl
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="ruby" />Ruby / Ruby on Rails
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="python" />Python
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="clojure" />Clojure
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="scala" />Scala
                        </label>
                        <label class="checkbox inline">
                            <input type="checkbox" name="q[other][]" value="go" />Go
                        </label>
                    </div>
                </div>
             </fieldset>

            <p class="alert alert-info">
                In addition to your input we're going to submit some information about your system setup for statistical
                purposes.
            </p>
            <p class="alert alert-info">
                We are not storing any personal information. You can see the data by clicking
                <a href="#" onclick="show('configliste'); return false;">here</a>.
            </p>

            <div id="configliste" class="hide">
                <a href="#" onclick="hide('configliste'); return false;">hide again</a>
                <?php
                $installer->printDataList();
                ?>
            </div>
            <p style="text-align: center;">
                <button class="btn btn-primary" type="submit">
                    Click here to submit the data and finish the installation process
                </button>
            </p>
        </form>
        <div id="questionnaireThanks" style="display:none;">
            <p class="alert alert-success"><b>Thank you for giving your feedback!</b></p>
            <p>
                You can visit <a href="../index.php">your version of phpMyFAQ</a> or login into your
                <a href="../admin/index.php">admin section</a>.
            </p>
        </div>
<?php
    $installer->cleanUpFiles();
    PMF_System::renderFooter();
}