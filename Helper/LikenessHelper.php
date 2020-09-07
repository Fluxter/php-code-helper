<?php

/*
 * This file is part of the Fluxter Kundencenter package.
 * (c) Fluxter <http://fluxter.net/>
 * You are not allowed to see or use this code if you are not a part of Fluxter!
 */

namespace Fluxter\PhpCodeHelper\Helper;

final class LikenessHelper
{
    public static function getAlike(string $needle, array $haystack, string $seperator): ?string
    {
        $searchParts = explode($seperator, $needle);

        $result = [];
        foreach ($haystack as $check) {
            $checkParts = explode($seperator, $check);
            $correct = 0;
            $searchPartsIndex = count($searchParts) - 1;

            for ($i = count($checkParts) - 1; $i > 0 && $searchPartsIndex > 0; $i--) {
                if ($searchParts[$searchPartsIndex] == $checkParts[$i]) {
                    $correct++;
                } else {
                    break;
                }
                $searchPartsIndex--;
            }

            if (0 != $correct) {
                $result[$check] = $correct;
            }
        }

        if (0 == count($result)) {
            return null;
        }
        arsort($result);

        return array_key_first($result);
    }
}
