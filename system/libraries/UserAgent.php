<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        提供函數來幫助你辨別瀏覽器資訊，例如:行動裝置或是機器人(Robot)，此外你可以取得語系與編碼資訊。
  @version      1.0.0
  @date         2015-02-28
  @since        1.0.0 -> 新增此新類別。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

class UserAgent
{
    /**
    @brief      agent
    **/
    public $agent       = null;

    /**
    @brief      is_browser
    **/
    public $is_browser  = false;

    /**
    @brief      is_robot
    **/
    public $is_robot    = false;

    /**
    @brief      is_mobile
    **/
    public $is_mobile   = false;

    /**
    @brief      languages
    **/
    public $languages   = array();

    /**
    @brief      charsets
    **/
    public $charsets    = array();

    /**
    @brief      platforms
    **/
    public $platforms   = array();

    /**
    @brief      browsers
    **/
    public $browsers    = array();

    /**
    @brief      mobiles
    **/
    public $mobiles     = array();

    /**
    @brief      robots
    **/
    public $robots      = array();

    /**
    @brief      platform
    **/
    public $platform    = '';

    /**
    @brief      browser
    **/
    public $browser     = '';

    /**
    @brief      version
    **/
    public $version     = '';

    /**
    @brief      mobile
    **/
    public $mobile      = '';

    /**
    @brief      robot
    **/
    public $robot       = '';

    /**
    @brief      proxy 使用者網路資訊
    **/
    public $proxy       = "";

    /**
    @brief      ip
    **/
    public $ip          = "";

    /**
    @cond      建構子
    @remarks    利用建構子來初始化設定 agent、proxy、ip 資料。
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 判斷當前的HTTP標頭有無HTTP_USER_AGENT
        # ----------------------------------------------------------------------
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )
        {
            $this->agent = trim( $_SERVER['HTTP_USER_AGENT'] );
        }
        # ----------------------------------------------------------------------
        # 如果當前Calss的agent參數為Null則使用 _load_agent_file() 來重新取得
        # ----------------------------------------------------------------------
        if ( ! is_null( $this->agent ) )
        {
            if ( $this->_load_agent_file() )
            {
                $this->_compile_data();
            }
        }
        # ----------------------------------------------------------------------
        # IP位置 或使用 代理伺服器的IP位置
        # ----------------------------------------------------------------------
        if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) )
        {
            $this->proxy = $_SERVER["REMOTE_ADDR"];
            $this->ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else
        {
            $this->proxy = '';
            $this->ip = $_SERVER["REMOTE_ADDR"];
        }
    }
    /**
    @endcond
    **/

    /**
    @brief      取得客戶端 Browser 資訊
    **/
    private function _load_agent_file()
    {
        if ( is_file( DEVIL_APP_APPLOCATION . 'appconfig\user_agents.php' ) )
        {
            include( DEVIL_APP_APPLOCATION . 'appconfig\user_agents.php' );
        }
        else
        {
            return false;
        }

        $return = false;
        # ----------------------------------------------------------------------
        # 判斷正在造訪你的網站的平台名稱 (例如: Linux，Windows，OS X)
        # ----------------------------------------------------------------------
        if ( isset( $platforms ) )
        {
            $this->platforms = $platforms;
            unset( $platforms );
            $return = true;
        }
        # ----------------------------------------------------------------------
        # 判斷正在造訪你的網站的瀏覽器名稱
        # ----------------------------------------------------------------------
        if ( isset( $browsers ) )
        {
            $this->browsers = $browsers;
            unset( $browsers );
            $return = true;
        }
        # ----------------------------------------------------------------------
        # 判斷正在造訪你的網站的行動裝置名稱
        # ----------------------------------------------------------------------
        if (isset($mobiles))
        {
            $this->mobiles = $mobiles;
            unset( $mobiles) ;
            $return = true;
        }
        # ----------------------------------------------------------------------
        # 判斷正在造訪你的網站的機器人名稱
        # ----------------------------------------------------------------------
        if ( isset( $robots ) )
        {
            $this->robots = $robots;
            unset( $robots );
            $return = true;
        }

        return $return;
    }

    /**
    @brief      開始處理每一個 FUNCTION
    **/
    private function _compile_data()
    {
        $this->_set_platform();

        foreach ( array('_set_robot', '_set_browser', '_set_mobile' ) as $function )
        {
            if ( $this->$function() === true )
            {
                break;
            }
        }
    }

