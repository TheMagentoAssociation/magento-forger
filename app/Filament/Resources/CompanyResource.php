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
                    ->options(function () {
                        return collect(countries())
                            ->mapWithKeys(fn($country) => [
                                $country['iso_3166_1_alpha3'] => $country['name']['common']
                            ])
                            ->sort()
                            ->toArray();
                    })
                    ->searchable(),

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
