<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TractatesSeeder extends Seeder
{
    private $mesechtot = array(
                                array('ברכות', 'Berakhot', 64, 0),
                                array('שבת', 'Shabbat', 175, 1),
                                array('ערובין', 'Eiruvin', 105, 0),
                                array('פסחים', 'Pesachim', 121, 1),
                                array('ראש השנה', 'Rosh_HaShanah', 35, 0),
                                array('יומא', 'Yoma', 88, 0),
                                array('סוכה', 'Sukkah', 56, 1),
                                array('ביצה', 'Beitzah', 40, 1),
                                array('תענית', 'Taanit', 31, 0),
                                array('מגילה', 'Megillah', 32, 0),
                                array('מועד קטן', 'Moed_Katan', 29, 1),
                                array('חגיגה', 'Chagigah', 27, 0),
                                array('יבמות', 'Yevamot', 122, 1),
                                array('כתובות', 'Ketubot', 112, 1),
                                array('נדרים', 'Nedarim', 91, 1),
                                array('נזיר', 'Nazir', 66, 1),
                                array('סוטה', 'Sotah', 49, 1),
                                array('גיטין', 'Gittin', 90, 2),
                                array('קידושין', 'Kiddushin', 82, 1),
                                array('בבא קמא', 'Bava_Kamma', 119, 1),
                                array('בבא מציעא', 'Bava_Metzia', 119, 0),
                                array('בבא בתרא', 'Bava_Batra', 176, 1),
                                array('סנהדרין', 'Sanhedrin', 113, 1),
                                array('מכות', 'Makkot', 24, 1),
                                array('שבועות', 'Shevuot', 49, 1),
                                array('עבודה זרה', 'Avodah_Zarah', 76, 1),
                                array('הוריות', 'Horayot', 14, 0),
                                array('זבחים', 'Zevachim', 120, 1),
                                array('מנחות', 'Menachot', 110, 0),
                                array('חולין', 'Chulin', 142, 0),
                                array('בכורות', 'Bekhorot', 61, 0),
                                array('ערכין', 'Arakhin', 34, 0),
                                array('תמורה', 'Temurah', 34, 0),
                                array('כריתות', 'Keritot', 28, 1),
                                array('מעילה', 'Meilah', 22, 0),
                                array('תמיד', 'Tamid', 33, 1),
                                array('נדה', 'Niddah', 73, 0),
                              );

    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function insert_data($name, $english_name, $pages, $has_last_amud) {
        DB::table('tractates')->insert([
            'name' => $name,
            'english_name' => $english_name,
            'pages' => $pages,
            'has_last_amud' => $has_last_amud,
        ]);
    }


    public function run()
    {
        foreach ($this->mesechtot as $masechet) {
            $this->insert_data($masechet[0],
                               $masechet[1],
                               $masechet[2],
                               $masechet[3]);
        }
    }
}
