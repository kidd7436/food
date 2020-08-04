<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );

    /**
    @cond       autoload ( 專案自動載入配置文件 )         @endcond
    **/

    /**
    @brief      要自動載入的類別 (Libraries)
    @code{.unparsed}
    $autoload['libraries'] = array( 'database' , 'Session' );
    @endcode
    **/
    $autoload['libraries'] = array( "HtmlView", "Navigation", "HtmlTable" );

    /**
    @brief      要自動載入的輔助函式 (Helper Files)
    @code{.unparsed}
    $autoload['helper'] = array( 'url' , 'file' );
    @endcode
    **/
    $autoload['helpers'] = array();

    /**
    @brief      要自動載入的模型 (Models)
    @code{.unparsed}
    $autoload['model'] = array( 'model1' , 'model2' );
    @endcode
    **/
    $autoload['models'] = array();