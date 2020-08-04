<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        ★★★★★ 核心 View ( 輸出父類別-抽象類別 )
  @version      1.0.0
  @date         2015-03-28 by Vam
  @since        1.0.0 -> 新增此新類別。
**/

abstract class View
{
    /**
    @cond       建構子
    @remarks    利用建構子產生類別
    **/
    public function __construct()
    {
        //
    }
    /**
    @endcond
    **/

    /**
    @cond       抽象：輸出結果
    **/
    abstract public function render();
    /**
    @endcond
    **/
}