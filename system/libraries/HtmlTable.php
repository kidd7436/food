<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        HTML Table 類別賦與你能從陣列或是資料庫的回傳結果自動產生表格。
  @version      1.0.0
  @date         2015-04-16
  @since        1.0.0 -> 新增此新類別。
**/

class HtmlTable
{
    /**
    @brief      &lt;thead&gt; 标签定义表格的表头。
    **/
    private $thead = array();

    /**
    @brief      &lt;tfoot&gt; 标签定义表格的页脚（脚注或表注）
    **/
    private $tfoot = array();

    /**
    @brief      &lt;tbody&gt; 标签表格主体（正文）。
    **/
    private $tbody_ar = array();

    /**
    @brief      存放當前產生的資料
    **/
    private $cur_section;

    /**
    @brief      用來存放產生的表格
    **/
    private $tableStr = '';

    /**
    @brief      產生 table
    @param      type        $id 區塊ID - 預設值為空
    @param      type        $klass class 屬性名稱 - 預設值為空
    @param      type        $attr_ar 其他擴充屬性 - 預設值為空
    @remarks    使用範例
    @code{.unparsed}
    $this->Core_HtmlTable->add_table();
    @endcode
    **/
    public function add_table( $id = '' , $klass = '' , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # add rows to tbody unless addTSection called
        # ----------------------------------------------------------------------
        $this->cur_section = &$this->tbody_ar[0];
        # ----------------------------------------------------------------------
        # 在此處添加 table id屬性
        # ----------------------------------------------------------------------
        $this->tableStr = "\n<table" . ( ! empty( $id ) ? " id=\"$id\"" : '' );
        # ----------------------------------------------------------------------
        # 在此處添加 table class 屬性
        # ----------------------------------------------------------------------
        $this->tableStr .= ( ! empty( $klass ) ? " class=\"$klass\"" : '' );
        # ----------------------------------------------------------------------
        # 在此處添加擴充屬性
        # ----------------------------------------------------------------------
        $this->tableStr .= $this->addAttribs( $attr_ar ) . ">\n";
    }

    /**
    @brief      設定開始的資料是 THEAD、TFOOT 或 TBODY
    @param      type        $sec 區塊名稱
    @param      type        $klass class 屬性名稱 - 預設值為空
    @param      type        $attr_ar 其他擴充屬性 - 預設值為空
    @remarks    使用範例
    @code{.unparsed}
    $this->Core_HtmlTable->add_TSection( 'tbody' );
    @endcode
    **/
    public function add_TSection( $sec , $klass = '' , $attr_ar = array() )
    {
        switch ( $sec )
        {
            case 'thead':
                $ref = &$this->thead;
                break;
            case 'tfoot':
                $ref = &$this->tfoot;
                break;
            default:
            case 'tbody':
                $ref = &$this->tbody_ar[ count( $this->tbody_ar ) ];
                break;
        }
        # ----------------------------------------------------------------------
        # 區塊的 class 屬性
        # ----------------------------------------------------------------------
        $ref['klass'] = $klass;
        # ----------------------------------------------------------------------
        # 區塊的擴充屬性
        # ----------------------------------------------------------------------
        $ref['atts'] = $attr_ar;
        # ----------------------------------------------------------------------
        # 區塊的內容屬性，用來存放此區塊中的 tr、td
        # ----------------------------------------------------------------------
        $ref['rows'] = array();
        # ----------------------------------------------------------------------
        # 把結果存入類別變數中
        # ----------------------------------------------------------------------
        $this->cur_section = &$ref;
    }

    /**
    @brief      設定表格名稱
    @param      String      $cap 名稱
    @param      String      $klass class屬性 - 預設值為空
    @param      String      $attr_ar 其他擴充屬性 - 預設值為空
    @remarks    使用範例
    @code{.unparsed}
    $this->Core_HtmlTable->add_caption( 'I am caption' );
    @endcode
    @code{.unparsed}
    $this->Core_HtmlTable->add_caption( 'I am caption' , 'className' , array( 'id' => 'DomCaptions' ) );
    @endcode
    **/
    public function add_caption( $cap , $klass = '' , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 如果有針對 caption 設定 class 屬性，就添加 class 屬性，否則為空
        # ----------------------------------------------------------------------
        $this->tableStr.= "<caption" . ( ! empty( $klass ) ? " class=\"$klass\"" : '' );
        # ----------------------------------------------------------------------
        # 若有設定擴充 caption 的屬性，就呼叫 addAttribs() 處理
        # ----------------------------------------------------------------------
        $this->tableStr.= $this->addAttribs( $attr_ar ) . '>' . $cap . "</caption>\n";
    }

