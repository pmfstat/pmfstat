<?php

interface Questionnaire_Data_Provider
{
    public function getIdentifier();
    public function getData();
}

class Questionnaire_PHP_Data_Provider implements Questionnaire_Data_Provider
{
    public function getIdentifier()
    {
        return 'PHP';
    }

    /**
     * Get data about the PHP runtime setup.
     *
     * @return  array
     */
    public function getData()
    {
        return array(
            'version'                       => PHP_VERSION,
            'sapi'                          => PHP_SAPI,
            'int_size'                      => defined('PHP_INT_SIZE') ? PHP_INT_SIZE : '',
            'safe_mode'                     => (int)ini_get('safe_mode'),
            'open_basedir'                  => (int)ini_get('open_basedir'),
            'memory_limit'                  => ini_get('memory_limit'),
            'allow_url_fopen'               => (int)ini_get('allow_url_fopen'),
            'allow_url_include'             => (int)ini_get('allow_url_include'),
            'file_uploads'                  => (int)ini_get('file_uploads'),
            'upload_max_filesize'           => ini_get('upload_max_filesize'),
            'post_max_size'                 => ini_get('post_max_size'),
            'disable_functions'             => ini_get('disable_functions'),
            'disable_classes'               => ini_get('disable_classes'),
            'enable_dl'                     => (int)ini_get('enable_dl'),
            'magic_quotes_gpc'              => (int)ini_get('magic_quotes_gpc'),
            'register_globals'              => (int)ini_get('register_globals'),
            'filter.default'                => ini_get('filter.default'),
            'zend.ze1_compatibility_mode'   => (int)ini_get('zend.ze1_compatibility_mode'),
            'unicode.semantics'             => (int)ini_get('unicode.semantics'),
            'zend_thread_safty'             => (int)function_exists('zend_thread_id'),
            'extensions'                    => get_loaded_extensions()
        );
    }
}


class Questionnaire_System_Data_Provider implements Questionnaire_Data_Provider
{
    public function getIdentifier()
    {
        return 'System';
    }

    /**
     * Get data about the general system information, like OS or IP (shortened).
     *
     * @return  array
     */
    public function getData()
    {
        // Start discovering the IPV4 server address, if available
        $serverAddress = '0.0.0.0';
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverAddress = $_SERVER['SERVER_ADDR'];
        }
        // Running on IIS?
        if (isset($_SERVER['LOCAL_ADDR'])) {
            $serverAddress = $_SERVER['LOCAL_ADDR'];
        }
        $aIPAddress = explode('.', $serverAddress);

        return array(
            'os'    => PHP_OS,
            'httpd' => $_SERVER['SERVER_SOFTWARE'],
            // we don't want the real IP address (for privacy policy reasons) but only
            // a network address to see whether your phpMyFAQ is running on a private or public network.
            // IANA reserved addresses for private networks (RFC 1918) are:
            // - 10.0.0.0/8
            // - 172.16.0.0/12
            // - 192.168.0.0/16
            'ip'    => $aIPAddress[0].'.'.$aIPAddress[1].'.XXX.YYY'
        );
    }
}

/**
 * This class collects data which is used to create some usage statistics.
 *
 * The collected data is - after authorization of the administrator - submitted
 * to a central server. For privacy reasons we try to collect only data which aren't private
 * or don't give any information which might help to identify the user.
 *
 * @author      Johannes Schlueter <johannes@php.net>
 * @copyright   (c) 2007-2008 Johannes Schlueter 
 */

class Questionnaire_Data_Collector
{
    private $providers;
    private $data = null;

    /**
     * Constructor.
     *
     * @param   array
     * @param   string
     */
    function __construct()
    {
        $this->providers = new SplObjectStorage();
    }

    public function addDataProvider(Questionnaire_Data_Provider $provider)
    {
        $this->providers->attach($provider);
    }

    /**
     * Get data as an array.
     *
     * @return  array All Data
     */
    function get()
    {
        if (!$this->data) {
            $this->collect();
        }

        return $this->data;
    }

    /**
     * Collect info into the data property.
     *
     * @return  void
     */
    function collect()
    {
        foreach ($this->providers as $value) {
            $this->data[$value->getIdentifier()] = $value->getData();
        } 
    }
}

class Questionnaire_Renderer {
    protected $url = "http://www.phpmyfaq.de/stats/getstatdata.php";
    protected $collector;

