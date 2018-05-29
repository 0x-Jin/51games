<?php 
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/8/29
 * Time: 16:04
 *
 * 幻灯片挂件
 */
namespace Cy\Widget;
use Think\Controller;
class SlideWidget extends Controller
{
	public function slideList($agent = '', $column_id = '', $slideType = 1, $index = 0)
	{
        $list = M('slide s',C('DB_PREFIX_WEBSITE'),'WEBSITE')->field('t.id,t.templateContent,s.id AS slide_id,s.width,s.height')
        ->join('JOIN '.C('DB_PREFIX_WEBSITE').'slide_template t ON s.templateId=t.id')
        ->where(array('s.status'=>0,'s.agent'=>$agent,'s.columnId'=>$column_id,'s.slideType'=>$slideType))->order('s.slideOrder ASC')->select();

        $row = $list[$index];
        !is_dir(THEME_PATH.'Widget/slideList') && mkdir(THEME_PATH.'Widget/slideList',0755,true);
        file_put_contents(THEME_PATH.'Widget/slideList/'.$row['id'].'.html', $row['templateContent']);
        $row['slide_id'] && $imgaes = M('slide_picture',C('DB_PREFIX_WEBSITE'),'WEBSITE')->where(array('slideId'=>$row['slide_id'], 'status'=>0))->select();
        $this->assign('images',$imgaes);
        $this->display('Widget/slideList/'.$row['id']);
	}
}