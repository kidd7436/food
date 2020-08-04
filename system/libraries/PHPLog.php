<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        PHPLog類，用來處理錯誤訊息。
  @version      1.0.0
  @date         2015-03-28
  @since        1.0.0 -> 新增此新類別。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

class PHPLog
{
    /**
    @brief      PHP 四種錯誤等級
    @see        http://blog.xuite.net/hero82103350/blog/36199649-%5B%E8%BD%89%E8%B2%BC%5D+PHP++%E5%9B%9B%E7%A8%AE%E9%8C%AF%E8%AA%A4%E7%AD%89%E7%B4%9A
    **/
    private static $levels = array
    (
        E_ERROR             => 'Error',                                         #  值 1     , 執行時錯誤
        E_WARNING           => 'Warning',                                       #  值 2     , 執行時錯誤
        E_PARSE             => 'Parsing Error',                                 #  值 4     , 執行時錯誤
        E_NOTICE            => 'Notice',                                        #  值 8     , 執行時錯誤
        E_CORE_ERROR        => 'Core Error',                                    #  值 16    , 執行時錯誤
        E_CORE_WARNING      => 'Core Warning',                                  #  值 32    , 執行時錯誤
        E_COMPILE_ERROR     => 'Compile Error',                                 #  值 64    , 執行時錯誤
        E_COMPILE_WARNING   => 'Compile Warning',                               #  值 128   , 執行時錯誤
        E_USER_ERROR        => 'User Error',                                    #  值 256   , 執行時錯誤
        E_USER_WARNING      => 'User Warning',                                  #  值 512   , 執行時錯誤
        E_USER_NOTICE       => 'User Notice',                                   #  值 1024  , 執行時錯誤
        E_STRICT            => 'Runtime Notice',                                #  值 2048  , 執行時錯誤
        E_RECOVERABLE_ERROR => 'Catchable error' ,                              #  值 4096  , 執行時錯誤
        E_DEPRECATED        => 'Runtime Notice',                                #  值 16384 , 執行時錯誤  since PHP 5.3.0
        E_USER_DEPRECATED   => 'User Warning'                                   #  值 30719 , 執行時錯誤  since PHP 5.3.0
    );

    /**
    @cond       建構子
    @remarks    利用建構時將全域的錯誤訊息集中至此處理。
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 另外處理PHP的錯誤訊息
        # ----------------------------------------------------------------------
        set_error_handler( array( $this , 'error_handler' ) );
        # ----------------------------------------------------------------------
        # 當有使用try cach 時捕捉到的錯誤訊息
        # ----------------------------------------------------------------------
        set_exception_handler( array( $this , 'exception_handler' ) );
    }
    /**
    @endcond
    **/

    /**
    @brief      PHP Error Handler
    @param      Int         $severity 錯誤層級
    @param      String      $message 錯誤訊息
    @param      String      $filepath 錯誤檔案路徑
    @param      Int         $line 錯誤行數
    @retval     Void
    **/
    public function error_handler( $severity , $message , $filepath , $line )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $tmpStr = '';
        # ----------------------------------------------------------------------
        # 錯誤類型
        # ----------------------------------------------------------------------
        $errtype = isset( self::$levels[$severity] ) ? self::$levels[$severity] : $severity;
        # ----------------------------------------------------------------------
        # 依照不同的開發模式處理不同錯誤訊息
        # ----------------------------------------------------------------------
        if ( defined( 'DEVIL_APP_ENVIRONMENT' ) )
        {
            switch ( DEVIL_APP_ENVIRONMENT )
            {
              	case 'development':
                    # ----------------------------------------------------------
                    # 依照輸出錯誤的設定方式來呈現錯誤
                    # ----------------------------------------------------------
                    switch( DEVIL_APP_DEBUGMETHOD )
                    {
                        # ------------------------------------------------------
                        # 原始方式
                        # ------------------------------------------------------
                        default:
                        case 'showpage':
                            Bootstart::$_lib['Core_Error']->show_php_error( $errtype , $message , $filepath , $line );
                            break;
                        # ------------------------------------------------------
                        # 除錯列方式
                        # ------------------------------------------------------
                        case 'debugbar':
                            list ( $PHP_usec , $PHP_sec ) = explode( ' ' , microtime() );
                            $data = array
                            (
                                'errno' => $severity ,
                                'errtype' => $errtype ,
                                'errstr' => $message ,
                                'errfile' => $filepath ,
                                'errline' => $line ,
                                'time' => date('Y-m-d H:i:s')
                            );
                            Bootstart::$_debug->addMessage( 'warnings' , 'PHPEH-' . $PHP_usec , $data );
                            break;
                    }
              	    break;
              	case 'testing':
              	case 'production':
                    $tmpStr .= 'PHPERH :' . $filepath . '|errline:' . $line . '|errstr:' . $message . "\n";
                    error_log( $tmpStr );
              	    break;
              	default:
                    Core_preDie( 'The application environment is not set correctly.' );
            }
        }
        else
        {
            $tmpStr .= 'PHPERH :' . $filepath . '|errline:' . $line . '|errstr:' . $message . "\n";
            error_log( $tmpStr );
        }
    }

    /**
    @brief      PHP Error Handler
    @param      Object      $exception 拋出的錯誤
    @return     Void
    **/
    public function exception_handler( $exception )
    {
        # ----------------------------------------------------------------------
        # 取出資料庫名稱 ( 使用 Master的 )
        # ----------------------------------------------------------------------
        $MasterDb_ConfigArr = Core_LoadBootStatic( '_config' , 'masterdb' );
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $tmpStr = '';
        # ----------------------------------------------------------------------
        # 如果有讓資料庫中斷的語法，直接忽略
        # ----------------------------------------------------------------------
        if ( $exception->getMessage() == "SQLSTATE[42S02]: Base table or view not found: 1146 Table '" . $MasterDb_ConfigArr['db_database'] . ".XXX' doesn't exist" )
        {
            return;
        }
        else
        {
            self::error_handler
            (
                $exception->getCode() ,
                $exception->getMessage() ,
                $exception->getFile() ,
                $exception->getLine()
            );
        }
    }
}