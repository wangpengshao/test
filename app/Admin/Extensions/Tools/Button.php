<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\AbstractTool;

/**
 * 公用按钮工具 render
 * Class Button
 * @package App\Admin\Extensions\Tools
 */
class Button extends AbstractTool
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var null
     */
    protected $icon;
    /**
     * @var string
     */
    protected $style;
    /**
     * @var bool
     */
    protected $newView;

    /**
     * Button constructor.
     * @param string $url
     * @param string $title
     * @param null   $icon
     * @param bool   $newView
     * @param string $style
     */
    public function __construct($url = 'javascript:void(0)', $title = '返回', $icon = null, $newView = false, $style = 'default')
    {
        $this->url = $url;
        $this->title = $title;
        $this->icon = $icon;
        $this->style = $style;
        $this->newView = $newView;
    }

    /**
     * @return string
     */
    public function render()
    {
        $str = '<div class="btn-group" style="margin-right: 3px">
<a href="%s" class="btn btn-sm btn-%s" target="%s" title="%s">%s %s</a></div>';
        $icon = $this->icon ? '<i class="fa ' . $this->icon . '"></i>' : '';
        $target = $this->newView ? '_blank' : '';
        return sprintf($str, $this->url, $this->style, $target, $this->title, $icon, $this->title);
    }
}
