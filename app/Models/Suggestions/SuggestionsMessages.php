<?php

namespace App\Models\Suggestions;

use Illuminate\Database\Eloquent\Model;

class SuggestionsMessages extends Model
{
    protected $table = 'w_suggestions_m';

    protected $fillable = ['r_reply', 'a_reply', 'm_id', 'token', 's_id', 'r_id', 'is_reading'];

    public function hasOneType()
    {
        return $this->hasOne(SuggestionsTypes::class, 'id', 's_id');
    }

    public function hasOneSuggestions()
    {
        return $this->hasOne(SuggestionsList::class, 'id', 'm_id');
    }

}
