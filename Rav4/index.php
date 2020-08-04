<?php
    /**
    @brief       PHP 最大執行時間，網管於 PHP.ini 設定 900 秒
    **/
    ini_set( "max_execution_time" , 900 );

    /**
    @brief       引入系統核心入口
    **/
    if ( ! file_exists( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core_index.php' ) )
    {
        exit( 'core_index is not exists.' );
    }
    else
    {
        require dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core_index.php';
    }

    /**
    @brief       引入專案初始化程序
    **/
    if ( ! file_exists( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'initialization.php' ) )
    {
        exit( 'initialization is not exists.' );
    }
    else
    {
        require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'initialization.php';
    }

    /**
    @brief       檢查加解密金鑰長度是否小於8碼
    **/
    if ( strlen( DEVIL_APP_CRONKEY ) < 8 )
    {
        exit( 'The Key『DEVIL_APP_CRONKEY』length must be greater than 8.' );
    }

    /**
    @brief       執行核心流程
    **/
    Bootstart::run( $config , $autoload , $_hipArr );

    /**
    @brief       結束程序
    **/
    exit;