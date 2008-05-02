<?Php
include('questionnaire.php');

class PMF_Dat extends Questionnaire_Data {
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
	parent::__construct();
        $this->config = $config;
        $this->config['oldversion'] = $oldversion;
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
    function collectphpMyFAQInfo()
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

$q = new PMF_Dat(array(), 1);
$q->collect();
var_Dump($q->get());


