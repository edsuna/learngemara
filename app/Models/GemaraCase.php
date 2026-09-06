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

    /**
     * The `_nr` (not relevant) columns are booleans. Without this cast they come
     * back from MySQL as integer 1/0, which serialises into the edit form as 1/0
     * and then fails `required_unless:{condition}_nr,true` on save -- that rule
     * only exempts a real boolean. A Not Relevant condition is stored NULL, so
     * the condition then fails `required` and the case cannot be re-saved.
     */
    protected function casts(): array
    {
        $casts = ['public' => 'boolean'];

        foreach (self::inputConditions as $inputCondition) {
            $casts[$inputCondition . '_nr'] = 'boolean';
        }

        return $casts;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tractate()
    {
        return $this->belongsTo(Tractate::class, 'masechet', 'english_name');
    }
}
