<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 简化扩展标签库
 */
namespace base\taglib;

use think\template\TagLib;

class Tag extends TagLib{

    /**
     * 定义标签列表
     */
    protected $tags   =  [
        'breads'   => ['attr' => 'isshwobread,value','close' => 0, 'expression' => true],
        'imgs'     => ['attr' => 'name,action,num','close' => 0, 'expression' => true]
    ];

    /**
     * 后台面包屑导航
     */
    public function tagBreads(array $tag): string
    {
        $value  = isset($tag['value']) ? $this->autoBuildVar($tag['value']) : '$value';
        //开始处理面包屑
        $parseStr ='';
        $parseStr .='<?php if(!empty(' . $value . ')): ?>';
        $parseStr .= '<div class="docs-brand">';
        $parseStr .= '<ul class="breadcrumb">';
        $parseStr .='<?php foreach(' . $value . ' as $item): ?>';
        $parseStr .= '<li class="breadcrumb-item">';
        $parseStr .='<?php if(!empty($item["icon"])): ?>';
        $parseStr .=' <i class="icon mr-4 bi-<?php echo $item["icon"];?>"></i>';
        $parseStr .= '<?php endif;?>';
        $parseStr .='<?php if(empty($item["url"])): ?>';
        $parseStr .=' <span class="c-not-allowed"><?php echo $item["name"];?></span>';
        $parseStr .='<?php else: ?>';
        $parseStr .=' <a href="<?php echo $item["url"];?>"><?php echo $item["name"];?></a>';
        $parseStr .= '<?php endif;?>';
        $parseStr .= '</li>';
        $parseStr .='<?php endforeach;?>';
        $parseStr .= '</ul>';
        $parseStr .= '</div>';
        $parseStr .= '<?php endif;?>';
        return $parseStr;
    }

 /**
     * 这是一个闭合标签的简单演示
     */
    public function tagImgs(array $tag): string
    {
        $name   = isset($tag['name']) ? $this->autoBuildVar($tag['name']) : '[]';
        $index  = isset($tag['index']) ? $this->autoBuildVar($tag['index']) : '$index';
        $num    = isset($tag['num']) ? $this->autoBuildVar($tag['num']) : 1;
        $dom    = $tag['id'] ?? '';
        $parseStr = '<div class="cols '.$dom.'" data-name="'.$dom.'">';
        $parseStr .='<input type="hidden" class="ui-img-num" value="'.$num.'" />';
        $parseStr .='<input type="hidden" class="ui-img-index" value="<?php echo ' . $index . '??"";?>" name="'.$dom.'_index" >';
        $parseStr .='<?php if(!empty(' . $name . ')  && is_array(' . $name . ')): ?>';
        $parseStr .='<?php foreach(' . $name . ' as $item): ?>';
        $parseStr .='<div class="col col-auto ui-img mb-4 mt-4">';
        $parseStr .='  <div class="<?php if(isset('.$index.') && $item == '.$index.'):echo "card-activate shadow"; endif;?> card card-hover card-sm w120">';
        $parseStr .='    <div class="card-image space space-align-center h80">';
        $parseStr .='      <input class="ui-img-input" type="hidden" name="'.$dom.'[]" value="<?php echo $item;?>" />';
        $parseStr .='      <img class="ui-img-set img-responsive img-width h80 img-fit-cover" src="<?php echo $item;?>"/>';
        $parseStr .='    </div>';
        $parseStr .='<div class="card-footer btn-group btn-group-block">';
        $parseStr .='   <button outline gray icon type="button" class="btn btn-sm ui-img-left"><i class="icon bi-arrow-left"></i></button>';
        $parseStr .='   <button outline gray icon type="button" class="btn btn-sm ui-img-right"><i class="icon bi-arrow-right"></i></button>';
        $parseStr .='   <button outline gray icon type="button" class="btn btn-sm ui-img-del"><i class="icon bi-x-lg"></i></button>';
        $parseStr .='</div>';
        $parseStr .='  </div>';
        $parseStr .='</div>';
        $parseStr .='<?php endforeach;?>';
        $parseStr .='<?php elseif(empty(' . $name . ') && !empty(' . $index . ')): ?>';
        $parseStr .='<div class="col col-auto ui-img mb-4 mt-4">';
        $parseStr .='  <div class="card card-hover card-activate shadow card-sm w120">';
        $parseStr .='    <div class="card-image space space-align-center h80">';
        $parseStr .='      <input class="ui-img-input" type="hidden" name="'.$dom.'[]" value="<?php  echo ' . $index . '??"";?>" />';
        $parseStr .='      <img class="ui-img-set img-responsive img-width h80 img-fit-cover" src="<?php  echo ' . $index . '??"";?>"/>';
        $parseStr .='    </div>';
        $parseStr .='    <div class="card-footer btn-group btn-group-block">';
        $parseStr .='      <button outline gray icon type="button" class="btn btn-sm ui-img-left"><i class="icon bi-arrow-left"></i></button>';
        $parseStr .='      <button outline gray icon type="button" class="btn btn-sm ui-img-right"><i class="icon bi-arrow-right"></i></button>';
        $parseStr .='      <button outline gray icon type="button" class="btn btn-sm ui-img-del"><i class="icon bi-x-lg"></i></button>';
        $parseStr .='    </div>';
        $parseStr .='  </div>';
        $parseStr .='</div>';
        $parseStr .= '<?php endif;?>';
        $parseStr .='<div class="col col-auto ui-img-botton mb-4 mt-4">';
        $parseStr .='<div class="card card-hover card-sm w120" id="'.$dom.'" data-name="'.$dom.'">';
        $parseStr .='<div class="card-image space space-align-center h80"><img class="img-responsive img-width h50" src="__PUBLIC__/img/upload.svg" /></div>';
        $parseStr .='<div class="card-footer btn-group btn-group-block"><button outline gray type="button" class="btn btn-sm text-primary"><i class="icon icon-1x bi-cloud-arrow-up"></i> 选择文件</button></div>';
        $parseStr .='</div>';
        $parseStr .='</div>';
        $parseStr .='</div>';
        return $parseStr;
    } 
}