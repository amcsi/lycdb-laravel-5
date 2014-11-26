<?php

//
// NOTE Migration Created: 2014-11-09 16:24:08
// --------------------------------------------------

class CreateLycdbDatabase {
    //
    // NOTE - Make changes to the database.
    // --------------------------------------------------

    public function up()
    {

        //
        // NOTE -- cards
        // --------------------------------------------------

        Schema::create('cards', function($table) {
            $table->increments('id');
            $table->string('cid', 10)->unique();
            $table->string('name_jp', 100);
            $table->string('name_en', 100);
            $table->string('alternate_rarities', 10);
            $table->string('alternate_images', 10);
            $table->tinyInteger('type');
            $table->tinyInteger('ex');
            $table->tinyInteger('is_snow');
            $table->tinyInteger('is_moon');
            $table->tinyInteger('is_flower');
            $table->tinyInteger('is_lightning');
            $table->tinyInteger('is_sun');
            $table->integer('cost_snow');
            $table->integer('cost_moon');
            $table->integer('cost_flower');
            $table->integer('cost_lightning');
            $table->integer('cost_sun');
            $table->integer('cost_star');
            $table->string('ability_desc_jp', 1000);
            $table->string('ability_desc_en', 1000);
            $table->string('ability_cost_jp', 200);
            $table->string('ability_cost_en', 200);
            $table->string('ability_name_jp', 80);
            $table->string('ability_name_en', 80);
            $table->string('conversion_jp', 100);
            $table->string('conversion_en', 100);
            $table->tinyInteger('ap');
            $table->tinyInteger('dp');
            $table->tinyInteger('sp');
            $table->integer('position_flags');
            $table->integer('basic_ability_flags');
            $table->string('basic_abilities_jp', 200);
            $table->string('basic_abilities_en', 200);
            $table->tinyInteger('is_male');
            $table->tinyInteger('is_female');
            $table->string('comments_jp', 200);
            $table->string('comments_en', 200);
            $table->timestamp('insert_date')->nullable();
            $table->timestamp('update_date')->nullable();
            $table->tinyInteger('locked');
            $table->string('import_errors', 2000);
            $table->unsignedInteger('card_hash')->nullable();
            $table->unsignedInteger('lang_hash')->nullable();
            $table->unsignedInteger('import_card_hash')->nullable();
            $table->unsignedInteger('lock_card_hash')->nullable();
            $table->unsignedInteger('en_lang_hash')->nullable();
        });


        //
        // NOTE -- cards_sets_connect
        // --------------------------------------------------

        Schema::create('cards_sets_connect', function($table) {
            $table->string('cid', 10);
            $table->increments('extended_cid', 10);
            $table->string('rarity', 10);
            $table->integer('set_ext_id');
            $table->string('set_name', 200);
        });


        //
        // NOTE -- sets
        // --------------------------------------------------

        Schema::create('sets', function($table) {
            $table->increments('id');
            $table->integer('ext_id')->unique();
            $table->string('name_jp', 100);
            $table->string('name_en', 100);
        });



    }

    //
    // NOTE - Revert the changes to the database.
    // --------------------------------------------------

    public function down()
    {

        Schema::drop('cards');
        Schema::drop('cards_sets_connect');
        Schema::drop('sets');

    }
}
