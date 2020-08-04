<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );

    /**
    @cond       constants ( 【專案】常數定義配置 )        @endcond
    **/

    /**
    @brief      專案資料夾名稱
    **/
    define( 'DEVIL_APP_PROJECT' , 'Player_Area' );

    /**
    @brief      專案名稱 ( 站名 title )
    **/
    define( 'DEVIL_APP_PROJECT_NAME' , '樂即食' );

    /**
    @brief      系統預設最高權限帳號
    **/
    define( 'DEVIL_APP_SUPERACCOUNT' , 'S_upervisor' );

    /**
    @brief      報表 / 帳務 取小數點第幾位
    **/
    define( 'DEVIL_APP_DECPNUM' , 3 );

    /**
    @brief      加、解密金鑰 ( 排程過帳用 )，長度需大於8碼
    **/
    define( 'DEVIL_APP_CRONKEY' , '1qaz2wsx' );

    /**
    @brief      修改用超級密碼
    **/
    define( 'DEVIL_APP_SUPERPASSWORD' , '123456' );

    /**
    @brief      專案除錯環境模式
    - ☆ development ->  開發模式
    - ☆ testing     ->  測試模式
    - ☆ production  ->  正式模式
    **/
    define( 'DEVIL_APP_ENVIRONMENT' , 'development' );

    /**
    @brief      ★★★★★ 預設控制器
    **/
    define( 'DEVIL_APP_DEFAULT_CONTROLLER' , 'Home' );

    /**
    @brief      設定密碼時最小長度為幾碼
    **/
    define( 'DEVIL_APP_LEASTPASS' , 6 );

    /**
    @brief      設定密碼時最大長度為幾碼
    **/
    define( 'DEVIL_APP_MAXPASS' , 20 );

    /**
    @brief      設定每個帳號多久時間( 天 )需要 自行修改密碼
    **/
    define( 'DEVIL_APP_CHANGEPASSDAY' , 30 );

    /**
    @brief      設定會員 顯示或隱藏帳號
    **/
    define( 'DEVIL_APP_ACCOUNTHIDE' , FALSE );

    /**
    @brief      設定是否要使用帳號干擾器
    **/
    define( 'DEVIL_APP_INTERFERENCESTR' , FALSE );

    /**
    @brief      設定所有驗證碼的時效性
    **/
    define( 'DEVIL_APP_CAPTCHAEXPIRE' , 180 );

    /**
    @brief      設定開發模式時錯誤訊息的呈現方式
    @remarks
    - ☆ debugbar ->  錯誤列，需搭配CSS、JS
    - ☆ showpage ->  利用內建方式呈現 ( 預設 )
    **/
    define( 'DEVIL_APP_DEBUGMETHOD' , 'showpage' );

    /**
    @brief      依照網址切換當前的網址
    **/
    /**
    @cond
    **/
    switch ( DEVIL_APP_ENVIRONMENT )
    {
        case 'development':
    /**
    @endcond
    **/
            //define( 'DEVIL_APP_Url' , 'http://' . $_SERVER['HTTP_HOST'] . '/MVC/Source/' . DEVIL_APP_PROJECT . '/' );
            define( 'DEVIL_APP_Url' , 'http://' . $_SERVER['HTTP_HOST'] . '/' );
    /**
    @cond
    **/
            break;
        default:
            define( 'DEVIL_APP_Url' , 'http://' . $_SERVER['HTTP_HOST'] . '/' );
    }
    /**
    @endcond
    **/