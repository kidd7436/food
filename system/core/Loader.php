<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        ★★★★★ 核心 讀取器 (Loader) 用來讀取元件. 這些元件可以是libraries、Helpers、Models。
  @version      1.0.0
  @date         2015-03-18
  @since        1.0.0 -> 新增此新類別。
  @attention    注意: 此類別由系統自動初始化所以不需要手動載入。
**/

final class Loader
{
    /**
    @brief 		取得 Bootstart library 靜態物件陣列，並將物件設定到當前的 Controller
    @param      String      $library_name   類名稱
    @param      String      $_path          類路徑
    @retval     Object
    @code{.unparsed}
    $this->Core_Loader->library( 'HtmlTable' );
    @endcode
    @code{.unparsed}
    $this->Core_Loader->library( array( 'HtmlForm' , 'HtmlTable' ) );
    @endcode
    **/
    public function library( $library_name , $_path = '' )
    {
        # ----------------------------------------------------------------------
        # 如果傳入的 $library_name 為空，則提示錯誤
        # ----------------------------------------------------------------------
        if ( empty( $library_name ) )
        {
            show_error( '您所加載的類別名稱不可為空。' );
        }
        # ----------------------------------------------------------------------
        # 如果傳入的 $library_name 為array，則遞回呼叫自己處理
        # ----------------------------------------------------------------------
        elseif ( is_array( $library_name ) )
        {
            foreach ( $library_name as $key => $val )
            {
                self::library( $val , $_path );
            }
        }
        # ----------------------------------------------------------------------
        # 如果已存在靜態變數陣列中，代表此類別已實體化
        # ----------------------------------------------------------------------
        elseif ( isset( Bootstart::$_lib[$library_name] ) || isset( Bootstart::$_lib[Bootstart::$_CoreNameSpaces . $library_name] ) )
        {
            # ------------------------------------------------------------------
            # 判斷當前執行控制器的 __initialization 方法是否存在，因為各個控制器會繼承父類別 Controller，__initialization 則是父類別中的方法
            # ------------------------------------------------------------------
            if ( method_exists( Bootstart::$_Controller_Object , '__initialization' ) )
            {
                Bootstart::$_Controller_Object->__initialization();
            }
            else
            {
                #Controller::__initialization();
            }
        }
        else
        {
            # ------------------------------------------------------------------
            # 執行手動載入
            # ------------------------------------------------------------------
            $classTmp = Bootstart::newLib( $library_name , $_path );
            # ------------------------------------------------------------------
            # 因為專案內的 Class 名稱會以 My_ 當開頭，所以此處用內建 Function 取得類的名稱
            # ------------------------------------------------------------------
            self::library( get_class( $classTmp ) );
        }
    }

    /**
    @brief      取得 Bootstart Model 靜態物件陣列，並將物件設定到當前的 Controller
    @retval     Object
    @code{.unparsed}
    $this->Core_Loader->model( 'Users' );
    @endcode
    @code{.unparsed}
    $this->Core_Loader->model( array( 'Users' , 'KConfig' ) );
    @endcode
    **/
    public function model( $model_name , $_path = '' )
    {
        # ----------------------------------------------------------------------
        # 如果傳入的 $model_name 為空，則提示錯誤
        # ----------------------------------------------------------------------
        if ( empty( $model_name ) )
        {
            show_error( '您所加載的模組名稱不可為空。' );
        }
        # ----------------------------------------------------------------------
        # 如果傳入的 $model_name 為array，則遞回呼叫自己處理
        # ----------------------------------------------------------------------
        elseif ( is_array( $model_name ) )
        {
            foreach ( $model_name as $key => $val )
            {
                self::model( $val );
            }
        }
        # ----------------------------------------------------------------------
        # 如果已存在靜態變數陣列中，代表此類別已實體化
        # ----------------------------------------------------------------------
        elseif ( isset( Bootstart::$_mod[$model_name . '_Model'] ) )
        {
            # ------------------------------------------------------------------
            # 判斷當前執行控制器的 __initialization 方法是否存在，因為各個控制器會繼承父類別 Controller，__initialization 則是父類別中的方法
            # ------------------------------------------------------------------
            if ( method_exists( Bootstart::$_Controller_Object , '__initialization' ) )
            {
                Bootstart::$_Controller_Object->__initialization();
            }
            else
            {
                #Controller::__initialization();
            }
        }
        else
        {
            Bootstart::newModel( $model_name );
            self::model( $model_name );
        }
    }

    /**
    @brief      取得 Bootstart Library 靜態物件陣列
    @retval     Object
    @code{.unparsed}
    $this->Core_Loader->helper( 'number' );
    @endcode
    @code{.unparsed}
    $this->Core_Loader->helper( array( 'number' , 'string' ) );
    @endcode
    **/
    public function helper( $helper_name )
    {
        # ----------------------------------------------------------------------
        # 如果傳入的 $helper_name 為空，則提示錯誤
        # ----------------------------------------------------------------------
        if ( empty( $helper_name ) )
        {
            show_error('您所加載的輔助函式名稱不可為空。');
        }
        # ----------------------------------------------------------------------
        # 如果傳入的 $helper_name 為array，則遞回呼叫自己處理
        # ----------------------------------------------------------------------
        elseif ( is_array( $helper_name ) )
        {
            foreach ( $helper_name as $key => $val )
            {
                self::helper( $val );
            }
        }
        # ----------------------------------------------------------------------
        # 如果已存在靜態變數陣列中，代表此類別已實體化
        # ----------------------------------------------------------------------
        elseif ( isset( Bootstart::$_help[$helper_name] ) )
        {
            Bootstart::$_help[$helper_name];
        }
        else
        {
            Bootstart::newHelper( $helper_name );
        }
    }
}