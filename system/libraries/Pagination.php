<?php if ( ! defined( 'DEVIL_SYS_CORE_PATH' ) ) exit( 'No direct script access allowed' );
/**
  @brief        分页类
  @remarks      分页样式
  @code
  #page {
      font:12px/16px arial;
  }
  #page span {
      float:left;
      margin:0px 3px;
  }
  #page a {
      float:left;
      margin:0 3px;
      border:1px solid #dddddd;
      padding:3px 7px;
      text-decoration:none;
      color:#666666;
  }
  #page a.now_page , #page a:hover {
      color:#ffffff;
      background:#0055cc;
  }
  @endcode
  @version      1.0.0
  @date         2015-03-07
  @since        1.0.0 -> 新增此新類別。
**/

class Pagination
{
    /**
    @brief      列表每页显示行数
    **/
    public $list_rows;

    /**
    @brief      总页数
    **/
    protected $total_pages;

    /**
    @brief      总行数
    **/
    protected $total_rows;

    /**
    @brief      当前页数
    **/
    protected $now_page;

    /**
    @brief      分页偏移量
    **/
    public $plus = 3;

    /**
    @brief      網址
    **/
    public $url;

    /**
    @brief      初始化分頁需要的資料
    @param      Array       $data
    **/
    public function __Initialization( $data = array() )
    {
        # ----------------------------------------------------------------------
        # 總筆數
        # ----------------------------------------------------------------------
        $this->total_rows = $data['total'];
        # ----------------------------------------------------------------------
        # 當前頁
        # ----------------------------------------------------------------------
        $this->now_page = $data['currentPage'];
        # ----------------------------------------------------------------------
        # 總分頁數
        # ----------------------------------------------------------------------
        $this->total_pages = $data['totalPage'];
        # ----------------------------------------------------------------------
        # 每頁呈現幾筆
        # ----------------------------------------------------------------------
        $this->list_rows = $data['pagesize'];
        # ----------------------------------------------------------------------
        # 當前分頁網址
        # ----------------------------------------------------------------------
        $this->url = $data['url'];
    }

    /**
    @brief      得到当前连接
    @param      Int         $page
    @param      String      $text
    @retval     String
    **/
    protected function _get_link( $page , $text )
    {
        return '<a href="' . $this->url . $page . '">' . $text . '</a>' . "\n";
    }

    /**
    @brief      得到第一页
    @param      String      $name 第一頁
    @retval     String
    **/
    public function first_page( $name = '第一页' )
    {
        if ( $this->now_page > 5 )
        {
            return $this->_get_link( '1' , $name );
        }
        return '';
    }

    /**
    @brief      最后一页
    @param      String      $name 最後一頁
    @retval     String
    **/
    public function last_page( $name = '最后一页' )
    {
        if ( $this->now_page < $this->total_pages - 5 )
        {
            return $this->_get_link( $this->total_pages , $name );
        }
        return '';
    }

    /**
    @brief      上一页
    @param      String      $name 上一頁
    @return     String
    **/
    public function up_page( $name = '上一页' )
    {
        if ( $this->now_page != 1 )
        {
            return $this->_get_link( $this->now_page - 1 , $name );
        }
        return '';
    }

    /**
    @brief      下一页
    @param      String      $name 下一頁
    @return     String
    **/
    public function down_page( $name = '下一页' )
    {
        if ( $this->now_page < $this->total_pages )
        {
            return $this->_get_link( $this->now_page + 1 , $name );
        }
        return '';
    }

    /**
    @brief      分页样式输出
    @param      Int         $param
    @return     String
    **/
    public function show( $param = 0 )
    {
        if ( $this->total_rows < 1 )
        {
            return '';
        }

        $className = 'show_' . $param;

        $classNames = get_class_methods( $this );

        if ( in_array( $className , $classNames ) )
        {
            return $this->$className();
        }
        return '';
    }

    /**
    @brief      分页样式2
    @return     String
    **/
    protected function show_2()
    {
        if($this->total_pages != 1)
        {
            $return = '';
            $return .= $this->up_page('<');
            for($i = 1;$i<=$this->total_pages;$i++)
            {
                if($i == $this->now_page)
                {
                    $return .= "<a class='now_page'>$i</a>\n";
                }
                else
                {
                    if($this->now_page-$i>=4 && $i != 1)
                    {
                        $return .="<span class='pageMore'>...</span>\n";
                        $i = $this->now_page-3;
                    }
                    else
                    {
                        if($i >= $this->now_page+5 && $i != $this->total_pages)
                        {
                            $return .="<span>...</span>\n";
                            $i = $this->total_pages;
                        }
                        $return .= $this->_get_link($i, $i) . "\n";
                    }
                }
            }
            $return .= $this->down_page('>');
            return $return;
        }
    }

    /**
    @brief      分页样式1
    @return     String
    **/
    protected function show_1()
    {
        $plus = $this->plus;
        if( $plus + $this->now_page > $this->total_pages)
        {
            $begin = $this->total_pages - $plus * 2;
        }else{
            $begin = $this->now_page - $plus;
        }

        $begin = ($begin >= 1) ? $begin : 1;
        $return = '';
        $return .= $this->first_page();
        $return .= $this->up_page();
        for ($i = $begin; $i <= $begin + $plus * 2;$i++)
        {
            if($i>$this->total_pages)
            {
                break;
            }
            if($i == $this->now_page)
            {
                $return .= "<a class='now_page'>$i</a>\n";
            }
            else
            {
                $return .= $this->_get_link($i, $i) . "\n";
            }
        }
        $return .= $this->down_page();
        $return .= $this->last_page();
        return $return;
    }

    /**
    @brief      分页样式3
    @return     String
    **/
    protected function show_3()
    {
        $plus = $this->plus;
        if( $plus + $this->now_page > $this->total_pages)
        {
            $begin = $this->total_pages - $plus * 2;
        }else{
            $begin = $this->now_page - $plus;
        }
        $begin = ($begin >= 1) ? $begin : 1;
        $return = '总计 ' .$this->total_rows. ' 个记录分为 ' .$this->total_pages. ' 页, 当前第 ' . $this->now_page . ' 页 ';
        $return .= ',每页: ' . $this->list_rows . ', ';
        $return .= $this->first_page()."\n";
        $return .= $this->up_page()."\n";
        $return .= $this->down_page()."\n";
        $return .= $this->last_page()."\n";

        $return .= '<select onchange="javascript:window.location.href=\''.$this->url.'\' + this.value">';

        for ($i = $begin;$i<=$begin+10;$i++)
        {
            if($i>$this->total_pages)
            {
                break;
            }
            if($i == $this->now_page)
            {
                $return .= '<option selected="true" value="'.$i.'">'.$i.'</option>';
            }
            else
            {
                $return .= '<option value="' .$i. '">' .$i. '</option>';
            }
        }
         $return .= '</select>';
        return $return;
    }
}