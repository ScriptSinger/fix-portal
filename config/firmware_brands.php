<?php

/*
 * Правила определения марки прошивки по буквенному префиксу модели («семейству»).
 * Заполняем только то, что подтверждено каталогами или известно наверняка. Остальные семейства
 * (FE, FA, AB, ALS, CM, числовые названия) намеренно не угадываем: для них остаётся только группа по платформе.
 *
 * Ключ: регулярное выражение, которое целиком совпадает с семейством (буквы до первой цифры).
 */
return [
    'families' => [
        // Electrolux Group
        '/^LAV$/' => 'AEG',
        '/^L$/' => 'AEG',
        '/^(EWF|EWT|EWS|EWW|EWP)$/' => 'Electrolux',
        '/^(ZWF|ZWG|ZWS|ZWT|ZWY|ZWD|ZWQ|ZWH|ZWE|ZWSG)$/' => 'Zanussi',

        // Indesit Company: Indesit
        '/^(WISL|WIL|WITL|WIUN|WISN|WISE|WIE|WIN|WIU|WIA|WID|WIT|WIXL|WIDL|WIUL|WIS|WITP)$/' => 'Indesit',
        '/^(IWSC|IWC|IWSD|IWSB|IWUB|IWUC|IWDC|IWD|IWE|IWB|IWT|IWSE|IWME|IWSNC|IWDD|IWUD)$/' => 'Indesit',
        '/^(W|INDESIT)$/' => 'Indesit',

        // Indesit Company: Hotpoint-Ariston
        '/^(AVL|AVSL|AVD|AVS|AVTL|AVSD|AVTF|AVF|AVC|AVXL|AVXD|AML)$/' => 'Hotpoint-Ariston',
        '/^(AQ|AQSL|AQSD|AQXL|AQXF|AQXD|AQSF|AQS|AQD|AQM|AQUALTIS)$/' => 'Hotpoint-Ariston',
        '/^(ARSL|ARXL|ARUSL|ARTXL|ARMXXL|ARMXXD|ARSF|ARTL|ARXF|ARUF|ARMXL|ARML|ARSAL)$/' => 'Hotpoint-Ariston',
        '/^AL$/' => 'Hotpoint-Ariston',
    ],

    // Префиксы, которыми пользуются разные марки: решает платформа модуля. Без известной платформы марку не угадываем
    'ambiguous_families' => [
        'EWC' => ['EWM' => 'Electrolux', 'ARCADIA' => 'Indesit', 'EVO1' => 'Indesit', 'EVO2' => 'Indesit'],
    ],

    // Группа брендов по платформе модуля (EVO1/EVO2/Arcadia у Indesit Company, EWM у Electrolux Group)
    'platform_groups' => [
        'EWM' => 'Electrolux Group',
        'EVO1' => 'Indesit Company',
        'EVO2' => 'Indesit Company',
        'ARCADIA' => 'Indesit Company',
    ],

    // Группа по марке, если платформа не указана
    'brand_groups' => [
        'AEG' => 'Electrolux Group',
        'Electrolux' => 'Electrolux Group',
        'Zanussi' => 'Electrolux Group',
        'Indesit' => 'Indesit Company',
        'Hotpoint-Ariston' => 'Indesit Company',
    ],

    // Как пишем платформу
    'platforms' => [
        'ARCADIA' => 'Arcadia',
        'EVO1' => 'EVO1',
        'EVO2' => 'EVO2',
        'EWM' => 'EWM',
        'MDL' => 'MDL',
    ],
];
