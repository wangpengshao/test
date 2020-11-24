<?php

namespace App\Admin\Controllers\CustomView;

use App\Models\WechatApi\TemplateMesList;
use Encore\Admin\Form\Field;


class TemplateData extends Field
{
    protected $view = 'admin.diy.templateData';

    protected $currentList = [];

    protected $jsonKey = '';


    /**
     * Get the view variables of this field.
     *
     * @return array
     */
    public function variables()
    {
        return array_merge($this->variables, [
            'id' => $this->id,
            'name' => $this->elementName ?: $this->formatName($this->column),
            'help' => $this->help,
            'class' => $this->getElementClassString(),
            'value' => $this->value(),
            'label' => $this->label,
            'viewClass' => $this->getViewElementClasses(),
            'column' => $this->column,
            'errorKey' => $this->getErrorKey(),
            'attributes' => $this->formatAttributes(),
            'placeholder' => $this->getPlaceholder(),
            'currentList' => $this->currentList,
            'jsonKey' => $this->jsonKey,
        ]);
    }

    public function setJsonColumn($key)
    {
        $this->jsonKey = $key;
        return $this;
    }

    public function currentList()
    {
        $this->currentList = TemplateMesList::getCurrent(session('wxtoken'));
    }


    public function render()
    {
        $this->currentList();
        $model = $this->form->model();
        $template_id = $model[$this->id];

        $currentList = array_column($this->currentList, 'content', 'template_id');
        $template_da = $model[$this->jsonKey];

        $liveContent = [];
        foreach ($currentList as $k => $v) {
            $re = [];
            preg_match_all("/(?:\{\{)(.*)(?:\.DATA\}\})/i", $v, $re);
            $liveContent[$k] = array_get($re, '1');
        }
        unset($k, $v);

        if (!array_get($liveContent, $template_id) || empty($template_id)) {
            $template_id = '';
            $template_da =[];
        }

        $da = json_encode($template_da);
        
        $listDa = json_encode($liveContent);

        $this->script = <<<EOT
        
        var te_{$this->id} = '{$template_id}';
        var da_{$this->id} = JSON.parse('{$da}');
        var list_{$this->id} = JSON.parse('{$listDa}');
        $('.embed-{$this->jsonKey}-form').html('');
              
        if( te_{$this->id} != '' && list_{$this->id}.hasOwnProperty(te_{$this->id}) ){
            (function(){
                var tpl = $('template.{$this->column}-tpl');
                var list = list_{$this->id}[te_{$this->id}];
                var data = da_{$this->id};
    
                $.each(list,function(index,value){
                       var template = tpl.html().replace(/__LABEL__/g, value).replace(/__JSONKEY__/, value);
                       if(typeof data !== 'undefined' && data.hasOwnProperty(value) && data[value]!=null ){
                          template = template.replace(/__JSONVAL__/g, data[value]);
                       }else{
                          template = template.replace(/__JSONVAL__/g, '');
                       }
                       $('.embed-{$this->jsonKey}-form').append(template);
                });
               
            })();
        }
   
   
        $('#{$this->id}').change(function () {
                var selectVal=$(this).val();
                $('.embed-{$this->jsonKey}-form').html('');
                if( selectVal === '' ){
                  var emTe= $('template.{$this->column}-tpl-emArr').html();
                  $('.embed-{$this->jsonKey}-form').append(emTe);
                  return false;
                }
          
                var tpl = $('template.{$this->column}-tpl');
                var list = list_{$this->id}[selectVal];
                var data = da_{$this->id};
                $.each(list,function(index,value){
                       var template = tpl.html().replace(/__LABEL__/g, value).replace(/__JSONKEY__/, value);
                       if(typeof data !== 'undefined' && data.hasOwnProperty(value) && data[value]!=null){
                          template = template.replace(/__JSONVAL__/g, data[value]);
                       }else{
                          template = template.replace(/__JSONVAL__/g, '');
                       }
                       $('.embed-{$this->jsonKey}-form').append(template);
                });
        });
        
EOT;
        return parent::render();
    }

}
