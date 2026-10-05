<?php
/**
 * Photography.
 *
 * Photos come from Unsplash (free to use under the Unsplash License,
 * https://unsplash.com/license). Admin → Photos → "Save photos to this website"
 * stores a copy on your site (see includes/photos.php); until then they load
 * from Unsplash's CDN. To use your own photo, upload it in Admin → Photos, or
 * drop a file named after the key into assets/img/photos/ (e.g. hero.jpg).
 *
 * 'id' is the Unsplash photo ID from images.unsplash.com/photo-{id}.
 */

return [
    'hero'       => ['file' => 'assets/img/fallback/57160dd35c949d4a.jpg', 'focus' => 'top', 'id' => '1573164713988-8665fc963095', 'alt' => 'Professional in a blazer working at a laptop', 'w' => 681, 'h' => 1024],
    'cyber'      => ['id' => '1515378791036-0648a3ef77b2',    'alt' => 'Professional working on a laptop at a tidy desk',                'w' => 1600, 'h' => 1067],
    'webdev'     => ['id' => '1519389950473-47ba0277781c', 'alt' => 'Team working together on laptops at a long table',                     'w' => 1600, 'h' => 1067],
    'itsupport'  => ['id' => '1573164713714-d95e436ab8d6', 'alt' => 'Support specialist helping a client with a computer',             'w' => 1600, 'h' => 1067],
    'training'   => ['id' => '1524178232363-1fb2b075b655', 'alt' => 'Group of adults learning together in a bright training room',     'w' => 1600, 'h' => 1067],
    'cloud'      => ['id' => '1556761175-b413da4baf72',    'alt' => 'Small business team reviewing work on their laptops',               'w' => 1600, 'h' => 1067],
    'consulting' => ['id' => '1600880292203-757bb62b4baf', 'alt' => 'Consultant discussing a technology plan with business owners',    'w' => 1600, 'h' => 1067],
    'team'       => ['id' => '1522071820081-009f0129c71c', 'alt' => 'People collaborating around laptops at a shared table',          'w' => 1600, 'h' => 1067],
    'services'   => ['id' => '1551434678-e076c223a692',    'alt' => 'Technology team working at computers in a modern workspace',      'w' => 1600, 'h' => 1067],
    'people'     => ['id' => '1531482615713-2afd69097998', 'alt' => 'Engineer working on a laptop at a bright desk',                    'w' => 1600, 'h' => 1067],
    'about'      => ['id' => '1542744173-8e7e53415bb0', 'alt' => 'Technology specialist presenting a plan to a small team',           'w' => 1600, 'h' => 1067],
    'blog'       => ['id' => '1522202176988-66273c2fd55f', 'alt' => 'Group of people learning together on laptops',                     'w' => 1600, 'h' => 1067],
    'resources'  => ['id' => '1552664730-d307ca884978',    'alt' => 'Team planning together with sticky notes on a wall',                      'w' => 1600, 'h' => 1067],
    'post-mfa'      => ['id' => '1512941937669-90a1b58e7e9c', 'alt' => 'Hand holding a smartphone',                                   'w' => 1600, 'h' => 1067],
    'post-phishing' => ['id' => '1551836022-d5d88e9218df', 'alt' => 'Professional reading messages on a laptop in an office',                        'w' => 1600, 'h' => 1067],
    'post-website'  => ['id' => '1521791136064-7986c2920216', 'alt' => 'Business owner shaking hands with a client',                     'w' => 1600, 'h' => 1067],
    'post-backup'   => ['id' => '1517245386807-bb43f82c33c4', 'alt' => 'Colleagues reviewing files together in a meeting room',                        'w' => 1600, 'h' => 1067],
];
