<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Services\DocumentsService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(DocumentsService::class)->store($data);
    }
    
    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()
                ->label('Отмена'),
            $this->getCreateFormAction()
                ->label('Создать документ'),
        ];
    }
} 