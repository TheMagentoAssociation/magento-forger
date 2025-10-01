<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-c-building-storefront';

    public static function form(Form $form): Form
    {
        /**
         * 'email',
         * 'phone',
         * 'website',
         * 'address',
         * 'zip',
         * 'city',
         * 'state',
         * 'country',
         * 'country_code',
         * 'logo',
         */
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('website')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->required(),
                Forms\Components\TextInput::make('address')->required(),
                Forms\Components\TextInput::make('zip')->required(),
                Forms\Components\TextInput::make('city')->required(),
                Forms\Components\TextInput::make('state'),
                Forms\Components\Select::make('country')
                ->options(
                    ['Africa' => [
                            'DZA' => 'Algeria',
                            'AGO' => 'Angola',
                            'BEN' => 'Benin',
                            'BWA' => 'Botswana',
                            'BFA' => 'Burkina Faso',
                            'BDI' => 'Burundi',
                            'CMR' => 'Cameroon',
                            'CPV' => 'Cape Verde',
                            'CAF' => 'Central African Republic',
                            'TCD' => 'Chad',
                            'COM' => 'Comoros',
                            'COG' => 'Congo (Brazzaville)',
                            'COD' => 'Congo (Kinshasa)',
                            'CIV' => 'Côte d’Ivoire',
                            'DJI' => 'Djibouti',
                            'EGY' => 'Egypt',
                            'GNQ' => 'Equatorial Guinea',
                            'ERI' => 'Eritrea',
                            'SWZ' => 'Eswatini',
                            'ETH' => 'Ethiopia',
                            'GAB' => 'Gabon',
                            'GMB' => 'Gambia',
                            'GHA' => 'Ghana',
                            'GIN' => 'Guinea',
                            'GNB' => 'Guinea-Bissau',
                            'KEN' => 'Kenya',
                            'LSO' => 'Lesotho',
                            'LBR' => 'Liberia',
                            'LBY' => 'Libya',
                            'MDG' => 'Madagascar',
                            'MWI' => 'Malawi',
                            'MLI' => 'Mali',
                            'MRT' => 'Mauritania',
                            'MUS' => 'Mauritius',
                            'MYT' => 'Mayotte',
                            'MAR' => 'Morocco',
                            'MOZ' => 'Mozambique',
                            'NAM' => 'Namibia',
                            'NER' => 'Niger',
                            'NGA' => 'Nigeria',
                            'RWA' => 'Rwanda',
                            'REU' => 'Réunion',
                            'STP' => 'São Tomé and Príncipe',
                            'SEN' => 'Senegal',
                            'SYC' => 'Seychelles',
                            'SLE' => 'Sierra Leone',
                            'SOM' => 'Somalia',
                            'ZAF' => 'South Africa',
                            'SSD' => 'South Sudan',
                            'SDN' => 'Sudan',
                            'TZA' => 'Tanzania',
                            'TGO' => 'Togo',
                            'TUN' => 'Tunisia',
                            'UGA' => 'Uganda',
                            'ESH' => 'Western Sahara',
                            'ZMB' => 'Zambia',
                            'ZWE' => 'Zimbabwe',
                        ],
                        'Asia' => [
                            'AFG' => 'Afghanistan',
                            'ARM' => 'Armenia',
                            'AZE' => 'Azerbaijan',
                            'BHR' => 'Bahrain',
                            'BGD' => 'Bangladesh',
                            'BTN' => 'Bhutan',
                            'BRN' => 'Brunei',
                            'KHM' => 'Cambodia',
                            'CHN' => 'China',
                            'CYP' => 'Cyprus',
                            'GEO' => 'Georgia',
                            'IND' => 'India',
                            'IDN' => 'Indonesia',
                            'IRN' => 'Iran',
                            'IRQ' => 'Iraq',
                            'ISR' => 'Israel',
                            'JPN' => 'Japan',
                            'JOR' => 'Jordan',
                            'KAZ' => 'Kazakhstan',
                            'KWT' => 'Kuwait',
                            'KGZ' => 'Kyrgyzstan',
                            'LAO' => 'Laos',
                            'LBN' => 'Lebanon',
                            'MYS' => 'Malaysia',
                            'MDV' => 'Maldives',
                            'MNG' => 'Mongolia',
                            'MMR' => 'Myanmar',
                            'NPL' => 'Nepal',
                            'PRK' => 'North Korea',
                            'OMN' => 'Oman',
                            'PAK' => 'Pakistan',
                            'PSE' => 'Palestine',
                            'PHL' => 'Philippines',
                            'QAT' => 'Qatar',
                            'SAU' => 'Saudi Arabia',
                            'SGP' => 'Singapore',
                            'KOR' => 'South Korea',
                            'LKA' => 'Sri Lanka',
                            'SYR' => 'Syria',
                            'TWN' => 'Taiwan',
                            'TJK' => 'Tajikistan',
                            'THA' => 'Thailand',
                            'TLS' => 'Timor-Leste',
                            'TUR' => 'Turkey',
                            'TKM' => 'Turkmenistan',
                            'ARE' => 'United Arab Emirates',
                            'UZB' => 'Uzbekistan',
                            'VNM' => 'Vietnam',
                            'YEM' => 'Yemen',
                        ],
                        'Europe' => [
                            'ALB' => 'Albania',
                            'AND' => 'Andorra',
                            'AUT' => 'Austria',
                            'BLR' => 'Belarus',
                            'BEL' => 'Belgium',
                            'BIH' => 'Bosnia and Herzegovina',
                            'BGR' => 'Bulgaria',
                            'HRV' => 'Croatia',
                            'CYP' => 'Cyprus',
                            'CZE' => 'Czech Republic',
                            'DNK' => 'Denmark',
                            'EST' => 'Estonia',
                            'FRO' => 'Faroe Islands',
                            'FIN' => 'Finland',
                            'FRA' => 'France',
                            'DEU' => 'Germany',
                            'GIB' => 'Gibraltar',
                            'GRC' => 'Greece',
                            'HUN' => 'Hungary',
                            'ISL' => 'Iceland',
                            'IRL' => 'Ireland',
                            'ITA' => 'Italy',
                            'LVA' => 'Latvia',
                            'LIE' => 'Liechtenstein',
                            'LTU' => 'Lithuania',
                            'LUX' => 'Luxembourg',
                            'MLT' => 'Malta',
                            'MDA' => 'Moldova',
                            'MCO' => 'Monaco',
                            'MNE' => 'Montenegro',
                            'NLD' => 'Netherlands',
                            'MKD' => 'North Macedonia',
                            'NOR' => 'Norway',
                            'POL' => 'Poland',
                            'PRT' => 'Portugal',
                            'ROU' => 'Romania',
                            'RUS' => 'Russia',
                            'SMR' => 'San Marino',
                            'SRB' => 'Serbia',
                            'SVK' => 'Slovakia',
                            'SVN' => 'Slovenia',
                            'ESP' => 'Spain',
                            'SWE' => 'Sweden',
                            'CHE' => 'Switzerland',
                            'UKR' => 'Ukraine',
                            'GBR' => 'United Kingdom',
                            'VAT' => 'Vatican City',
                        ],
                        'North America' => [
                            'ATG' => 'Antigua and Barbuda',
                            'BHS' => 'Bahamas',
                            'BRB' => 'Barbados',
                            'BLZ' => 'Belize',
                            'CAN' => 'Canada',
                            'CRI' => 'Costa Rica',
                            'CUB' => 'Cuba',
                            'DMA' => 'Dominica',
                            'DOM' => 'Dominican Republic',
                            'SLV' => 'El Salvador',
                            'GRD' => 'Grenada',
                            'GTM' => 'Guatemala',
                            'HTI' => 'Haiti',
                            'HND' => 'Honduras',
                            'JAM' => 'Jamaica',
                            'MEX' => 'Mexico',
                            'NIC' => 'Nicaragua',
                            'PAN' => 'Panama',
                            'KNA' => 'Saint Kitts and Nevis',
                            'LCA' => 'Saint Lucia',
                            'VCT' => 'Saint Vincent and the Grenadines',
                            'TTO' => 'Trinidad and Tobago',
                            'USA' => 'United States',
                        ],
                        'South America' => [
                            'ARG' => 'Argentina',
                            'BOL' => 'Bolivia',
                            'BRA' => 'Brazil',
                            'CHL' => 'Chile',
                            'COL' => 'Colombia',
                            'ECU' => 'Ecuador',
                            'GUY' => 'Guyana',
                            'PRY' => 'Paraguay',
                            'PER' => 'Peru',
                            'SUR' => 'Suriname',
                            'URY' => 'Uruguay',
                            'VEN' => 'Venezuela',
                        ],
                        'Oceania' => [
                            'AUS' => 'Australia',
                            'FJI' => 'Fiji',
                            'KIR' => 'Kiribati',
                            'MHL' => 'Marshall Islands',
                            'FSM' => 'Micronesia',
                            'NRU' => 'Nauru',
                            'NZL' => 'New Zealand',
                            'PLW' => 'Palau',
                            'PNG' => 'Papua New Guinea',
                            'WSM' => 'Samoa',
                            'SLB' => 'Solomon Islands',
                            'TON' => 'Tonga',
                            'TUV' => 'Tuvalu',
                            'VUT' => 'Vanuatu',
                        ],
                    ]
                )->searchable(),

                Forms\Components\Toggle::make('is_magento_member')
                    ->label('Is Magento Member'),

                Forms\Components\Toggle::make('is_recommended')
                    ->label('Recommended by Users'),

                Forms\Components\FileUpload::make('logo')
                ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg', 'image/gif'])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),

                Tables\Columns\IconColumn::make('is_magento_member')
                    ->boolean()
                    ->label('Magento Member'),

                Tables\Columns\IconColumn::make('is_recommended')
                    ->boolean()
                    ->label('Recommended'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
