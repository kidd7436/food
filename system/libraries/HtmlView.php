<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        HtmlView Template
  @version      1.0.0
  @date         2015-03-29 by Vam
  @since        1.0.0 -> 新增此新類別。
**/

class HtmlView extends View
{
    /**
    @brief      宣告空陣列
    **/
    private $_files = array();

    /**
    @brief      正規化一般變數
    **/
    private $_newvar_format = '/\{(\w+)\}/';

    /**
    @brief      預設樣版路徑
    **/
    private $_dir = DEVIL_APP_TPL_PATH;

    /**
    @cond       建構子
    @remarks    利用建構子初始化樣板路徑
    **/
    public function __construct(  )
    {
        # ----------------------------------------------------------------------
        # 設定樣板路徑
        # ----------------------------------------------------------------------
        $this->dir = DEVIL_APP_TPL_PATH;
        # ----------------------------------------------------------------------
        # 如果沒有就 DIE 並顯示錯誤訊息
        # ----------------------------------------------------------------------
        if ( $this->dir === null )
        {
            show_error( 'Cannot found template folder' );
        }
    }
    /**
    @endcond
    **/

    /**
    @brief      處理要產生的資料
    @param      String      $block 區塊資料
    @retval     String
    **/
    private function parse( $block )
    {
        if ( ! isset( $this->_files[$block] ) )
        {
            show_error( "not vaild block name :{$block}" );
        }
        # ----------------------------------------------------------------------
        # 讀取要處理的資料
        # ----------------------------------------------------------------------
        $temp = $this->_files[$block];
        # ----------------------------------------------------------------------
        # 新方法
        # ----------------------------------------------------------------------
        return preg_replace_callback( $this->_newvar_format , array( $this , 'subparse' ) , $temp );
    }

    /**
    @brief      給正規化資料呼叫取代用的功能
    @param      String      $matches 區塊資料
    **/
    private function subparse( $matches )
    {
        if ( isset( $this->vars[$matches[1]] ) )
        {
            return $this->vars[$matches[1]];
        }
        # ----------------------------------------------------------------------
        # Don't bother doing the substitution.
        # ----------------------------------------------------------------------
        return $matches[0];
    }

    /**
    @brief      讀取要處理的模版資料 ( 靜態頁面 )
    @param      Array       $tpl_var 區塊資料
    @param      String      $value 區塊資料
    **/
    private function load( $tpl_var = null , $value = '' )
    {
        if ( is_array( $tpl_var ) )
        {
            foreach ( $tpl_var as $key => $val )
            {
                if ( $key != '' )
                {
                    if ( ! file_exists( $this->_dir . $val ) )
                    {
                        show_error( "can't find template file :{$this->_dir}{$val}" );
                    }
                    $this->_files[$key] = implode( "" , file( $this->_dir . $val ) );
                }
            }
        }
        else
        {
            if ( $tpl_var != '' )
            {
                if ( ! file_exists( $this->_dir . $value ) )
                {
                    show_error( "can't find template file :{$this->_dir}{$value}" );
                }
                $this->_files[$tpl_var] = implode( "" , file( $this->_dir . $value ) );
            }
        }
    }

    /**
    @brief      設定要替換的資料
    @param      String      $tpl_var 區塊名稱
    @param      String      $value 區塊資料
    **/
    private function set( $tpl_var = null , $value = null )
    {
        # ----------------------------------------------------------------------
        # 判斷是否為陣列
        # ----------------------------------------------------------------------
        if ( is_array( $tpl_var ) )
        {
            # ------------------------------------------------------------------
            # 取出陣列內的設定值
            # ------------------------------------------------------------------
            foreach ( $tpl_var as $key => $val )
            {
                # --------------------------------------------------------------
                # 如果陣列鍵名不等於空的話，就放到vars內
                # --------------------------------------------------------------
                if ( $key != '' ) $this->vars[$key] = $val;
            }
        }
        else
        {
            if ( $tpl_var != '' )
            {
                $this->vars[$tpl_var] = $value;
            }
        }
    }

    /**
    @brief      秀出處理完的資料 ( 靜態頁面 )
    @param      String      $block 區塊名稱
    @param      Bool        $return 是否返回
    **/
    private function show( $block = null , $return = false )
    {
        # ----------------------------------------------------------------------
        # 判斷是否需要直接輸出
        # ----------------------------------------------------------------------
        if ( ! $return )
        {
            echo $this->parse( $block );
        }
        else
        {
            return $this->parse( $block );
        }
        unset( $this->_files[$block] );
    }

    /**
    @brief      秀出處理完的資料 ( 靜態 HTML 頁面 )
    @param      String      $block 區塊名稱
    @param      String      $file 檔案名稱
    @param      Array       $dataArr 資料陣列
    @param      Bool        $isreturn 是否返回
    @retval     String
    @remarks    輸出結果 HTML 格式。
    @code{.unparsed}
    $this->Core_HtmlView->render
    (
        "Core_UI_Header_Page" ,
        "Core_UI_Header_Page.html" ,
        array
        (
            # --------------------------------------------------------------
            # 當前網址
            # --------------------------------------------------------------
            "URL" => DEVIL_APP_Url
        )
    );
    @endcode
    **/
    public function render( $block = null , $file = null , $dataArr = null , $isreturn = false )
    {
        # ----------------------------------------------------------------------
        # 載入模版資料
        # ----------------------------------------------------------------------
        self::load( $block , $file );
        # ----------------------------------------------------------------------
        # 設定樣板資料
        # ----------------------------------------------------------------------
        self::set( $dataArr );
        # ----------------------------------------------------------------------
        # 輸出結果
        # ----------------------------------------------------------------------
        return self::show( $block , $isreturn );
    }
}