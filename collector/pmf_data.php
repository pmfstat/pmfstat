<?Php
include('questionnaire.php');

class Questionnaire_phpMyFAQ_Data_Provider implements Questionnaire_Data_Provider
{
    private $config;
    private $oldversion;

    /**
     * Constructor.
     *
     * @param   array
     * @param   string
     */
    function __construct($config, $oldversion = 0)
    {
        $this->config = $config;
        $this->config['oldversion'] = $oldversion;
    }

    public function getIdentifier()
    {
        return 'phpMyFAQ';
    }


    /**
     * Get data about this phpMyFAQ installation.
     *
     * @return  array
     * @access  public
     * @since   2007-03-17
     * @author  Johannes Schlueter <johannes@php.net>
     * @author  Matteo Scaramuccia <matteo@scaramuccia.com>
     */
    public function getData()
    {
        // oldversion isn't a real PMF config option and it is just used by this class
        $settings = array(
            'main.currentVersion',
            'oldversion',
            'main.language',
            'main.permLevel',
            'main.languageDetection',
            'main.ldapSupport'
        );

        if (function_exists('array_intersect_key')) {
            return array_intersect_key($this->config, array_flip($settings));
        } else {
            $result = array();
            $selected = array_flip($settings);
            foreach ($this->config as $key => $value) {
                if (array_key_exists($key, $selected)) {
                    $result[$key] = $this->config[$key];
                }
            }

            return $result;
        }
    }
}


$c = new Questionnaire_Data_Collector();
$c->addDataProvider(new Questionnaire_PHP_Data_Provider());
$c->addDataProvider(new Questionnaire_System_Data_Provider());
$c->addDataProvider(new Questionnaire_phpMyFAQ_Data_Provider(array(), "1.0"));

$url = "http://www.phpmyfaq.de/stats/getstatdata.php";

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
        array_walk($value, 'data_printer', $ident."\t");
        echo $ident, "\t", '</dl>';
    } else {
        echo htmlentities($value);
    }
    echo '</dd>';
}

?>
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
<form action="<?php echo $url;?>" method="post" target="questionaireResult" id="questionnaireForm">

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
<?php
    array_walk($c->getDataRaw(), 'data_printer');
    echo '</dl><input type="hidden" name="systemdata" value="'.$c->getDataForForm().'" />';
?>
    </div>
    <p class="center"><input type="submit" value="Click here to submit the data and fnish the installation process" /></p>
</form>
<div id="questionnaireThanks" style="display:none;">
    <p class="center"><b>Thank you for giving your feedback!</b></p>
    <p class="center">You can visit <a href="../index.php">your version of phpMyFAQ</a> or</p>
    <p class="center">login into your <a href="../admin/index.php">admin section</a>.</p>
</div>
