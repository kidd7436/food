<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        HTML Form 類別賦與你能從陣列或是資料庫的回傳結果自動產生表單元件。
  @version      1.0.0
  @date         2015-02-28
  @since        1.0.0 -> 新增此新類別。
**/

class HtmlForm
{
    /**
    @brief      $tag
    **/
    private $tag;

    /**
    @brief      $xhtml
    **/
    private $xhtml;

    /**
    @cond       建構子
    **/
    public function __construct( $xhtml = true )
    {
        $this->xhtml = $xhtml;
    }
    /**
    @endcond
    **/

    /**
    @brief      建立表單
    @param      String      $action 後端接收的網址，預設為#當前頁
    @param      String      $method 規定如何傳送表單資訊，預設為post
    @param      String      $id 表單的 Id
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    <form action="result.php" method="post" id="demoForm">
    @endcode
    @code{.unparsed}
    $frmStr = $this->Core_HtmlForm->startForm( 'result.php', 'post', 'demoForm', array('class'=>'demoForm' ) );
    @endcode
    @attention 需搭配
    @code{.unparsed}
    $this->Core_HtmlForm->endForm();
    @endcode
    處理結尾
    **/
    public function startForm( $action = '#' , $method = 'post' , $id = '' , $attr_ar = false )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<form action=\"$action\" method=\"$method\"";
        # ----------------------------------------------------------------------
        # 如果$id不為空，就組合至表單中
        # ----------------------------------------------------------------------
        if ( ! empty( $id ) )
        {
            $str .= " id=\"$id\"";
        }
        # ----------------------------------------------------------------------
        # 如果$attr_ar為陣列的話就使用 addAttributes() 處理
        # ----------------------------------------------------------------------
        $str .= ( is_array( $attr_ar ) ) ? $this->addAttributes( $attr_ar ) . '>': '>';