    /**
    @brief      增加 tr
    @param      String      $klass class屬性 - 預設值為空
    @param      String      $attr_ar 其他擴充屬性 - 預設值為空
    @remarks    使用範例
    @code{.unparsed}
    $this->Core_HtmlTable->add_row();
    @endcode
    **/
    public function add_row( $klass = '' , $attr_ar = array() )
    {
        # ----------------------------------------------------------------------
        # 加入至當前的 Section
        # ----------------------------------------------------------------------
        $this->cur_section['rows'][] = array
        (
            'klass' => $klass ,
            'atts' => $attr_ar ,
            'cells' => array()
        );
    }

    /**
    @brief      增加 td
    @param      String      $data 內容 - 預設值為空
    @param      String      $type data表示td、其它表示th - 預設值為 data
    @param      String      $klass class屬性 - 預設值為空
    @param      Array       $attr_ar 其他擴充屬性 - 預設值為空
    @remarks    使用範例
    @code{.unparsed}
    $this->Core_HtmlTable->add_cell( 'Colors' );
    @endcode
    **/
    public function add_cell( $data = '' , $type = 'data' , $klass = '' , $attr_ar = array() )
    {
        $cell = array
        (
            'data' => $data ,
            'klass' => $klass ,
            'type' => $type ,
            'atts' => $attr_ar
        );

        if ( empty( $this->cur_section['rows'] ) )
        {
            try
            {
                throw new Exception( 'You need to addRow before you can addCell' );
            }
            catch( Exception $ex )
            {
                $msg = $ex->getMessage();
                echo "<p>Error: $msg</p>";
            }
        }

        // add to current section's current row's list of cells
        $count = count( $this->cur_section['rows'] );
        $curRow = &$this->cur_section['rows'][$count - 1];
        $curRow['cells'][] = &$cell;
    }

    /**
    @brief      回傳所建立的 Table
    @retval     String
    @remarks    使用範例
    @code{.unparsed}
    $this->Core_HtmlTable->generate();
    @endcode
    **/
    public function generate()
    {
        # ----------------------------------------------------------------------
        # 如果存放 thead 的 array 不為空，就產生 thead 區塊
        # ----------------------------------------------------------------------
        $this->tableStr .= ! empty( $this->thead ) ? $this->getSection( $this->thead , 'thead' ) : '';
        # ----------------------------------------------------------------------
        # 處理tbody的內容，可以有好幾個 tbody
        # ----------------------------------------------------------------------
        foreach( $this->tbody_ar as $sec )
        {
            $this->tableStr .= ! empty( $sec ) ? $this->getSection( $sec , 'tbody' ) : '';
        }
        # ----------------------------------------------------------------------
        # 如果存放 tfoot 的 array 不為空，就產生 tfoot 區塊
        # ----------------------------------------------------------------------
        $this->tableStr .= ! empty( $this->tfoot ) ? $this->getSection( $this->tfoot , 'tfoot' ) : '';
        # ----------------------------------------------------------------------
        # 在加上</table>結尾
        # ----------------------------------------------------------------------
        $this->tableStr .= "</table>\n";
        # ----------------------------------------------------------------------
        # 回傳結果
        # ----------------------------------------------------------------------
        return $this->tableStr;
    }

    /**
    @brief      清除表格，當你想要顯示多個表格時你應該先清除先前所建立的表格資料:
    @retval     Void
    @remarks    使用範例
    @code{.unparsed}
    $this->load->library( 'HtmlTable' );

    $this->Core_HtmlTable->add_table( 'idName' , '' , array( 'border' => 0 , 'cellpadding' => 4 , 'cellspacing' => 0 ) );
    $this->Core_HtmlTable->add_TSection( 'thead' );
    $this->Core_HtmlTable->add_row( '' , array( 'id' => 'VamTHead' ) );
    $this->Core_HtmlTable->add_cell( 'Name' , 'header' );
    $this->Core_HtmlTable->add_cell( 'Color' , 'header' );
    $this->Core_HtmlTable->add_cell( 'Size' , 'header' );
    $this->Core_HtmlTable->add_TSection( 'tbody' );
    $dataArr = array
    (
        array( '' , 'Blue' , 'Small' ) ,
        array( 'Mary' , 'Red' , 'Large' ) ,
        array( 'John' , 'Green' , 'Medium' ) ,
    );
    foreach ( $dataArr as $list )
    {
        $this->Core_HtmlTable->add_row( '' , array( 'id' => time() . '_VamTBody' ) );
        list( $td1 , $td2 , $td3 ) = $list;
        $this->Core_HtmlTable->add_cell( $td1 );
        $this->Core_HtmlTable->add_cell( $td2 );
        $this->Core_HtmlTable->add_cell( $td3 );
    }
    echo $this->Core_HtmlTable->generate();


    $this->Core_HtmlTable->clear();


    $this->Core_HtmlTable->add_table( 'idName' , '' , array( 'border' => 0 , 'cellpadding' => 4 , 'cellspacing' => 0 ) );
    $this->Core_HtmlTable->add_TSection( 'thead' );
    $this->Core_HtmlTable->add_row();
    $this->Core_HtmlTable->add_cell( 'Name' , 'header' );
    $this->Core_HtmlTable->add_cell( 'Color' , 'header' );
    $this->Core_HtmlTable->add_cell( 'Size' , 'header' );
    $this->Core_HtmlTable->add_TSection( 'tbody' );
    $dataArr = array
    (
        array( '' , 'Blue' , 'Small' ) ,
        array( 'Mary' , 'Red' , 'Large' ) ,
        array( 'John' , 'Green' , 'Medium' ) ,
    );
    foreach ( $dataArr as $list )
    {
        $this->Core_HtmlTable->add_row();
        list( $td1 , $td2 , $td3 ) = $list;
        $this->Core_HtmlTable->add_cell( $td1 );
        $this->Core_HtmlTable->add_cell( $td2 );
        $this->Core_HtmlTable->add_cell( $td3 );
    }
    echo $this->Core_HtmlTable->generate();
    @endcode
    **/
    public function clear()
    {
        $this->tableStr = '';
        $this->cur_section = null;
        $this->thead = array();
        $this->tfoot = array();
        $this->tbody_ar = array();
    }

