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

$c->collect();
var_Dump($c->get());

