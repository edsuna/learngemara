<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TractatesSeeder extends Seeder
{
    private $mesechtot = [
        ['ברכות', 'Berakhot', 64, 0],
        ['שבת', 'Shabbat', 175, 1],
        ['ערובין', 'Eiruvin', 105, 0],
        ['פסחים', 'Pesachim', 121, 1],
        ['ראש השנה', 'Rosh_HaShanah', 35, 0],
        ['יומא', 'Yoma', 88, 0],
        ['סוכה', 'Sukkah', 56, 1],
        ['ביצה', 'Beitzah', 40, 1],
        ['תענית', 'Taanit', 31, 0],
        ['מגילה', 'Megillah', 32, 0],
        ['מועד קטן', 'Moed_Katan', 29, 1],
        ['חגיגה', 'Chagigah', 27, 0],
        ['יבמות', 'Yevamot', 122, 1],
        ['כתובות', 'Ketubot', 112, 1],
        ['נדרים', 'Nedarim', 91, 1],
        ['נזיר', 'Nazir', 66, 1],
        ['סוטה', 'Sotah', 49, 1],
        ['גיטין', 'Gittin', 90, 2],
        ['קידושין', 'Kiddushin', 82, 1],
        ['בבא קמא', 'Bava_Kamma', 119, 1],
        ['בבא מציעא', 'Bava_Metzia', 119, 0],
        ['בבא בתרא', 'Bava_Batra', 176, 1],
        ['סנהדרין', 'Sanhedrin', 113, 1],
        ['מכות', 'Makkot', 24, 1],
        ['שבועות', 'Shevuot', 49, 1],
        ['עבודה זרה', 'Avodah_Zarah', 76, 1],
        ['הוריות', 'Horayot', 14, 0],
        ['זבחים', 'Zevachim', 120, 1],
        ['מנחות', 'Menachot', 110, 0],
        ['חולין', 'Chulin', 142, 0],
        ['בכורות', 'Bekhorot', 61, 0],
        ['ערכין', 'Arakhin', 34, 0],
        ['תמורה', 'Temurah', 34, 0],
        ['כריתות', 'Keritot', 28, 1],
        ['מעילה', 'Meilah', 22, 0],
        ['תמיד', 'Tamid', 33, 1],
        ['נדה', 'Niddah', 73, 0],
    ];

    public function run(): void
    {
        foreach ($this->mesechtot as $masechet) {
            DB::table('tractates')->insert([
                'name' => $masechet[0],
                'english_name' => $masechet[1],
                'pages' => $masechet[2],
                'has_last_amud' => $masechet[3],
            ]);
        }
    }
}
