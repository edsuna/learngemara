<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Tractate;

class GemaraCase extends Model
{
    use HasFactory;

    const inputConditions = [
        "consequences",
        "when",
        "where",
        "toWhat",
        "withWhat",
        "how",
        "other",
        "who",
    ];

    const dinTypes = [
        'מותר',
        'אסור',
        'חייב',
        'פטור',
        'כשר',
        'פסול',
    ];

    protected $guarded = [];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function tractate() {
        return $this->belongsTo(Tractate::class, 'masechet', 'english_name');
    }
}
