<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Content::create([
              'key' => 'HOME_PAGE',
              'value' => '<span style="font-family: Montserrat;">Host video meetings your way.</span>',
          ]);

        Content::create([
              'key' => 'PRIVACY_POLICY',
              'value' => '<p><font color="#000000" face="Open Sans, Arial, sans-serif"><span style="font-family: Montserrat;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span></font><span style="font-family: Montserrat;">﻿</span><br></p>',
          ]);

        Content::create([
              'key' => 'TERMS_AND_CONDITIONS',
              'value' => '<p><font color="#000000" face="Open Sans, Arial, sans-serif"><span style="font-family: Montserrat;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</span></font><span style="font-family: Montserrat;">﻿</span><br></p>',
          ]);
    }
}
