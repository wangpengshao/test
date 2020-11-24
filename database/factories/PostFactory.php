<?php

use Faker\Generator as Faker;

//$factory->define(\App\Models\Wechat\Replycontent::class, function (Faker $faker) {
//
//    return [
//        'type' =>  $faker->numberBetween(0,1),
//        'user_id' => str_random(2),
//        'keyword' => $faker->word,
//        'token' => '18c6684c',
//        'content' => $faker->text,
//        'pic' => $faker->imageUrl(),
//        'matchtype' => $faker->numberBetween(0,1),
//        'url' => $faker->url,
//    ];
//
//});
//`order` int(11) NOT NULL DEFAULT '0',
//  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
//  `status` tinyint(1) NOT NULL DEFAULT '0',
//  `classtype` tinyint(1) NOT NULL,
//  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
//  `uri` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
//  `reurl` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
//  `add_openid` tinyint(1) NOT NULL,
//  `add_rdid` tinyint(1) NOT NULL,
//  `add_libcode` tinyint(1) NOT NULL,
$factory->define(\App\Models\Wechat\Indexmenu::class, function (Faker $faker) {
    return [
        'status' =>  $faker->numberBetween(0,1),
        'classtype' =>  $faker->numberBetween(0,3),
        'add_openid' =>  $faker->numberBetween(0,1),
        'add_rdid' =>  $faker->numberBetween(0,1),
        'add_libcode' =>  $faker->numberBetween(0,1),
        'order' =>  $faker->numberBetween(0,100),
        'caption' => $faker->title,
        'token' => '18c6684c',
        'icon' => $faker->imageUrl(),
        'reurl' => $faker->url(),
        'uri' => $faker->url(),
    ];

});
