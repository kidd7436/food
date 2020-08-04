<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );

    /**
    @brief      專案路徑
    **/
    define( 'DEVIL_APP_PATH' , dirname( __FILE__ ) );

    /**
    @cond       載入專案常數設定檔
    **/
    require 'appconfig' . DIRECTORY_SEPARATOR . 'constants.php';
    /**
    @endcond
    **/

    /**
    @brief      專案資料夾路徑
    **/
    define( 'DEVIL_APP_APPLOCATION' , dirname( DEVIL_APP_PATH ) . DIRECTORY_SEPARATOR . DEVIL_APP_PROJECT . DIRECTORY_SEPARATOR );

    /**
    @brief      預設專案內樣版目錄路徑
    **/
    define( 'DEVIL_APP_TPL_PATH' , DEVIL_APP_APPLOCATION . 'views' . DIRECTORY_SEPARATOR );

    /**
    @brief      專案 URL Public 預設路徑
    **/
    define( 'DEVIL_APP_PUBLIC_URL' , DEVIL_APP_Url . 'public/' );

    /**
    @cond       載入核心起始控制項
    **/
    if ( ! file_exists( DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstart.php' ) )
    {
        exit( 'Bootstart is not exists.' );
    }
    else
    {
        require DEVIL_SYS_CORE_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Bootstart.php';
    }
    /**
    @endcond
    **/

    /**
    @cond       載入專案各項設定檔
    **/
    require 'appconfig' . DIRECTORY_SEPARATOR . 'jgconfig.php';
    /**
    @endcond
    **/

    /**
    @cond       載入專案自動加載設定檔
    **/
    require 'appconfig' . DIRECTORY_SEPARATOR . 'autoload.php';
    /**
    @endcond
    **/

    /**
    @brief      專案模組路徑
    **/
    define( 'DEVIL_APP_MODEL_PATH' , DEVIL_APP_APPLOCATION . 'models' . DIRECTORY_SEPARATOR );

    /**
    @brief      專案類別路徑
    **/
    define( 'DEVIL_APP_LIB_PATH' , DEVIL_APP_APPLOCATION . 'libraries' . DIRECTORY_SEPARATOR );

    /**
    @brief      系統核心自動加載輔助函式路徑
    **/
    define( 'DEVIL_APP_HELPER_PATH' , DEVIL_APP_APPLOCATION . 'helpers' . DIRECTORY_SEPARATOR );

    /**
    @cond       依照不同的開發模式，進階設定狀態
    **/
    if ( defined( 'DEVIL_APP_ENVIRONMENT' ) )
    {
        switch ( DEVIL_APP_ENVIRONMENT )
        {
            case 'development':
                # ----------------------------------------------------------------
                # 若為開發模式的話，則開啟錯誤提示
                # ----------------------------------------------------------------
                error_reporting(E_ALL);
                # ----------------------------------------------------------------
                # 若為開發模式，初始化除錯工具列的計算時間 變數
                # ----------------------------------------------------------------
                define( 'DEVIL_APP_JAVSCRIPT_START' , 0 );
                define( 'DEVIL_APP_JAVSCRIPT_END', 0 );
                break;

            case 'testing':
            case 'production':
                # ----------------------------------------------------------------
                # 若為測試模式或正式模式的話，則關閉錯誤提示
                # ----------------------------------------------------------------
                error_reporting( 0 );
                break;

            default:
                Core_preDie( 'The application environment is not set correctly.' );
        }
    }
    /**
    @endcond
    **/