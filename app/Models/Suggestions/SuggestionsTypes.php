<?php

namespace App\Models\Suggestions;

use Illuminate\Database\Eloquent\Model;

class SuggestionsTypes extends Model
{
    protected $table = 'w_suggestions_t';

    public function getGatherAttribute($options)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        return $options;
    }

    public function setGatherAttribute($options)
    {
        if (is_array($options)) {
            $options = join(',', $options);
        }
        $this->attributes['gather'] = $options;
    }

    public function getAddgatherAttribute($extra)
    {
        return array_values(json_decode($extra, true) ?: []);
    }

    public function setAddgatherAttribute($extra)
    {
        $this->attributes['addgather'] = json_encode(array_values($extra));
    }

}