    /**
    @brief      設定作業系統
    **/
    private function _set_platform()
    {
        if ( is_array( $this->platforms ) AND count( $this->platforms ) > 0 )
        {
            foreach ( $this->platforms as $key => $val )
            {
                if ( preg_match( "|" . preg_quote( $key ) . "|i" , $this->agent ) )
                {
                    $this->platform = $val;
                    return true;
                }
            }
        }
        $this->platform = 'Unknown Platform';
    }

    /**
    @brief      存放當前瀏覽器資訊
    @retval     Boolen
    **/
    private function _set_browser()
    {
        if ( is_array( $this->browsers) && count( $this->browsers ) > 0 )
        {
            foreach ( $this->browsers as $key => $val )
            {
                if ( preg_match( "|" . preg_quote( $key ) . ".*?([0-9\.]+)|i" , $this->agent , $match ) )
                {
                    $this->is_browser = true;
                    $this->version = $match[1];
                    $this->browser = $val;
                    $this->_set_mobile();
                    return true;
                }
            }
        }
        return false;
    }

    /**
    @brief      存放當前瀏覽器裝置資訊
    @retval     Boolen
    **/
    private function _set_robot()
    {
        if ( is_array( $this->robots ) && count( $this->robots ) > 0)
        {
            foreach ( $this->robots as $key => $val )
            {
                if ( preg_match( "|" . preg_quote( $key ) . "|i", $this->agent ) )
                {
                    $this->is_robot = true;
                    $this->robot = $val;
                    return true;
                }
            }
        }
        return false;
    }

    /**
    @brief      存放是否為移動裝置設備
    @retval     Boolen
    **/
    private function _set_mobile()
    {
        if ( is_array( $this->mobiles ) && count( $this->mobiles ) > 0 )
        {
            foreach ( $this->mobiles as $key => $val )
            {
                if ( false !== ( strpos( strtolower( $this->agent ) , $key) ) )
                {
                    $this->is_mobile = true;
                    $this->mobile = $val;
                    return true;
                }
            }
        }
        return false;
    }