    /**
    @brief      處理要擴充的屬性資料
    @param      Array       $attr_ar 屬性
    @retval     String
    **/
    private function addAttribs( $attr_ar )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $str = '';
        # ----------------------------------------------------------------------
        # 迴圈組合屬性資料
        # ----------------------------------------------------------------------
        foreach( $attr_ar as $key=>$val )
        {
            $str .= " $key=\"$val\"";
        }
        # ----------------------------------------------------------------------
        # 回傳結果
        # ----------------------------------------------------------------------
        return $str;
    }

    /**
    @brief      td內容
    @param      Array       $cells 屬性
    @retval     String
    **/
    private function getRowCells( $cells )
    {
        # ----------------------------------------------------------------------
        # 初始化變數
        # ----------------------------------------------------------------------
        $str = '';
        # ----------------------------------------------------------------------
        # 迴圈組合資料
        # ----------------------------------------------------------------------
        foreach( $cells as $cell )
        {
            # ------------------------------------------------------------------
            # 如果type為 data 則表示td內容
            # ------------------------------------------------------------------
            $tag = ( $cell['type'] == 'data' ) ? 'td': 'th';
            # ------------------------------------------------------------------
            # 如果有指定 class 屬性就加上
            # ------------------------------------------------------------------
            $str .= ( ! empty( $cell['klass'] ) ? "    <$tag class=\"{$cell['klass']}\"" : "    <$tag" );
            # ------------------------------------------------------------------
            # 呼叫 addAttribs() 來組合擴充屬性
            # ------------------------------------------------------------------
            $str .= $this->addAttribs( $cell['atts'] ) . ">" . $cell['data'] . "</$tag>\n";
        }
        # ----------------------------------------------------------------------
        # 回傳結果
        # ----------------------------------------------------------------------
        return $str;
    }

    /**
    @brief      取得組合資料
    @param      Array       $cells 屬性
    @retval     String
    **/
    private function getSection( $sec, $tag )
    {
        # ----------------------------------------------------------------------
        # 如果有設定 class 屬性，就加上。
        # ----------------------------------------------------------------------
        $klass = ! empty( $sec['klass'] ) ? " class=\"{$sec['klass']}\"" : '';
        # ----------------------------------------------------------------------
        # 如果有擴充屬性就加上
        # ----------------------------------------------------------------------
        $atts = ! empty( $sec['atts'] ) ? $this->addAttribs( $sec['atts'] ): '';
        # ----------------------------------------------------------------------
        # 組合回傳資料
        # ----------------------------------------------------------------------
        $str = "<$tag" . $klass . $atts . ">\n";
        # ----------------------------------------------------------------------
        # 逐筆整理
        # ----------------------------------------------------------------------
        foreach( $sec['rows'] as $row )
        {
            # ------------------------------------------------------------------
            # 如果有指定class屬性，就在tr 加上，否則不處理
            # ------------------------------------------------------------------
            $str .= ( ! empty( $row['klass'] ) ? "  <tr class=\"{$row['klass']}\"": "  <tr" );
            # ------------------------------------------------------------------
            # 如果有擴充屬性就加上
            # ------------------------------------------------------------------
            $str .= $this->addAttribs( $row['atts'] ) . ">\n";
            # ------------------------------------------------------------------
            # 組合td內容
            # ------------------------------------------------------------------
            $str .= $this->getRowCells( $row['cells'] ) . "  </tr>\n";
        }
        # ----------------------------------------------------------------------
        # 組合回傳文字
        # ----------------------------------------------------------------------
        $str .= "</$tag>\n";
        # ----------------------------------------------------------------------
        # 回傳結果
        # ----------------------------------------------------------------------
        return $str;
    }
}