    public function __construct(Questionnaire_Data_Collector $collector) {
        $this->collector = $collector;
    }

    /**
     * Output the data as an HTML Definition List.
     *
     * @param   mixed
     * @param   string
     * @param   string
     * @return  void
     */
    function data_printer($value, $key, $ident = "\n\t")
    {
        echo $ident, '<dt>', htmlentities($key), '</dt>', $ident, "\t", '<dd>';
        if (is_array($value)) {
            echo '<dl>';
            array_walk($value, array($this, 'data_printer'), $ident."\t");
            echo $ident, "\t", '</dl>';
        } else {
            echo htmlentities($value);
        }
        echo '</dd>';
    }

    public function __toString()
    {
        ob_start();
        echo <<<EOT
<script type="text/javascript">
//<![CDATA[
var iframect = 0;

function iframeUpdated() {
    if (iframect++ == 0) {
        return;
    }

    hide("questionnaireForm");
    show("questionnaireThanks");
}

function hide(item) {
    cssAddClass(item, 'collapsed');
}

function show(item) {
    cssDelClass(item, 'collapsed');
}
//]]>
</script>
<iframe onload="iframeUpdated();" name="questionaireResult" style="display:none"></iframe>
<form action="{$this->url}" method="post" target="questionaireResult" id="questionnaireForm">

    <p class="center">For further development we would like to get some feedback from our users.<br />Therefore we'd ask you to spend us a few minutes from your time and answer a few questions.</p>
    <p class="center">If you don't want to you can directly visit <a href="../index.php">your version of phpMyFAQ</a> or login into your <a href="../admin/index.php">admin section</a>.</p>

    <fieldset class="installation">
        <legend class="installation">General questions</legend>
        <label class="leftquestionaire">How do you act like?</label>
        <select name="q[individual]">
            <option>as an individual</option>
            <option>as an organisation</option>
        </select>
        <br/>
        <label class="leftquestionaire">What kind of organisation is that?</label>
        <select name="q[organisation]">
             <option>private held</option>
             <option>public held</option>
             <option>government organisation</option>
             <option>foundation</option>
             <option>other</option>
         </select>
     </fieldset>
     <br />

     <fieldset class="installation">
         <legend class="installation">Technical questions</legend>
         <label class="leftquestionaire">Where did you installed phpMyFAQ?</label>
         <select name="q[server]">
             <option>server run by a hosting company</option>
             <option>public server run by you/your organisation</option>
             <option>private server run by you/your organisation</option>
             <option>Don't know</option>
         </select>
     </fieldset>
     <br />

     <fieldset class="installation">
         <legend class="installation">Beyond our own nose</legend>
         <label class="leftquestionaire">Which PHP software do you also use?</label>
         <input name="q[other]" /><br />

         <label class="leftquestionaire">Are you using other web technologies?</label>
         <input type="checkbox" name="q[other][]" value="ASP" />ASP
         <input type="checkbox" name="q[other][]" value="ASP.NET" />ASP.NET
         <input type="checkbox" name="q[other][]" value="jsp" />JAVA JSP
         <input type="checkbox" name="q[other][]" value="perl" />Perl
         <input type="checkbox" name="q[other][]" value="ruby" />Ruby / Ruby on Rails
         <input type="checkbox" name="q[other][]" value="python" />Python
     </fieldset>
    <br />

    <p class="center">Additional to your input we're going to submit some information about your system setup for statstic purpose.</p>
    <p class="center">We are not storing any personal information. You can see the data by clicking <a href="#" onclick="show('configliste');return false;">here</a>.</p>

    <div id="configliste" class="collapsed">
        <a href="#" onclick="hide('configliste'); return false;">hide again</a>
        <dl>
EOT;
    $options = $this->collector->get();
    array_walk($options, array($this, 'data_printer'));
    echo '</dl><input type="hidden" name="systemdata" value="'.htmlspecialchars(serialize($options), ENT_QUOTES).'" />';
    echo <<<EOT
    </div>
    <p class="center"><input type="submit" value="Click here to submit the data and fnish the installation process" /></p>
</form>
<div id="questionnaireThanks" style="display:none;">
    <p class="center"><b>Thank you for giving your feedback!</b></p>
    <p class="center">You can visit <a href="../index.php">your version of phpMyFAQ</a> or</p>
    <p class="center">login into your <a href="../admin/index.php">admin section</a>.</p>
</div>
EOT;
        return ob_get_clean();
    }
}
