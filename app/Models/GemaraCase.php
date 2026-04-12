<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $fillable = [
        'masechet',
        'daf',
        'gemara_text',
        'title',
        'din_type',
        'act',
        'public',
        'consequences',
        'consequences_nr',
        'when',
        'when_nr',
        'where',
        'where_nr',
        'toWhat',
        'toWhat_nr',
        'withWhat',
        'withWhat_nr',
        'how',
        'how_nr',
        'other',
        'other_nr',
        'who',
        'who_nr',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tractate()
    {
        return $this->belongsTo(Tractate::class, 'masechet', 'english_name');
    }
}
