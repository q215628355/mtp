<?php
//分页类
//加入了pageSize,totalRow;

class Page{
    public  $form = 'p';//页码的URL参数名	
	public  $listpage = array('prev','count','number','input','next');	//需要获取的分页文本内容
	public  $pagename = array('上一页','下一页','共[number]页',10);	// 数字表示number长度,必须是偶数.设置参数顺序不能变
	public  $action = __SELF__;//action要提交的地址
	
	//以下参数不可以设置
	private $page = null;
	private $maxPage = null;
	private $pageSize = null;
	private $totalRow=null;
	private $addurl = null;
	private $url = null;
	//实例化
	public function __construct($page,$pageSize,$totalRow,$addurl=array()) {
		$this->totalRow=$totalRow;
		$this->pageSize=$pageSize;
		$this->maxPage = ceil($this->totalRow/$this->pageSize);
		$this->page    =  $page < 1 ? 1 : $page;	
        $this->page    =  $this->page >	$this->maxPage ? $this->maxPage :  $this->page;	
		$this->addurl  =  $addurl;
		$this->url     = $this->action.'?'. http_build_query($this->addurl);
	}
	//设置参数
	public function setConfig($name,$config){	
        	
		if(in_array($name,array('form','listpage','pagename','action')))
		{			
			$this->$name = $config;
		}
	}
	
	//显示分页
	public function show(){
		$prev = $next =  '';
		if($this->page > 1)
			$prev = '<a href="'.$this->url.'&'.$this->form.'='.($this->page-1).'" class="prev" >'.$this->pagename[0].'</a>';	
		if($this->page < $this->maxPage)
			$next = '<a href="'.$this->url.'&'.$this->form.'='.($this->page+1).'" class="next" >'.$this->pagename[1].'</a>';
		
		$count = str_replace('[number]',$this->maxPage,$this->pagename[2]);
		
		$this->pagename[3] = intval($this->pagename[3] /2) * 2;
		
		$min =  $this->page - ($this->pagename[3])/2; 
		$max =  $this->page + ($this->pagename[3])/2;
		
		if($min <1){
			$min  = 1;
			$max  =	$this->pagename[3];	
		}
		if($max > $this->maxPage){
			$max = $this->maxPage;
			$min = $max - $this->pagename[3];
			$min = $min <1 ? : $min;
		}		
		$number = '<span class="number">';	
        //此处可能存在内存泄漏，暂不知道为何原因		
		for($i=(int)$min;$i<=(int)$max;$i++){			
			if($i==$this->page){
				$number .= '<a href="'.$this->url.'&'.$this->form.'='.$i.'" class="hover" >'.$i.'</a>';	
				
			}else{
				$number .= '<a href="'.$this->url.'&'.$this->form.'='.$i.'" >'.$i.'</a>';	
			}		
		}
		$number.="</span>";
		
		$input = '<form action="'.$this->action.'" method="get">';
		foreach($this->addurl as $key=>$val){
			$input .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />';			
		}
		$input .= '<input class="input" type="text" name="'.$this->form.'" value=""  placeholder="'.$this->page.'/'.$this->maxPage.'"/>';	
		$input .='</form>';
		$oup = array();
		foreach($this->listpage as $name){			
			$oup[] = isset( $$name ) ? $$name : '';
		}
	    return implode(' ',$oup);		
	}
}