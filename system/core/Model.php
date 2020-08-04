<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        ★★★★★ 核心 Model ( 模組資料父類別-保留用 )
  @version      1.0.0
  @date         2015-03-26 by Vam
  @since        1.0.0 -> 新增此新類別。
**/

class Model
{
    /**
    @brief      存放 Bootstart::$_lib['Core_Pdo_Driver'] 。
    **/
    public $db = null;

    /**
    @cond       建構子
    **/
    public function __construct()
    {
        # ----------------------------------------------------------------------
        # 如果當前類別為 null 就將 Core_Pdo_Driver 的類別存入
        # ----------------------------------------------------------------------
        if ( $this->db === null )
        {
            $this->db = Bootstart::$_lib['Core_Pdo_Driver'];
        }
    }
    /**
    @endcond
    **/
}