<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum MediaTypeEnum: string implements TranslatableInterface
{
    case IMAGE = "IMAGE";
    case WEBSITE = "WEBSITE";
    case VIDEO = "VIDEO";
    case YOUTUBE = "YOUTUBE";

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return match ($this) {
            self::IMAGE => $translator->trans(
                'media_type.image',
                domain: 'enum',
                locale: $locale
            ),
            self::WEBSITE => $translator->trans(
                'media_type.website',
                domain: 'enum',
                locale: $locale
            ),
            self::VIDEO => $translator->trans(
                'media_type.video',
                domain: 'enum',
                locale: $locale
            ),
            self::YOUTUBE => $translator->trans(
                'media_type.youtube',
                domain: 'enum',
                locale: $locale
            ),
        };
    }
}
