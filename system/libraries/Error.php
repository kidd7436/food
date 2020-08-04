<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        錯誤處理類，將捕捉到的錯誤另外呈現。
  @version      1.0.0
  @date         2015-02-27
  @since        1.0.0 -> 新增此新類別。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

class Error
{
    /**
    @brief      Nesting level of the output buffering mechanism. Type: Int
    **/
    public $ob_level;

    /**
    @cond       建構子
    @remarks    取得當前服務器的緩衝機制級別
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # Note:  Do not log messages from this constructor.
        # ----------------------------------------------------------------------
        $this->ob_level = ob_get_level();
    }
    /**
    @endcond
    **/

    /**
    @brief      404找不到頁面 ( 除了測試環境外，一律導回預設控制器 )
    @param      String      $page the page
    @param      Boolen      $log_error log error yes/no
    @retval     String
    **/
    public function show_404( $page = '' , $log_error = true )
    {
        # ----------------------------------------------------------------------
        # 如果是正式上線模式，錯誤訊息不顯示
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT == 'production' )
        {
            Core_Redirect( DEVIL_APP_DEFAULT_CONTROLLER );
        }
        else
        {
            echo $this->show_error( "404 Page Not Found" , "The page you requested was not found." , 'error_404' , 404 );
          	exit;
        }
    }

    /**
    @brief      一般錯誤頁面 ( 除了測試環境外，一律導回預設控制器 )
    @param      String      $heading the heading
    @param      String      $message the message
    @param      String      $template the template name
    @param      Int         $status_code the status code
    @retval     String
    **/
    public function show_error( $heading , $message , $template = 'error_general' , $status_code = 500 )
    {
        if ( php_sapi_name() == 'cli' )
        {
            # ------------------------------------------------------------------
            # 輸出錯誤訊息，且刪除html標籤
            # ------------------------------------------------------------------
            exit( strip_tags( $message ) );
        }
        # ----------------------------------------------------------------------
        # 如果是正式上線模式，錯誤訊息不顯示
        # ----------------------------------------------------------------------
        if ( DEVIL_APP_ENVIRONMENT == 'production' )
        {
            Core_Redirect( DEVIL_APP_DEFAULT_CONTROLLER );
        }
        else
        {
            $this->set_status_header( $status_code );

            $message = '<p>' . implode( '</p><p>' , ( ! is_array( $message ) ) ? array( $message ) : $message ).'</p>';

            if ( ob_get_level() > $this->ob_level + 1  )
            {
                ob_end_flush();
            }
            ob_start();
            include( DEVIL_SYS_LIB_PATH . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . $template . '.php' );
            $buffer = ob_get_contents();
            ob_end_clean();
            return $buffer;
        }
    }

    /**
    @brief      原生的PHP錯誤處理程序
    @param      string      $severity the error severity
    @param      string      $message the error string
    @param      string      $filepath the error filepath
    @param      string      $line the error line number
    @return     string
    **/
    public function show_php_error( $severity , $message , $filepath , $line )
    {
        #$severity = ( ! isset( $this->levels[$severity] ) ) ? $severity : $this->levels[$severity];
        # ----------------------------------------------------------------------
        # 替換反斜線
        # ----------------------------------------------------------------------
        $filepath = str_replace( "\\", "/", $filepath );
        # ----------------------------------------------------------------------
        # 出于安全考虑，不显示完整的文件路径
        # ----------------------------------------------------------------------
        if ( FALSE !== strpos( $filepath , '/' ) )
        {
            $x = explode( '/' , $filepath );
            $filepath = $x[count( $x ) - 2] . '/' . end( $x );
        }

        if ( ob_get_level() > $this->ob_level + 1 )
        {
            ob_end_flush();
        }
        ob_start();
        include( DEVIL_SYS_LIB_PATH . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR . 'error_php.php' );
        $buffer = ob_get_contents();
        ob_end_clean();
        exit( $buffer );
    }

    /**
    @brief      Set HTTP Status Header
    @param      Int     $code the status code
    @param      String  $text tex
    @return     Void
    **/
    public function set_status_header( $code = 200 , $text = '' )
    {
        $stati = array
        (
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',

            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        if ( $code == '' || ! is_numeric( $code ) )
        {
            self::show_error( 'Status codes must be numeric' , 500 );
        }

        if ( isset( $stati[$code] ) && $text == '' )
        {
            $text = $stati[$code];
        }

        if ( $text == '' )
        {
            self::show_error( 'No status text available.  Please check your status code number or supply your own message text.' , 500 );
        }

        $server_protocol = ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) ? $_SERVER['SERVER_PROTOCOL'] : false;

        if ( substr( php_sapi_name() , 0 , 3 ) == 'cgi' )
        {
            header( "Status: {$code} {$text}" , true );
        }
        elseif ( $server_protocol == 'HTTP/1.1' || $server_protocol == 'HTTP/1.0' )
        {
            header( $server_protocol." {$code} {$text}" , true , $code );
        }
        else
        {
            header( "HTTP/1.1 {$code} {$text}" , true , $code );
        }
    }
}