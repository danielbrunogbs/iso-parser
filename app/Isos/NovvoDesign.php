<?php

namespace App\Isos;

class NovvoDesign
{

    const VARIABLE_LENGTH = TRUE;
    const FIXED_LENGTH    = FALSE;

    public static function getIso()
    {
        return [
            1   => ['b',   32,  self::FIXED_LENGTH],
            2   => ['a',  99,  self::VARIABLE_LENGTH],
            3   => ['n',   6,   self::FIXED_LENGTH],
            4   => ['n',   12,  self::FIXED_LENGTH],
            5   => ['n',   12,  self::FIXED_LENGTH],
            6   => ['n',   12,  self::FIXED_LENGTH],
            7   => ['an',  10,  self::FIXED_LENGTH],
            8   => ['n',   8,   self::FIXED_LENGTH],
            9   => ['n',   8,   self::FIXED_LENGTH],
            10  => ['n',   8,   self::FIXED_LENGTH],
            11  => ['n',   6,   self::FIXED_LENGTH],
            12  => ['n',   6,   self::FIXED_LENGTH],
            13  => ['n',   4,   self::FIXED_LENGTH],
            14  => ['n',   4,   self::FIXED_LENGTH],
            15  => ['n',   4,   self::FIXED_LENGTH],
            16  => ['n',   4,   self::FIXED_LENGTH],
            17  => ['n',   4,   self::FIXED_LENGTH],
            18  => ['n',   4,   self::FIXED_LENGTH],
            19  => ['n',   3,   self::FIXED_LENGTH],
            20  => ['n',   3,   self::FIXED_LENGTH],
            21  => ['n',   3,   self::FIXED_LENGTH],
            22  => ['n',   3,   self::FIXED_LENGTH],
            23  => ['n',   3,   self::FIXED_LENGTH],
            24  => ['n',   3,   self::FIXED_LENGTH],
            25  => ['n',   2,   self::FIXED_LENGTH],
            26  => ['n',   2,   self::FIXED_LENGTH],
            27  => ['n',   1,   self::FIXED_LENGTH],
            28  => ['n',   8,   self::FIXED_LENGTH],
            29  => ['an',  9,   self::FIXED_LENGTH],
            30  => ['n',   8,   self::FIXED_LENGTH],
            31  => ['an',  9,   self::FIXED_LENGTH],
            32  => ['ans', 11,  self::VARIABLE_LENGTH],
            33  => ['n',   11,  self::VARIABLE_LENGTH],
            34  => ['an',  28,  self::VARIABLE_LENGTH],
            35  => ['ans', 99,  self::VARIABLE_LENGTH],
            36  => ['n',   104, self::VARIABLE_LENGTH],
            37  => ['an',  12,  self::FIXED_LENGTH],
            38  => ['an',  6,   self::FIXED_LENGTH],
            39  => ['an',  2,   self::FIXED_LENGTH],
            40  => ['ll',  99,  self::VARIABLE_LENGTH],
            41  => ['ans', 16,  self::FIXED_LENGTH],
            42  => ['n', 20,  self::FIXED_LENGTH],
            43  => ['ans', 999,  self::VARIABLE_LENGTH],
            44  => ['an',  25,  self::VARIABLE_LENGTH],
            45  => ['an',  76,  self::VARIABLE_LENGTH],
            46  => ['an',  999, self::VARIABLE_LENGTH],
            47  => ['an',  999, self::VARIABLE_LENGTH],
            48  => ['ans', 999, self::VARIABLE_LENGTH],
            49  => ['an',  3,   self::FIXED_LENGTH],
            50  => ['an',  3,   self::FIXED_LENGTH],
            51  => ['a',   3,   self::FIXED_LENGTH],
            52  => ['ans',  16,  self::FIXED_LENGTH],
            53  => ['ans',  16,  self::FIXED_LENGTH],
            54  => ['an',  120, self::FIXED_LENGTH],
            55  => ['ans', 999, self::VARIABLE_LENGTH],
            56  => ['ans', 999, self::VARIABLE_LENGTH],
            57  => ['ans', 999, self::VARIABLE_LENGTH],
            58  => ['ans', 999, self::VARIABLE_LENGTH],
            59  => ['ans', 99,  self::VARIABLE_LENGTH],
            60  => ['ans', 999,  self::VARIABLE_LENGTH],
            61  => ['ans', 999, self::VARIABLE_LENGTH],
            62  => ['ans', 999, self::VARIABLE_LENGTH],
            63  => ['ans', 999, self::VARIABLE_LENGTH],
            64  => ['b',   16,  self::FIXED_LENGTH],
            65  => ['ans', 64,  self::FIXED_LENGTH],
            66  => ['n',   1,   self::FIXED_LENGTH],
            67  => ['n',   2,   self::FIXED_LENGTH],
            68  => ['n',   3,   self::FIXED_LENGTH],
            69  => ['n',   3,   self::FIXED_LENGTH],
            70  => ['n',   3,   self::FIXED_LENGTH],
            71  => ['n',   4,   self::FIXED_LENGTH],
            72  => ['ans', 999, self::VARIABLE_LENGTH],
            73  => ['n',   6,   self::FIXED_LENGTH],
            74  => ['n',   10,  self::FIXED_LENGTH],
            75  => ['n',   10,  self::FIXED_LENGTH],
            76  => ['n',   10,  self::FIXED_LENGTH],
            77  => ['n',   10,  self::FIXED_LENGTH],
            78  => ['n',   10,  self::FIXED_LENGTH],
            79  => ['n',   10,  self::FIXED_LENGTH],
            80  => ['n',   10,  self::FIXED_LENGTH],
            81  => ['n',   10,  self::FIXED_LENGTH],
            82  => ['n',   12,  self::FIXED_LENGTH],
            83  => ['n',   12,  self::FIXED_LENGTH],
            84  => ['n',   12,  self::FIXED_LENGTH],
            85  => ['n',   12,  self::FIXED_LENGTH],
            86  => ['n',   15,  self::FIXED_LENGTH],
            87  => ['an',  16,  self::FIXED_LENGTH],
            88  => ['n',   16,  self::FIXED_LENGTH],
            89  => ['n',   16,  self::FIXED_LENGTH],
            90  => ['an',  42,  self::FIXED_LENGTH],
            91  => ['an',  1,   self::FIXED_LENGTH],
            92  => ['n',   2,   self::FIXED_LENGTH],
            93  => ['n',   5,   self::FIXED_LENGTH],
            94  => ['an',  7,   self::FIXED_LENGTH],
            95  => ['an',  42,  self::FIXED_LENGTH],
            96  => ['an',  8,   self::FIXED_LENGTH],
            97  => ['an',  17,  self::FIXED_LENGTH],
            98  => ['ans', 25,  self::FIXED_LENGTH],
            99  => ['n',   11,  self::VARIABLE_LENGTH],
            100 => ['n',   11,  self::VARIABLE_LENGTH],
            101 => ['ans', 17,  self::FIXED_LENGTH],
            102 => ['ans', 28,  self::VARIABLE_LENGTH],
            103 => ['ans', 28,  self::VARIABLE_LENGTH],
            104 => ['an',  99,  self::VARIABLE_LENGTH],
            105 => ['ans', 999, self::VARIABLE_LENGTH],
            106 => ['ans', 999, self::VARIABLE_LENGTH],
            107 => ['ans', 999, self::VARIABLE_LENGTH],
            108 => ['ans', 999, self::VARIABLE_LENGTH],
            109 => ['ans', 999, self::VARIABLE_LENGTH],
            110 => ['ans', 999, self::VARIABLE_LENGTH],
            111 => ['ans', 999, self::VARIABLE_LENGTH],
            112 => ['ans', 999, self::VARIABLE_LENGTH],
            113 => ['n',   11,  self::VARIABLE_LENGTH],
            114 => ['ans', 999, self::VARIABLE_LENGTH],
            115 => ['ans', 999, self::VARIABLE_LENGTH],
            116 => ['ans', 999, self::VARIABLE_LENGTH],
            117 => ['ans', 999, self::VARIABLE_LENGTH],
            118 => ['ans', 999, self::VARIABLE_LENGTH],
            119 => ['ans', 999, self::VARIABLE_LENGTH],
            120 => ['ans', 999, self::VARIABLE_LENGTH],
            121 => ['ans', 999, self::VARIABLE_LENGTH],
            122 => ['ans', 999, self::VARIABLE_LENGTH],
            123 => ['ans', 999, self::VARIABLE_LENGTH],
            124 => ['ans', 999, self::VARIABLE_LENGTH],
            125 => ['ans', 999,  self::VARIABLE_LENGTH],
            126 => ['ans', 999,   self::VARIABLE_LENGTH],
            127 => ['ans', 999, self::VARIABLE_LENGTH],
            128 => ['b',   16,  self::FIXED_LENGTH],
            129 => ['b',   999,  self::VARIABLE_LENGTH]
        ];
    }
}