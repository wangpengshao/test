<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Displayers\Actions;

class ReplaceActions extends Actions
{
    //全局进行覆盖样式!

    /**
     * Render view action.
     *
     * @return string
     */
    protected function renderView()
    {
        $view = trans('admin.view');
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->grid->getGridRowName()}-view btn btn-xs btn-default" 
title="{$view}" data-toggle="tooltip">
    <i class="fa fa-eye"></i>
</a>
EOT;
    }

    /**
     * Render edit action.
     *
     * @return string
     */
    protected function renderEdit()
    {
        $edit = trans('admin.edit');
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}/edit" class="{$this->grid->getGridRowName()}-edit btn btn-xs btn-default" 
title="{$edit}" data-toggle="tooltip">
    <i class="fa fa-edit"></i>
</a>
EOT;
    }

    /**
     * Render delete action.
     *
     * @return string
     */
    protected function renderDelete()
    {
        $delete = trans('admin.delete');
        $this->setupDeleteScript();
        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="{$this->grid->getGridRowName()}-delete btn btn-xs btn-warning"
title="{$delete}" data-toggle="tooltip">
    <i class="fa fa-trash"></i>
</a>
EOT;
    }

}
