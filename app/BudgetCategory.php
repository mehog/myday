<?php

namespace App;

enum BudgetCategory: string
{
    case SalaIVecera = 'sala_i_vecera';
    case BendIGlazba = 'bend_i_glazba';
    case FotografIVideo = 'fotograf_i_video';
    case CvijeceIDekoracija = 'cvijece_i_dekoracija';
    case TortaISlastice = 'torta_i_slastice';
    case VjencanicaIOdijelo = 'vjencanica_i_odijelo';
    case Prstenje = 'prstenje';
    case PozivniceITisak = 'pozivnice_i_tisak';
    case Prijevoz = 'prijevoz';
    case Smjestaj = 'smjestaj';
    case Ostalo = 'ostalo';

    public function label(): string
    {
        return __('budget.categories.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $category): array => [$category->value => $category->label()],
        )->all();
    }

    public function chartColor(): string
    {
        return match ($this) {
            self::SalaIVecera => '#c4a574',
            self::BendIGlazba => '#6b8f71',
            self::FotografIVideo => '#5b7c99',
            self::CvijeceIDekoracija => '#9b6b8a',
            self::TortaISlastice => '#d4a5a5',
            self::VjencanicaIOdijelo => '#c9a227',
            self::Prstenje => '#8b7355',
            self::PozivniceITisak => '#7a9eb8',
            self::Prijevoz => '#6d7a8a',
            self::Smjestaj => '#8a9e7a',
            self::Ostalo => '#9a9a9a',
        };
    }
}
