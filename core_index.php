<?php

    /**
    @cond       檢查PHP版本是否小於 5.3.0
    **/
    if ( version_compare( PHP_VERSION , '5.3.0' , 'le' ) )
    {
        header( "Content-type: text/html; charset=utf-8" );
        exit( '您的PHP版本小於5.3.0，當前版本為： ' . PHP_VERSION );
    }
    /**
    @endcond
    **/

    /**
    @cond       如果是 Command Line Interface ( CLI ) 取不到 $_SERVER['SERVER_ADDR']，所以使用 gethostbyname( gethostname() ) 來取得服務器IP
    **/
    if ( php_sapi_name() == 'cli' )
    {
        # ----------------------------------------------------------------------
        # 取得服務器IP
        # ----------------------------------------------------------------------
        $_SERVER['SERVER_ADDR'] = gethostbyname( gethostname() );
        # ----------------------------------------------------------------------
        # 當前客戶端IP 127.0.0.1
        # ----------------------------------------------------------------------
        $_SERVER["REMOTE_ADDR"] = '127.0.0.1';
        # ----------------------------------------------------------------------
        # 當前服務器HOST
        # ----------------------------------------------------------------------
        $_SERVER['HTTP_HOST'] = 'localhost';
    }
    /**
    @endcond
    **/

    /**
    @brief      核心初始化當前路徑
    **/
    define( 'DEVIL_SYSTEM_PATH' , dirname( __FILE__ ) );

    /**
    @brief      系統核心路徑
    **/
    define( 'DEVIL_SYS_CORE_PATH' , DEVIL_SYSTEM_PATH . DIRECTORY_SEPARATOR . 'system' );

    /**
    @brief      純真IP檔案路徑
    **/
    define( 'DEVIL_SYS_CORE_QQWRY_PATH' , DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'QQWry.DAT' );

    /**
    @brief      核心字型檔案路徑
    **/
    define( 'FONTS_PATH' , DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'fonts' );

    /**
    @brief      系統核心類別路徑
    **/
    define( 'DEVIL_SYS_LIB_PATH' , DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'libraries' );

    /**
    @brief      系統核心輔助函式路徑
    **/
    define( 'DEVIL_SYS_HELPER_PATH' , DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'helpers' );

    /**
    @cond       載入全域使用的 Function
    **/
    if ( ! file_exists( DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Common.php' ) )
    {
        exit( 'Common is not exists.' );
    }
    else
    {
        require DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Common.php';
    }
    /**
    @endcond
    **/

    /**
    @cond       時區設定 ( 直接指定台灣台北 GMT+8 )
    **/
    Core_TimeZoneSet();
    /**
    @endcond
    **/

    /**
    @cond       檢查指定的 PHP ini ( mcrypt ) 是否設定
    **/
    Core_Cheak_PhpIni( "mcrypt.modes_dir" );
    /**
    @endcond
    **/

    /**
    @cond       載入全域陣列資料
    **/
    require DEVIL_SYSTEM_PATH . DIRECTORY_SEPARATOR . 'hip.php';
    /**
    @endcond
    **/