        return $str;
    }

    /**
    @brief      結束表單
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    </form>
    @endcode
    @attention  與
    @code{.unparsed}
    $this->Core_HtmlForm->startForm( 'result.php', 'post', 'demoForm', array('class'=>'demoForm' ) );
    @endcode
    是一對的
    @code{.unparsed}
    $this->Core_HtmlForm->endForm();
    @endcode
    **/
    public function endForm()
    {
        return "</form>";
    }

    /**
    @brief      表單元件添加屬性用
    @param      Array       ( $attr_ar 屬性陣列 )
    @retval     String      ( class="demoForm" )
    @attention  注意必須要有index值，disabled等。
    @code{.unparsed}
    $this->Core_HtmlForm->addAttributes( array('class'=>'demoForm' ) );
    @endcode
    **/
    private function addAttributes( $attr_ar )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $str = '';
        # ----------------------------------------------------------------------
        # check minimized (boolean) attributes html5
        # ----------------------------------------------------------------------
        $min_atts = array
        (
            'checked' , 'disabled' , 'readonly' , 'multiple' ,
            'required' , 'autofocus' , 'novalidate' , 'formnovalidate'
        );
        # ----------------------------------------------------------------------
        # 跑迴圈添加屬性
        # ----------------------------------------------------------------------
        foreach( $attr_ar as $key=>$val )
        {
            # ------------------------------------------------------------------
            # 判斷有無存在上方的陣列
            # ------------------------------------------------------------------
            if ( in_array( $key , $min_atts ) )
            {
                # --------------------------------------------------------------
                # 如果資料不為空
                # --------------------------------------------------------------
                if ( ! empty( $val ) )
                {
                    # ----------------------------------------------------------
                    # 如果 $this->xhtml 為 true，則使用跟key值一樣的屬性名稱
                    # 例如 disabled="disabled" 這種標準用法
                    # ----------------------------------------------------------
                    $str .= ( $this->xhtml ) ? " $key=\"$key\"": " $key";
                }
            }
            else
            {
                $str .= " $key=\"$val\"";
            }
        }
        return $str;
    }

    /**
    @brief      添加輸入框元件
    @param      String      $type 表單元件的形態
    @param      String      $name 表單元件名稱
    @param      String      $value 表單元件值
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    <input....
    @endcode
    @remarks    可產生input、radio、checkbox等。
    @code{.unparsed}
    $this->Core_HtmlForm->addInput('text', 'firstName', '', array('id'=>'firstName', 'size'=>16, 'required'=>true ) );
    @endcode
    **/
    public function addInput( $type , $name , $value , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
        # ----------------------------------------------------------------------
        # 添加元素的屬性
        # ----------------------------------------------------------------------
        if ( $attr_ar)
        {
            $str .= $this->addAttributes( $attr_ar );
        }
        # ----------------------------------------------------------------------
        # 當使用 xhtml 時所有元素，包括空元素，比如img、br等，也都必須閉合
        # ----------------------------------------------------------------------
        $str .= ( $this->xhtml ) ? ' />': '>';

        return $str;
    }

    /**
    @brief      添加區塊型欄位元件
    @param      String      $name 表單元件名稱
    @param      Int         $rows 规定文本区内的可见行数
    @param      Int         $cols 规定文本区内的可见宽度
    @param      String      $value 內容
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    <textarea>...</textarea>
    @endcode
    @code{.unparsed}
    $this->Core_HtmlForm->addTextArea('comments', 6, 40, '', array('id'=>'comments', 'placeholder'=>'We would love to hear your comments.') );
    @endcode
    **/
    public function addTextarea( $name , $rows = 4 , $cols = 30 , $value = '' , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<textarea name=\"$name\" rows=\"$rows\" cols=\"$cols\"";
        # ----------------------------------------------------------------------
        # 添加元素的屬性
        # ----------------------------------------------------------------------
        if ( $attr_ar )
        {
            $str .= $this->addAttributes( $attr_ar );
        }
        # ----------------------------------------------------------------------
        # 處理基本元素結尾
        # ----------------------------------------------------------------------
        $str .= ">$value</textarea>";

        return $str;
    }

    /**
    @brief      input 元素定義標記
    @param      String      $forID for名稱
    @param      String      $text 要包起來的資料
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    <label for="comments">Your comments: hello!!</label>
    @endcode
    @code{.unparsed}
    $this->Core_HtmlForm->addLabelFor( 'comments', 'Your comments: hello!!' );
    @endcode
    **/
    public function addLabelFor( $forID , $text , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<label for=\"$forID\"";
        # ----------------------------------------------------------------------
        # 添加元素的屬性
        # ----------------------------------------------------------------------
        if ( $attr_ar )
        {
            $str .= $this->addAttributes( $attr_ar );
        }
        # ----------------------------------------------------------------------
        # 處理基本元素結尾
        # ----------------------------------------------------------------------
        $str .= ">$text</label>";

        return $str;
    }

    /**
    @brief      select 下拉選單
    @param      String      $name select名稱
    @param      String      $option_list 資料陣列
    @param      String      $selected_value 要選中的值
    @param      String      $header 是否設定第一個 option
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks    此範例結果：
    @code{.unparsed}
    <select name="rank">
      <option value="0">Totally lame</option>
      <option value="1">Minimally useful</option>
      <option value="2" selected="selected">Pretty good</option>
      <option value="3">I realy like it</option>
      <option value="4">Fabulous</option>
    </select>
    @endcode
    @code{.unparsed}
    $rank = array('Totally lame', 'Minimally useful', 'Pretty good', 'I realy like it', 'Fabulous');
    $this->Core_HtmlForm->addSelectList( 'rank', $rank, 'Pretty good' );
    @endcode
    **/
    public function addSelectList( $name , $option_list , $selected_value = null , $header = null , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<select name=\"$name\"";
        # ----------------------------------------------------------------------
        # 添加元素的屬性
        # ----------------------------------------------------------------------
        if ( $attr_ar )
        {
            $str .= $this->addAttributes( $attr_ar );
        }
        # ----------------------------------------------------------------------
        # 處理基本元素換行
        # ----------------------------------------------------------------------
        $str .= ">\n";
        # ----------------------------------------------------------------------
        # 如果有需要設定第一個 option ,通常會拿來產生成 == 請選擇 ==
        # ----------------------------------------------------------------------
        if ( isset( $header ) )
        {
            $str .= "  <option value=\"\">$header</option>\n";
        }
        # ----------------------------------------------------------------------
        # 迴圈產生所有的下拉選項
        # ----------------------------------------------------------------------
        foreach ( $option_list as $val => $text )
        {
            # ------------------------------------------------------------------
            # 判斷 option 是否要有值
            # ------------------------------------------------------------------
            $str .= "  <option value=\"$val\"";
            # ------------------------------------------------------------------
            # 如果有指定要選中的值在這邊處理
            # ------------------------------------------------------------------
            if ( isset( $selected_value ) && $selected_value == $val )
            {
                # --------------------------------------------------------------
                # 如果有設定 xhml 嚴謹則需成對
                # --------------------------------------------------------------
                $str .= ( $this->xhtml ) ? ' selected="selected"': ' selected';
            }
            # ------------------------------------------------------------------
            # 處理基本元素 option 結尾
            # ------------------------------------------------------------------
            $str .= ">$text</option>\n";
        } 
        # ----------------------------------------------------------------------
        # 處理基本元素 select 結尾
        # ----------------------------------------------------------------------
        $str .= "</select>";

        return $str;
    }

    /**
    @brief      input 元素定義標記
    @param      String      $tag 元素
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks
    @remarks    此範例返回：
    @code{.unparsed}
    <p id="comments">hello!!</p>
    @endcode
    @code{.unparsed}
    $this->Core_HtmlForm->startTag( 'p' , array( 'id'=>'comments' ) ) . 'hello!!' . $this->HtmlForm->endTag('p');
    @endcode
    @attention  需搭配
    @code{.unparsed}
    $this->Core_HtmlForm->endTag('p');
    @endcode
    處理結尾
    **/
    public function startTag( $tag , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 先將傳入的 Tag 存放到物件變數 $this->tag 中
        # ----------------------------------------------------------------------
        $this->tag = $tag;
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<$tag";
        # ----------------------------------------------------------------------
        # 添加元素的屬性
        # ----------------------------------------------------------------------
        if ( $attr_ar )
        {
            $str .= $this->addAttributes( $attr_ar );
        }
        # ----------------------------------------------------------------------
        # 處理基本元素結尾
        # ----------------------------------------------------------------------
        $str .= '>';

        return $str;
    }

    /**
    @brief      input 元素定義標記結尾
    @param      String      $tag 元素
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    <p id="comments">hello!!</p>
    @endcode
    @code{.unparsed}
    $this->Core_HtmlForm->startTag( 'p' , array( 'id'=>'comments' ) ) . 'hello!!' . $this->HtmlForm->endTag('p');
    @endcode
    @attention  需搭配
    @code{.unparsed}
    $this->Core_HtmlForm->startTag('p');
    @endcode
    處理開頭
    **/
    public function endTag( $tag = '' )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = ( $tag ) ? "</$tag>": "</$this->tag>";
        # ----------------------------------------------------------------------
        # 清空屬性
        # ----------------------------------------------------------------------
        $this->tag = '';

        return $str;
    }

    /**
    @brief      產生標籤
    @param      String      $tag 元素
    @param      Array       $attr_ar 屬性陣列
    @retval     String
    @remarks    此範例返回：
    @code{.unparsed}
    <br />
    @endcode
    @code{.unparsed}
    $this->Core_HtmlForm->addEmptyTag( 'br' );
    @endcode
    **/
    public function addEmptyTag( $tag , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 處理基本元素
        # ----------------------------------------------------------------------
        $str = "<$tag";
        # ----------------------------------------------------------------------
        # 添加元素的屬性
        # ----------------------------------------------------------------------
        if ( $attr_ar )
        {
            $str .= $this->addAttributes( $attr_ar );
        }
        # ----------------------------------------------------------------------
        # 處理基本元素結尾
        # ----------------------------------------------------------------------
        $str .= $this->xhtml? ' />': '>';

        return $str;
    }
}