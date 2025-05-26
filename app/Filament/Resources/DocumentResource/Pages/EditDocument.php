<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentsService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->using(function (Document $record) {
                    app(DocumentsService::class)->delete($record);
                    return true;
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $data['content'] = $record->content;

        // Определяем значение parent_category на основе category_id
        if ($record->subcategory_id) {
            $data['parent_category'] = $record->category_id;
        } else {
            $data['parent_category'] = $record->category_id;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(DocumentsService::class)->update($record, $data);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()
                ->label('Отмена'),
            $this->getSaveFormAction()
                ->label('Сохранить'),
        ];
    }
} 