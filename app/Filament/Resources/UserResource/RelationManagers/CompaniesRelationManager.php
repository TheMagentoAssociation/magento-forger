<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';

    protected static ?string $title = 'Owned Companies';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn($record) => route('filament.admin.resources.companies.edit', ['record' => $record->id]))
                    ->color('primary'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('website')
                    ->url(fn($record) => $record->website)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\IconColumn::make('is_magento_member')
                    ->boolean()
                    ->label('Magento Member'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'website', 'email'])
                    ->label('Add Company Ownership'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove Ownership'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
