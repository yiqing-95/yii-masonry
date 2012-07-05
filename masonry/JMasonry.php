<?php
/**
 * User: yiqing
 * Date: 12-7-5
 * Time: 上午10:59
 * To change this template use File | Settings | File Templates.
 *------------------------------------------------------------
 *------------------------------------------------------------
 */
class JMasonry extends CWidget
{


    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var bool
     */
    public $debug = YII_DEBUG;

    /**
     * @var \CClientScript
     */
    protected $cs;

    /**
     * @var array|string
     * -------------------------
     * the options will be passed to the underlying plugin
     *   eg:  js:{key:val,k2:v2...}
     *   array('key'=>$val,'k'=>v2);
     * -------------------------
     */
    public $options = array();


    /**
     * @var array
     * the settings for using the plugin
     * eg:
     * .item =>
     * ' {
     *      width: 220px;
     *      margin: 10px;
     *      float: left;
     *     } '
     */
    public $cssOptions = array();

    /**
     * @var string
     */
    public $container;

    /**
     * @return JMasonry
     */
    public function publishAssets()
    {
        if (empty($this->baseUrl)) {
            $assetsPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
            if ($this->debug == true) {
                $this->baseUrl = Yii::app()->assetManager->publish($assetsPath, false, -1, true);
            } else {
                $this->baseUrl = Yii::app()->assetManager->publish($assetsPath);
            }
        }
        return $this;
    }


    /**
     *
     */
    public function init()
    {

        parent::init();

        $this->cs = Yii::app()->getClientScript();
        // publish assets and register css/js files
        $this->publishAssets();
        // register necessary js file and css files
        $this->cs->registerCoreScript('jquery');

        if ($this->debug) {
            $this->registerScriptFile('jquery.masonry.js', CClientScript::POS_HEAD);
        } else {
            $this->registerScriptFile('jquery.masonry.min.js', CClientScript::POS_HEAD);
        }

        /**
         * if you give some css settings i will register for you ,
         * surely you can do it yourself !
         */
        if (!empty($this->cssOptions)) {
            $cssStr = '';
            if (is_string($this->cssOptions)) {
                $cssStr = $this->cssOptions;
            } elseif (is_array($this->cssOptions)) {
                foreach ($this->cssOptions as $cssSelector => $cssRules) {
                    $cssStr .= "\n {$cssSelector} ";
                    if (is_string($cssRules)) {
                        $cssStr .= "{$cssRules} \n ";
                    } elseif (is_array($cssRules)) {
                        $cssRules = $this->genCssFromArray($cssRules);
                        $cssStr .= "{$cssRules} \n ";
                    } else {
                        throw new InvalidArgumentException(" cssRules must be string or array . cssOptions is invalidate ");
                    }
                }
            }else{
                throw new InvalidArgumentException(" cssOptions must be string or array  ");
            }
          $this->cs->registerCss(__CLASS__.__FUNCTION__.$this->getId(),$cssStr);
        }

        if (empty($this->container)) {
            //just register the necessary css and js files ; you want use it manually
            return;
        }

        $options = empty($this->options) ? '' : CJavaScript::encode($this->options);

        $jsSetup = <<<JS_INIT
         $("{$this->container}").masonry({$options});
JS_INIT;
        $this->cs->registerScript(__CLASS__ . '#' . $this->getId(), $jsSetup, CClientScript::POS_READY);

    }


    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        try {
            //shouldn't swallow the parent ' __set operation
            parent::__set($name, $value);
        } catch (Exception $e) {
            $this->options[$name] = $value;
        }
    }

    /**
     * @param $fileName
     * @param int $position
     * @return JMasonry
     * @throws InvalidArgumentException
     */
    protected function registerScriptFile($fileName, $position = CClientScript::POS_END)
    {
        if (is_string($fileName)) {
            $jsFiles = explode(',', $fileName);
        } elseif (is_array($fileName)) {
            $jsFiles = $fileName;
        } else {
            throw new InvalidArgumentException('you must give a string or array as first argument , but now you give' . var_export($fileName, true));
        }
        foreach ($jsFiles as $jsFile) {
            $jsFile = trim($jsFile);
            $this->cs->registerScriptFile($this->baseUrl . '/' . ltrim($jsFile, '/'), $position);
        }
        return $this;
    }

    /**
     * @param $fileName
     * @return JMasonry
     * @throws InvalidArgumentException
     */
    protected function registerCssFile($fileName)
    {
        $cssFiles = func_get_args();
        foreach ($cssFiles as $cssFile) {
            if (is_string($cssFile)) {
                $cssFiles2 = explode(',', $cssFile);
            } elseif (is_array($cssFile)) {
                $cssFiles2 = $cssFile;
            } else {
                throw new InvalidArgumentException('you must give a string or array as first argument , but now you give' . var_export($cssFiles, true));
            }
            foreach ($cssFiles2 as $css) {
                $this->cs->registerCssFile($this->baseUrl . '/' . ltrim($css, '/'));
            }
        }
        // $this->cs->registerCssFile($this->assetsUrl . '/vendors/' .$fileName);
        return $this;
    }

    /**
     * use an array to generate  Css  code
     * @param array $cssSettings
     * @param bool $withCurlyBrace   whether close with curlyBrace
     * @return string
     */
    public function genCssFromArray($cssSettings = array(), $withCurlyBrace = true)
    {
        $cssCodes = '';
        foreach ($cssSettings as $k => $v) {
            $cssCodes .= "{$k}:{$v}; \n";
        }
        if ($withCurlyBrace === true) {
            $cssCodes = '{' . "\n" . $cssCodes . '}';
        }
        return $cssCodes;
    }

    /**
     * parse the css code  to php array
     * @param string $cssString
     * @return array
     */
    public function getArrayFromCssString($cssString = '')
    {
        $rtn = array();
        //remove  {   and  }  if exists
        $cssString = rtrim(trim($cssString), '}');
        $cssString = ltrim($cssString, '{');
        //remove  all comments and space
        $text = preg_replace('!/\*.*?\*/!s', '', $cssString);
        $text = preg_replace('/\n\s*\n/', "", $text);
        // pairs handle
        $pairs = explode(';', $text);
        foreach ($pairs as $pair) {
            $colonPos = strpos($pair, ':');
            if (($k = trim(substr($pair, 0, $colonPos))) !== '') {
                $rtn[$k] = substr($pair, $colonPos + 1);
            }
        }
        return $rtn;
    }

    /**
     * @static
     * @param bool $hashByName
     * @return string
     * return this widget assetsUrl
     */
    public static function getAssetsUrl($hashByName = false)
    {
        // return CHtml::asset(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets', $hashByName);
        return Yii::app()->getAssetManager()->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets', $hashByName, -1, YII_DEBUG);
    }
}