    /**
    @brief      設置接受的語言 ( Set the accepted languages. )
    @retval     Void
    **/
    private function _set_languages()
    {
        if ( ( count( $this->languages ) == 0 ) && isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '' )
        {
            $languages = preg_replace( '/(;q=[0-9\.]+)/i' , '' , strtolower( trim( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) );

            $this->languages = explode( ',' , $languages );
        }

        if ( count( $this->languages ) == 0 )
        {
            $this->languages = array('Undefined');
        }
    }

    /**
    @brief      Set the accepted character sets
    @retval     Void
    **/
    private function _set_charsets()
    {
        if ( ( count( $this->charsets ) == 0 ) && isset( $_SERVER['HTTP_ACCEPT_CHARSET'] ) && $_SERVER['HTTP_ACCEPT_CHARSET'] != '' )
        {
            $charsets = preg_replace( '/(;q=.+)/i' , '' , strtolower( trim( $_SERVER['HTTP_ACCEPT_CHARSET'] ) ) );

            $this->charsets = explode( ',' , $charsets );
        }

        if ( count( $this->charsets ) == 0 )
        {
            $this->charsets = array( 'Undefined' );
        }
    }

    /**
    @brief      判別是否為已知的瀏覽器，回傳 TRUE 或 FALSE
    @remarks    注意: 在這個範例中 "Safari" 這個字是你的瀏覽器清單中的一個陣列的索引值，你可以在 專案/appconfig/user_agents.php 找到瀏覽器清單
    @code{.unparsed}
    if ( $this->Core_UserAgent->is_browser( 'Safari' ) )
    {
        echo 'You are using Safari.';
    }
    else if ( $this->Core_UserAgent->is_browser() )
    {
        echo 'You are using a browser.';
    }
    @endcode
    @retval     Bool
    **/
    public function is_browser( $key = null )
    {
        if ( ! $this->is_browser)
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # No need to be specific, it's a browser
        # ----------------------------------------------------------------------
        if ( $key === null )
        {
            return true;
        }
        # ----------------------------------------------------------------------
        # Check for a specific browser
        # ----------------------------------------------------------------------
        return array_key_exists( $key, $this->browsers ) && $this->browser === $this->browsers[$key];
    }

    /**
    @brief      判別是否為已知的機器人，回傳 TRUE 或 FALSE
    @code
    注意:  機器人清單中只包含了幾種最常見的機器人，你可以在 專案/appconfig/user_agents.php 新增新的機器人
    @endcode
    @retval     Bool
    **/
    public function is_robot( $key = null )
    {
        if ( ! $this->is_robot )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # No need to be specific, it's a robot
        # ----------------------------------------------------------------------
        if ( $key === null )
        {
            return true;
        }
        # ----------------------------------------------------------------------
        # Check for a specific robot
        # ----------------------------------------------------------------------
        return array_key_exists( $key, $this->robots ) && $this->robot === $this->robots[$key];
    }

    /**
    @brief      判別是否為已知的行動裝置，回傳 TRUE 或 FALSE
    @retval     Bool
    **/
    public function is_mobile( $key = null )
    {
        if ( ! $this->is_mobile )
        {
            return false;
        }
        # ----------------------------------------------------------------------
        # No need to be specific, it's a mobile
        # ----------------------------------------------------------------------
        if ( $key === null )
        {
            return true;
        }
        # ----------------------------------------------------------------------
        # Check for a specific robot
        # ----------------------------------------------------------------------
        return array_key_exists( $key , $this->mobiles ) && $this->mobile === $this->mobiles[$key];
    }

    /**
    @brief      判斷使用者是否從其它網站過來，回傳 TRUE 或 FALSE
    @retval     Bool
    **/
    public function is_referral()
    {
        if ( ! isset( $_SERVER['HTTP_REFERER'] ) || $_SERVER['HTTP_REFERER'] == '' )
        {
            return false;
        }
        return true;
    }

    /**
    @brief      取得完整的使用者代理(user agent)資訊，例如:
    @code
    Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.0.4) Gecko/20060613 Camino/1.0.2
    @endcode
    @retval     String
    **/
    public function agent_string()
    {
        return $this->agent;
    }

    /**
    @brief      回傳正在造訪你的網站的平台名稱 (例如: Linux，Windows，OS X)
    @retval     String
    **/
    public function platform()
    {
        return $this->platform;
    }

    /**
    @brief      回傳正在造訪你的網站的瀏覽器名稱
    @retval     String
    **/
    public function browser()
    {
        return $this->browser;
    }

    /**
    @brief      回傳正在造訪你的網站的瀏覽器版本
    @retval     String
    **/
    public function version()
    {
        return $this->version;
    }

    /**
    @brief      回傳正在造訪你的網站的機器人名稱
    @retval     String
    **/
    public function robot()
    {
        return $this->robot;
    }

    /**
    @brief      回傳正在造訪你的網站的行動裝置名稱
    @retval     String
    **/
    public function mobile()
    {
        return $this->mobile;
    }

    /**
    @brief      假如使用者是從其它網站造訪，你可以取得 referrer 資訊:
    @code{.unparsed}
    if ( $this->Core_UserAgent->is_referral() )
    {
        echo $this->Core_UserAgent->referrer();
    }
    @endcode
    @retval     String
    **/
    public function referrer()
    {
        return ( ! isset( $_SERVER['HTTP_REFERER'] ) || $_SERVER['HTTP_REFERER'] == '' ) ? '' : trim( $_SERVER['HTTP_REFERER'] );
    }

    /**
    @brief      Get the accepted languages
    @retval     Array
    **/
    public function languages()
    {
        if ( count( $this->languages ) == 0 )
        {
            $this->_set_languages();
        }

        return $this->languages;
    }

    /**
    @brief      Get the accepted Character Sets
    @return     Array
    **/
    public function charsets()
    {
        if ( count( $this->charsets ) == 0 )
        {
            $this->_set_charsets();
        }

        return $this->charsets;
    }

    /**
    @brief      如果使用者代理(user agent)接受特定語系，則你可以決定:
    @remarks    注意: 這個函數並不是那麼精確的，因為瀏覽器並不見得會提供有關語系的資訊
    @code{.unparsed}
    if ( $this->Core_UserAgent->accept_lang('en') )
    {
        echo 'You accept English!';
    }
    @endcode
    @retval     Bool
    **/
    public function accept_lang( $lang = 'en' )
    {
        return ( in_array( strtolower( $lang ) , $this->languages() , true ) );
    }

    /**
    @brief      如果使用者代理(user agent)接受特定編碼，則你可以決定:
    @remarks    注意: 這個函數並不是那麼精確的，因為瀏覽器並不見得會提供有關編碼的資訊
    @code{.unparsed}
    if ( $this->Core_UserAgent->accept_charset('utf-8') )
    {
        echo 'You browser supports UTF-8!';
    }
    @endcode
    @retval     Bool
    **/
    public function accept_charset( $charset = 'utf-8' )
    {
        return ( in_array( strtolower( $charset ) , $this->charsets() , true ) );
    }
}